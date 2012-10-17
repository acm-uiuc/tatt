<?php
/*
 * This is an example config.  We do not keep the actual file with passwords
 * in version control.  Copy this file as "dbconfig.php" and modify the copy
 * with the appropriate values.  Add the copy to your version control ignore.
 */


namespace tatt;
if (!defined('IN_TATT')) {
    exit;
}

/*
 * This file creates a multidemensional array of db configs which are fetched
 * by current hostname.  Add your hostname to the dev section at the end of
 * this file.
 */

$db_prod = array(
        'main' => array(
                'host'         => 'localhost',
                'user'         => 'username',
                'pass'         => 'password',
                'db'           => 'db_name',
                'table_prefix' => 'table_prefix',
        ),
);

$db_dev = array(
        'main' => array(
                'host'         => 'localhost',
                'user'         => 'dev_username',
                'pass'         => 'dev_password',
                'db'           => 'dev_db_name',
                'table_prefix' => 'dev_table_prefix',
        ),
);

//Define which of the above configs to use based on hostname.
$db_config = array(
    // Production
    'trackallthethings.com' => $db_prod,

    // Development environments
    'devexample.trackallthethings.com' => $db_dev,
);
