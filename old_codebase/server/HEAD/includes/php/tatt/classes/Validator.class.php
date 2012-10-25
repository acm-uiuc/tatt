<?php
/*
 * The MIT License
 *
 * Copyright (c) 2011 Eric Parsons
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
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
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace tatt;
if (!defined('IN_TATT')) {
    exit;
}

/*
 * Class for validating values that php doesn't have a built in function for.
 */
class Validator {

    /*
     * Returns user input that passes type testing.  Returns value in the
     * specified type on success, FALSE if the variable does not exist, and
     * NULL if the validation fails.
     *
     * NOTE: This site was built under the influence of magic quotes so to avoid
     *       breaking existing code, we will escape all string based input.  If
     *       for some reason you need an unescaped string, I've added a parameter
     *       to prevent this function from running the escape code.
     *       Prepared database statements would be better, but considering the
     *       sheer ammount of effort needed to make that conversion, we will
     *       have to settle for this for now...  If you have the time and will power
     *       to see prepared statments to fruition (while maintaining functionality
     *       of our new db object) feel free to attempt it.
     */
    public static function getExtInput($source=NULL, $varName=NULL, $expectedType=NULL, $makeDbSafe=TRUE){
        global $db;
        //Make sure parameters are as expected
        $validSources   = array('get','post','cookie');
        $validTypes     = array('bool','int','float','string','array');
        
        if(!in_array($source, $validSources) || !is_string($varName) || !in_array($expectedType, $validTypes)){
            trigger_error('ERROR: Parameters not correctly specified in validator::getExtInput()');
        }else{
            $inputId    = self::getFilter_InputId($source);
            $filterId   = self::getFilter_FilterId($expectedType);

            if($expectedType == 'array'){
                $filterFlag = FILTER_REQUIRE_ARRAY;
            }else{
                $filterFlag = NULL;
            }

            //print "$userVariable = filter_input($inputId, $varName, $filterId, $filterFlags)";
            $userVariable = filter_input($inputId, $varName, $filterId, $filterFlag);

            if($makeDbSafe){
                if($expectedType == 'string' && $userVariable != false){
                    $userVariable = $db->realEscapeString($userVariable);
                } elseif ($expectedType == 'array' && $userVariable != false) {
                    array_walk_recursive($userVariable,'self::makeArrayValuesDbSafe');
                }
            }
            
            return $userVariable;
        }
    }

    /*
     * Internal function that returns the Filter ID we need based on the type being tested
     */
    protected static function getFilter_FilterId($expectedType){
            switch ($expectedType){
                case 'bool':
                    $filterId = FILTER_VALIDATE_BOOLEAN;
                    break;
                case 'int':
                    $filterId = FILTER_VALIDATE_INT;
                    break;
                case 'float':
                    $filterId = FILTER_VALIDATE_FLOAT;
                    break;
                case 'string':
                    $filterId = FILTER_DEFAULT;
                    break;
                case 'array':
                    $filterId = FILTER_DEFAULT;
                    break;
                default:
                    $filterId = NULL;
                    break;
            }
        return $filterId;
    }


    /*
     * Internal function that returns the Input ID we need for php's filter functions.
     */
    protected static function getFilter_InputId($source){
            switch ($source){
                case 'get':
                    $inputId = INPUT_GET;
                    break;
                case 'post':
                    $inputId = INPUT_POST;
                    break;
                case 'cookie':
                    $inputId = INPUT_COOKIE;
                    break;
                default:
                    $inputId = NULL;
                    break;
            }
        return $inputId;
    }


    public static function is_username($username){
     //TODO: return a boolean check of username
        return true;
    }

    /*
     * Validates an email based on current RFCs.  Method wraps third-party
     * BSD code.
     */
    public static function is_email ($email, $check_DNS = false) {
        require_once TATT_LIBRARIES . 'is_email.php';
        $result = is_email($email,$check_DNS);
        return $result;
    }
}
