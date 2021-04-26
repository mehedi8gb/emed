<?php

if (!file_exists('../config.php')) {
    die();
}
if (!defined('SB_PATH')) define('SB_PATH', dirname(dirname(__FILE__)));
require('../config.php');
require('functions.php');
sb_init_translations();
require('components.php');
if (sb_isset($_GET, 'mode') == 'tickets') {
    sb_component_tickets();
} else {
    sb_component_chat();
}
echo '<!-- Support Board - https://board.support -->';
die();

?>