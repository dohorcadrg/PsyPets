<?php
require_once 'commons/settings.php';
require_once 'lib/HandyDB.class.php';
require_once 'commons/old_db_code_support.php';

require_once 'libraries/extra_functions.php';
require_once 'libraries/ad_box.php';

ini_set('session.bug_compat_warn', 0);
ini_set('session.bug_compat_42', 0);
ignore_user_abort(true);

$database = new HandyDB();
$now = time();
