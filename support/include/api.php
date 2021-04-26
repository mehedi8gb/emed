<?php

/*
 * ==========================================================
 * API.PHP
 * ==========================================================
 *
 * API main file. This file listens the POST queries and return the result. © 2020 board.support. All rights reserved.
 *
 */

require_once('functions.php');
define('SB_API', true);
$function_name = '';
$functions = [
    'is-online' => ['user_id'],
    'get-setting' => ['setting'],
    'saved-replies' => [],
    'get-settings' => [],
    'add-user' => [],
    'get-user' => ['user_id'],
    'get-user-extra' => ['user_id'],
    'get-new-users' => ['datetime'],
    'get-users' => [],
    'get-online-users' => [],
    'search-users' => ['search'],
    'delete-user' => ['user_id'],
    'delete-users' => ['user_ids'],
    'update-user' => ['user_id'],
    'count-users' => [],
    'update-user-to-lead' => ['user_id'],
    'get-conversations' => [],
    'get-new-conversations' => ['datetime'],
    'get-conversation' => ['conversation_id'],
    'search-conversations' => ['search'],
    'search-user-conversations' => ['search', 'user_id'],
    'new-conversation' => ['user_id'],
    'get-user-conversations' => ['user_id'],
    'get-new-user-conversations' => ['user_id', 'datetime'],
    'update-conversation-status' => ['conversation_id', 'status_code'],
    'update-conversation-department' => ['conversation_id', 'department'],
    'set-rating' => ['settings'],
    'get-rating' => ['user_id'],
    'get-new-messages' => ['user_id', 'conversation_id', 'datetime'],
    'send-message' => ['user_id', 'conversation_id'],
    'send-bot-message' => ['conversation_id', 'message'],
    'send-slack-message' => ['user_id'],
    'update-message' => ['message_id'],
    'delete-message' => ['message_id'],
    'send-email' => ['recipient_id', 'message'],
    'slack-users' => [],
    'archive-slack-channels' => [],
    'current-url' => [],
    'get-articles' => [],
    'save-articles' => ['articles'],
    'search-articles' => ['search'],
    'get-versions' => [],
    'update' => [],
    'wp-synch' => [],
    'app-get-key' => ['app_name'],
    'app-activation' => ['app_name', 'key'],
    'csv-users' => [],
    'csv-conversations' => [],
    'is-agent-typing' => ['conversation_id'],
    'push-notification' => ['title' , 'message', 'interests'],
    'dialogflow-intent' => ['expressions', 'response'],
    'dialogflow-entity' => ['entity_name', 'synonyms'],
    'dialogflow-get-entity' => [],
    'dialogflow-get-token' => [],
    'dialogflow-get-agent' => ['context_name', 'user_id'],
    'dialogflow-set-active-context' => ['context_name'],
    'dialogflow-curl' => ['url_part', 'query'],
    'woocommerce-get-customer' => ['session_key'],
    'woocommerce-get-user-orders' => ['user_id'],
    'woocommerce-get-order' => ['order_id'],
    'woocommerce-get-product' => ['product_id'],
    'woocommerce-get-taxonomies' => ['type'],
    'woocommerce-get-attributes' => [],
    'woocommerce-get-product-id-by-name' => ['name'],
    'woocommerce-get-product-images' => ['product_id'],
    'woocommerce-get-product-taxonomies' => ['product_id'],
    'woocommerce-get-attribute-by-term' => ['term_name'],
    'woocommerce-get-attribute-by-name' => ['name'],
    'woocommerce-is-in-stock' => ['product_id'],
    'woocommerce-coupon' => ['discount', 'expiration'],
    'woocommerce-coupon-check' => ['user_id'],
    'woocommerce-coupon-delete-expired' => [],
    'woocommerce-get-url' => ['type'],
    'woocommerce-get-session' => ['session_key'],
    'woocommerce-get-session-key' => ['user_id'],
    'woocommerce-payment-methods' => [],
    'woocommerce-shipping-locations' => [],
    'woocommerce-get-products' => [],
    'woocommerce-search-products' => ['search'],
    'woocommerce-dialogflow-entities' => [],
    'woocommerce-dialogflow-intents' => []
];

if (!isset($_POST['function'])) {
    sb_api_error(new SBError('missing-function-name', '', 'Function name is required. Get it from the docs.'));
} else {
    $function_name = $_POST['function'];
    if (!isset($functions[$function_name])) {
        sb_api_error(new SBError('function-not-found', $function_name, 'Function ' . $function_name . ' not found. Check the function name.'));
    }
}

if (!isset($_POST['token'])) {
    sb_api_error(new SBError('token-not-found', $function_name, 'Admin token is required. Get it from the Users > Your admin user profile details box.'));
} else if (!sb_api_security($_POST['token'])) {
    sb_api_error(new SBError('invalid-token', $function_name, 'It looks like the token is invalid, please make sure you are using a token from an admin user.'));
}

if (count($functions[$function_name]) > 0) {
    for ($i = 0; $i < count($functions[$function_name]); $i++) {
        if (!isset($_POST[$functions[$function_name][$i]])) {
            sb_api_error(new SBError('missing-argument', $function_name, 'Missing argument: ' . $functions[$function_name][$i]));
        }
    }
}

/*
 * -----------------------------------------------------------
 * # APPS CHECK
 * -----------------------------------------------------------
 *
 * Check if the app required by a method is installed
 *
 */

$apps = [
    'SB_WP'=> ['wp-synch'],
    'SB_DIALOGFLOW'=> ['dialogflow-intent', 'dialogflow-entity', 'dialogflow-get-entity', 'dialogflow-get-token', 'dialogflow-get-agent', 'dialogflow-set-active-context', 'dialogflow-curl', 'send-bot-message'],
    'SB_SLACK'=> ['send-slack-message', 'slack-users', 'archive-slack-channels']
];

foreach ($apps as $key => $value) {
    if ((in_array($function_name, $value) && !defined($key))) {
        sb_api_error(new SBError('app-not-installed', $function_name));
    }
}

/*
 * -----------------------------------------------------------
 * # API ONLY FUNCTIONS
 * -----------------------------------------------------------
 *
 * Additional APIs
 *
 */

switch ($_POST['function']) {
    case 'is-online':
        $result = 'offline';
        $user = sb_db_get('SELECT last_activity, user_type FROM sb_users WHERE id = "' . $_POST['user_id'] . '" LIMIT 1');
        if (isset($user['last_activity']) && sb_is_online($user['last_activity'])) {
            $result = 'online';
        } else if (defined('SB_SLACK') && isset($user['user_type']) && sb_is_agent($user['user_type'])) {
            $result = sb_slack_agent_online($_POST['user_id']);
        }
        die(sb_api_success($result));
    case 'get-setting':
        die(sb_api_success(sb_get_setting($_POST['setting'])));
    case 'update-user':
    case 'add-user':
        $values = ['first_name', 'last_name', 'email', 'profile_image', 'password', 'user_type', 'department'];
        $settings = [];
        $extra = isset($_POST['extra']) ? $_POST['extra'] : [];
        for ($i = 0; $i < count($values); $i++) {
            if (isset($_POST[$values[$i]])) {
                $settings[$values[$i]] = [$_POST[$values[$i]]];
            }
        }
        die(sb_api_success($_POST['function'] == 'add-user' ? sb_add_user($settings, $extra) : sb_update_user($_POST['user_id'], $settings, $extra)));
    default:
        require_once('ajax.php');
        break;
}

/*
 * -----------------------------------------------------------
 * # FUNCTIONS
 * -----------------------------------------------------------
 *
 * Help functions used only by the APIs
 *
 */

function sb_api_error($error) {
    $response = ['status' => 'error', 'response' => $error->code()];
    if ($error->message() != '') {
        $response['message'] = $error->message();
    }
    die(json_encode($response));
}

function sb_api_success($result) {
    $response = [];
    if (sb_is_validation_error($result)) {
        $response['success'] = false;
        $response['response'] = $result->code();
    } else {
        $response['success'] = true;
        $response['response'] = $result;
    }
    die(json_encode($response));
}

function sb_api_security($token) {
    $admin = sb_db_get('SELECT * FROM sb_users WHERE token = "' . $_POST['token'] . '" LIMIT 1');
    if (isset($admin['user_type']) && $admin['user_type'] === 'admin') {
        global $SB_LOGIN;
        $SB_LOGIN = ['id' => $admin['id'], 'profile_image' => $admin['profile_image'], 'first_name' => $admin['first_name'], 'last_name' => $admin['last_name'], 'email' => $admin['email'], 'user_type' => 'admin', 'token' => $_POST['token']];
        return true;
    }
    return false;
}

?>