<?php
/*
 * The MIT License
 *
 * Copyright (c) 2011 Vinay Hiremath, Drew Cross
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"""), to
 * deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * */

/*
 *  The recommendation class recommends items to a user based on the following
 *  algorithm:
 *
 *  1.) Take in the user_id.
 *
 *  2.) Get the latest items (item_ids) checked out by this user by
 *  checkout_time.
 *
 *  3.) Get the top users (user_ids) that checked out these same items by
 *  frequency.
 *
 *  4.) Return the top items these top users have checked out that the
 *  original user_id param has not checked out by frequency.
 *
 */


namespace tatt;
if (!defined('IN_TATT')) {
    exit;
}

class Recommendation {

    /*
     *  Uses the algorithm detailed above to intelligently recommend items the
     *  user may be interested in.
     */
    static public function recommend_items($user_id) {
        // the number of latest items we'll get for the recommendation
        $NUM_LATEST_ITEMS = 10;
        // the number of top users (by frequency) that checked out the same items
        $NUM_TOP_USERS = 10;
        // the number of items we should recommend
        $NUM_ITEMS_TO_RECOMMEND = 5;
        // the database we'll be querying
        global $db;

        // first we should get the latest items (item_ids) checked out by
        // this user
        //---------------------------------------------------------------
        $get_latest_items_query = 'SELECT item_id ' .
                                  'FROM ' . TATT_PREFIX. 'checkouts ' .
                                  'WHERE user_id = ' . $user_id .
                                  ' ORDER BY checkout_time DESC ';
//        $latest_items_result = $db->query($get_latest_items_query);

        // now we should get the top users (by frequency) (user_ids) that
        // checked out these same items
        //----------------------------------------------------------------
//        while($item_result = $latest_item_result->fetch_object()){
//
//        }
        $get_top_users_query = 'SELECT user_id ' .
                               'FROM ' . TATT_PREFIX . 'checkouts ' .
                               'WHERE user_id <> ' . $user_id . ' AND item_id = ANY ' . "($get_latest_items_query)" .
                               ' GROUP BY user_id ' .
                               'ORDER BY COUNT(user_id) DESC ';
//        $get_top_users_result = $db->query($get_top_users_query);

        // finally we should recommend the top items checked out by these top
        // users (by frequency) that the user_id param has not checked out
        //--------------------------------------------------------------------
        // first get the items this user has checked out
        $get_users_checkouts_query = 'SELECT item_id ' .
                                     'FROM tatt_checkouts ' .
                                     'WHERE user_id = ' . $user_id;
//        $get_users_checkouts_result = $db->query($get_users_checkouts_query);
        // then get the recommendations
        $get_recommendations_query = 'SELECT item_id ' .
                                     'FROM tatt_checkouts ' .
                                     'WHERE user_id = ANY ' . "($get_top_users_query)" . ' AND item_id <> ALL' . "($get_users_checkouts_query)" .
                                     ' GROUP BY item_id ' .
                                     'ORDER BY COUNT(item_id) DESC ' .
                                     'LIMIT 0, ' . $NUM_ITEMS_TO_RECOMMEND;
        $get_recommendations_result = $db->query($get_recommendations_query);

        // now let's put these recommended item_ids in an array and return it
        $recommendations = array();
        while ($item = $get_recommendations_result->fetch_object()){
            $recommendations[] = $item->item_id;
        }

        return $recommendations;

        /* THE FULL QUERY:
         * ---------------
         * SELECT item_id FROM tatt_checkouts WHERE user_id = ANY (SELECT
         * user_id FROM tatt_checkouts WHERE user_id <> $user_id AND item_id = ANY (
         * SELECT item_id FROM tatt_checkouts WHERE user_id = $user_id ORDER BY
         * checkout_time DESC) GROUP BY user_id ORDER BY COUNT(user_id) DESC)
         * AND item_id <> ALL (SELECT item_id FROM tatt_checkouts WHERE user_id
         * = $user_id) GROUP BY item_id ORDER BY COUNT(item_id) DESC
         * ---------------
         * */

    }

}
