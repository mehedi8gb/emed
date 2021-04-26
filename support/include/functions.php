<?php

/*
 * ==========================================================
 * FUNCTIONS.PHP
 * ==========================================================
 *
 * Main PHP functions files. © 2020 board.support. All rights reserved.
 *
 */

define('SB_VERSION', '3.1.3');

if (!defined('SB_PATH')) {
    $path = dirname(__DIR__, 1);
    if ($path == '') {
        $path = dirname(__DIR__);
    }
    define('SB_PATH', $path);
}

require_once(SB_PATH . '/config.php');
global $SB_CONNECTION;
global $SB_SETTINGS;
global $SB_LOGIN;
global $SB_LANGUAGE;
global $SB_TRANSLATIONS;
const  SELECT_FROM_USERS = 'SELECT id, first_name, last_name, email, profile_image, user_type, creation_time, last_activity, department, token';

/*
 * -----------------------------------------------------------
 * # APPS LOADING
 * -----------------------------------------------------------
 *
 * Load the external apps if availables
 *
 */

$sb_apps = ['dialogflow', 'slack', 'wordpress', 'tickets', 'woocommerce', 'ump'];
for ($i = 0; $i < count($sb_apps); $i++) {
    $file = SB_PATH . '/apps/' . $sb_apps[$i] . '/functions.php';
    if (file_exists($file)) {
        require_once($file);
    }
}

/*
 * -----------------------------------------------------------
 * # DATABASE
 * -----------------------------------------------------------
 *
 * Functions to read, update, delete database records and to connect to the database.
 *
 */

function sb_db_connect() {
    global $SB_CONNECTION;
    if (SB_DB_NAME != '' && isset($SB_CONNECTION) && $SB_CONNECTION->ping()) {
        sb_db_init_settings();
        return true;
    }
    $SB_CONNECTION = new mysqli(SB_DB_HOST, SB_DB_USER, SB_DB_PASSWORD, SB_DB_NAME, defined('SB_DB_PORT') && SB_DB_PORT != '' ? ini_get('mysqli.default_port') : intval(SB_DB_PORT));
    if ($SB_CONNECTION->connect_error) {
        echo 'Connection error. Visit the admin area for more details or open the config.php file and check the database information. Message: ' . $SB_CONNECTION->connect_error . '.';
        return false;
    }
    sb_db_init_settings();
    return true;
}

function sb_db_get($query, $single = true) {
    global $SB_CONNECTION;
    $status = sb_db_connect();
    $value = ($single ? '' : []);
    if ($status) {
        $result = $SB_CONNECTION->query($query);
        if ($result) {
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    if ($single) {
                        $value = $row;
                    } else {
                        array_push($value, $row);
                    }
                }
            }
        } else {
            return sb_db_error('sb_db_get');
        }
    } else {
        return $status;
    }
    return $value;
}

function sb_db_query($query, $return = false) {
    global $SB_CONNECTION;
    $status = sb_db_connect();
    if ($status) {
        $result = $SB_CONNECTION->query($query);
        if ($result) {
            if ($return) {
                if (isset($SB_CONNECTION->insert_id) && $SB_CONNECTION->insert_id > 0) {
                    return $SB_CONNECTION->insert_id;
                } else {
                    return sb_db_error('sb_db_query');
                }
            } else {
                return true;
            }
        } else {
            return sb_db_error('sb_db_query');
        }
    } else {
        return $status;
    }
}

function sb_db_encode($string) {
    global $SB_CONNECTION;
    sb_db_connect();
    return $SB_CONNECTION->real_escape_string($string);
}

function sb_db_escape($value, $escape_special_chars = false) {
    $value = str_replace('"', '\"', $value);
    if ($escape_special_chars) $value = sb_db_encode($value);
    return $value;
}

function sb_db_json_enconde($array) {
    return sb_db_encode(str_replace(['"false"', '"true"'], ['false', 'true'], json_encode($array)));
}

function sb_db_error($function) {
    global $SB_CONNECTION;
    return new SBError('db-error', $function, $SB_CONNECTION->error);
}

function sb_db_check_connection($name = false, $user = false, $password = false, $host = false, $port = false) {
    global $SB_CONNECTION;
    $response = true;
    if ($name === false && defined('SB_DB_NAME')) {
        $name = SB_DB_NAME;
        $user = SB_DB_USER;
        $password = SB_DB_PASSWORD;
        $host = SB_DB_HOST;
        $port = defined('SB_DB_PORT') && SB_DB_PORT != '' ? intval(SB_DB_PORT) : false;
    }
    if ($name === false || $name == '') {
        return 'installation';
    }
    try {
        set_error_handler(function() {}, E_ALL);
    	$SB_CONNECTION = new mysqli($host, $user, $password, $name, $port === false ? ini_get('mysqli.default_port') : intval($port));
        sb_db_init_settings();
    }
    catch (Exception $e) {
        $response = $e->getMessage();
    }
    if ($SB_CONNECTION->connect_error) {
        $response = $SB_CONNECTION->connect_error;
    }
    restore_error_handler();
    return $response;
}

function sb_db_init_settings() {
    global $SB_CONNECTION;
    $SB_CONNECTION->set_charset('utf8mb4');
    $SB_CONNECTION->query("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
}

/*
 * -----------------------------------------------------------
 * # ERROR REPORTING
 * -----------------------------------------------------------
 *
 * Return a error message as json string
 *
 */

class SBError {
    public $error;

    function __construct($error_code, $function = '', $message = '') {
        $this->error = ['message' => $message, 'function' => $function, 'code' => $error_code];
    }

    public function __toString() {
        return $this->message();
    }

    function message() {
        return $this->error['message'];
    }

    function code() {
        return $this->error['code'];
    }

    function function_name() {
        return $this->error['function'];
    }
}

class SBValidationError {
    public $error;

    function __construct($error_code) {
        $this->error = $error_code;
    }

    public function __toString() {
        return $this->error;
    }

    function code() {
        return $this->error;
    }
}

function sb_is_error($object) {
    return is_a($object, 'SBError');
}

function sb_is_validation_error($object) {
    return is_a($object, 'SBValidationError');
}

/*
 * -----------------------------------------------------------
 * # LOGIN AND ACCOUNT
 * -----------------------------------------------------------
 *
 * Check if the login details are corrects and if yes set the login
 * Logout a user
 * Return the logged in user information
 * Return the agent department
 *
 */

function sb_login($email = '', $password = '', $user_id = '', $user_token = '') {
    global $SB_LOGIN;
    $valid_login = false;
    $result = null;
    if ($email != '' && $password != '') {

        // Login for registered users and agents
        $result = sb_db_get('SELECT id, profile_image, first_name, last_name, email, user_type, token, department, password FROM sb_users WHERE email = "' . $email . '" LIMIT 1');
        if (sb_is_error($result)) return $result;
        if (isset($result) && $result != '' && isset($result['password']) && isset($result['user_type']) && sb_password_verify($password, $result['password'])) {
            $valid_login = true;
        }
    } else if ($user_id != '' && $user_token != '') {

        // Login for visitors
        $result = sb_db_get('SELECT id, profile_image, first_name, last_name, email, user_type, token FROM sb_users WHERE id = "' . $user_id . '" AND token = "' . $user_token . '" LIMIT 1');
        if (sb_is_error($result)) return $result;
        if (isset($result['user_type']) && isset($result['token'])) {
            $valid_login = true;
        }
    }
    if ($valid_login) {
        $settings =  ['id' => $result['id'], 'profile_image' => $result['profile_image'], 'first_name' => $result['first_name'], 'last_name' => $result['last_name'], 'email' => $result['email'], 'user_type' => $result['user_type'], 'token' => $result['token']];
        if (isset($result['department'])) {
            $settings['department'] = $result['department'];
        }
        sb_set_cookie_login($settings);
        $SB_LOGIN = $settings;
        return [$settings, sb_encryption('encrypt', json_encode($settings))];
    }
    return false;
}

function sb_update_login($profile_image, $first_name, $last_name, $email, $department = '', $user_type = false) {
    global $SB_LOGIN;
    $settings = sb_get_cookie_login();
    if (empty($settings)) $settings = [];
    $settings['profile_image'] = $profile_image;
    $settings['first_name'] = $first_name;
    $settings['last_name'] = $last_name;
    $settings['email'] = $email;
    $settings['department'] = $department == 'NULL' || $department == '' || $department === false ? null : $department;
    if ($user_type != false) {
        $settings['user_type'] = $user_type;
    }
    if (!headers_sent()) {
        sb_set_cookie_login($settings);
    }
    $SB_LOGIN = $settings;
    return [$settings, sb_encryption('encrypt', json_encode($settings))];
}

function sb_logout() {
    global $SB_LOGIN;
    if (!headers_sent()) {
        setcookie('sb-login', '', time() - 3600);
    }
    $SB_LOGIN = null;
    return true;
}

function sb_get_active_user($login_data = false, $db = false, $login_app = false) {
    global $SB_LOGIN;
    $return = false;
    if (isset($SB_LOGIN)) {
        $return = $SB_LOGIN;
    }
    if ($return === false && !empty($login_data)) {
        $return = json_decode(sb_encryption('decrypt', $login_data), true);
    }
    if ($return === false) {
        $return = sb_get_cookie_login();
    }
    if ($login_app !== false) {
        if ($return === false || !isset($return['email'])) {
            $return = sb_wp_get_active_user($login_app);
            if ($return !== false) {
                $return = $return[0];
            }
        } else {
            $user = json_decode(sb_encryption('decrypt', $login_app), true);
            if (isset($user['email']) && $user['email'] != $return['email']) {
                $return = sb_wp_get_active_user($login_app)[0];
            }
        }
    }
    if ($db && $return != false && isset($return['id'])) {
        if (intval(sb_db_get('SELECT COUNT(*) as count FROM sb_users WHERE id = "' . $return['id'] . '"')['count']) == 0) {
            $return = false;
        }
    }
    if ($return !== false && !isset($SB_LOGIN)) {
        $SB_LOGIN = $return;
    }
    return $return;
}

function sb_set_cookie_login($value) {
    if (!headers_sent()) {
        setcookie('sb-login', sb_encryption('encrypt', json_encode($value)), time() + 315569260, '/');
    }
}

function sb_get_cookie_login() {
    if (isset($_COOKIE['sb-login'])) {
        $response = json_decode(sb_encryption('decrypt', $_COOKIE['sb-login']), true);
        return empty($response) ? false : $response;
    }
    return false;
}

function sb_password_verify($password, $hash) {
    $success = password_verify($password, $hash);
    if (!$success && defined('SB_WP')) {
        $wp_hasher = new SBPasswordHash(8, true);
        $success = $wp_hasher->CheckPassword($password, $hash);
    }
    return $success;
}

function sb_is_agent($user = false) {
    if ($user === '') {
        return false;
    }
    $user = $user === false ? sb_get_active_user() : (is_string($user) ? ['user_type' => $user] : $user);
    if ($user == false) {
        return false;
    }
    return $user['user_type'] == 'agent' || $user['user_type'] == 'admin' || $user['user_type'] == 'bot';
}

function sb_get_agent_department() {
    if (sb_is_agent() && !defined('SB_API')) {
        $user = sb_get_active_user();
        return isset($user['department']) && $user['department'] != '' ? $user['department'] : false;
    }
    return false;
}

/*
 * -----------------------------------------------------------
 * # JAVASCRIPT GLOBAL AND ADMIN
 * -----------------------------------------------------------
 *
 * Global Javascripts and admin codes to be printed to the user and admin page.
 *
 */

function sb_js_global() {
    global $SB_LANGUAGE;
    $ajax_url = str_replace('//include', '/include', SB_URL . '/include/ajax.php');
    $code = '<script>';
    $code .= 'var SB_AJAX_URL = "' . $ajax_url . '";';
    $code .= 'var SB_URL = "' . SB_URL . '";';
    $code .= 'var SB_LANG = ' . ($SB_LANGUAGE == false ? 'false' : json_encode($SB_LANGUAGE)) . ';';
    $code .= '</script>';
    echo $code;
}

function sb_js_admin() {
    $active_user = sb_get_active_user();
    $code = '<script>';
    $code .= 'var SB_ADMIN_SETTINGS = { "bot-id": ' . sb_get_bot_id() . ', "close-message": ' . (sb_get_multi_setting('close-message', 'close-active') ? 'true' : 'false') . ', "routing": ' . ((!$active_user || $active_user['user_type'] == 'agent') && (sb_get_multi_setting('queue', 'queue-active') || sb_get_setting('routing')) ? 'true' : 'false') . ', "desktop-notifications": "' . sb_get_setting('desktop-notifications') .'", "push-notifications": "' . sb_get_multi_setting('push-notifications', 'push-notifications-active') . '", "flash-notifications": "' . sb_get_setting('flash-notifications') . '", "notifications-icon" : "' . (empty(sb_get_setting('notifications-icon')) ? SB_URL . '/media/icon.png': '') . '", "auto-updates": "' . sb_get_setting('auto-updates') .'", "sounds": "' . sb_get_setting('chat-sound-admin') . '"' . (defined('SB_WOOCOMMERCE') ? ', "currency": "' . sb_get_setting('wc-currency-symbol') . '", "languages": ' . json_encode(sb_isset(sb_wp_language_settings(), 'languages', '[]')) : '') . ' };';
    $wp_apps = '';
    if ($active_user) {
        $code .= 'var SB_ACTIVE_AGENT = { id: "' . $active_user['id'] . '", full_name: "' . sb_get_user_name($active_user) . '", user_type: "' . $active_user['user_type'] . '", profile_image: "' . $active_user['profile_image'] . '", department: "' . sb_isset($active_user, 'department', '') . '"};';
    } else {
        $code .= 'var SB_ACTIVE_AGENT = { id: "", full_name: "", user_type: "", profile_image: "" };';
    }
    if (defined('SB_WP')) {
        $wp_apps = ', woocommerce: "' . (defined('SB_WOOCOMMERCE') ? SB_WOOCOMMERCE : -1) . '"';
        $code .= 'var SB_WP = true;';
    }
    $code .= 'var SB_TRANSLATIONS = ' . json_encode(sb_get_current_translations()) . ';';
    $code .= 'var SB_VERSIONS = { sb: "' . SB_VERSION . '", dialogflow: "' . (defined('SB_DIALOGFLOW') ? SB_DIALOGFLOW : -1) . '", slack: "' . (defined('SB_SLACK') ? SB_SLACK : -1) . '", tickets: "' . (defined('SB_TICKETS') ? SB_TICKETS : -1) . '", ump: "' . (defined('SB_UMP') ? SB_UMP : -1) . '"' . $wp_apps . '};';
    $code .= '</script>';
    echo $code;
}

/*
 * -----------------------------------------------------------
 * # ADD NEW USER
 * -----------------------------------------------------------
 *
 * Add a new user or agent.
 *
 */

function sb_add_user($settings = [], $settings_extra = [], $login_app = false) {
    $keys = ['profile_image', 'first_name', 'last_name', 'email', 'user_type', 'password', 'department'];
    for ($i = 0; $i < count($keys); $i++) {
        $settings[$keys[$i]] = sb_isset($settings, $keys[$i], '');
        if (!is_string($settings[$keys[$i]])) {
            $settings[$keys[$i]] = $settings[$keys[$i]][0];
        }
    }
    if ($settings['email'] != '' && intval(sb_db_get('SELECT COUNT(*) as count FROM sb_users WHERE email = "' . $settings['email'] . '"')['count']) > 0) {
        if (sb_get_setting('duplicate-emails')) {
            $settings['email'] = '';
        } else return new SBValidationError('duplicate-email');
    }
    if ($settings['profile_image'] == '') {
        $settings['profile_image'] = SB_URL . '/media/user.svg';
    }
    if ($settings['first_name'] == '') {
        $name = sb_get_setting('visitor-prefix');
        $settings['first_name'] = $name === false || $name == '' ? 'User' : $name;
        $settings['last_name'] = '#' . rand(0, 99999);
    }
    if ($settings['user_type'] == '') {
        $settings['user_type'] = $settings['email'] != '' ? 'user' : 'visitor';
    } else if (!in_array($settings['user_type'], ['visitor', 'user', 'lead', 'agent', 'admin', 'bot'])) {
        return new SBValidationError('invalid-user-type');
    }
    if ($settings['password'] != '') {
        $settings['password'] = password_hash($settings['password'], PASSWORD_DEFAULT);
    }
    if ($settings['department'] == '') {
        $settings['department'] = 'NULL';
    }
    if (defined('SB_WP') && sb_get_setting('wp-users-system') == 'wp' && $login_app !== false) {
        $wp_settings = sb_wp_get_user($login_app);
        if ($wp_settings !== false) {
            $settings['first_name'] = $wp_settings['first_name'];
            $settings['last_name'] = $wp_settings['last_name'];
            $settings['password'] = $wp_settings['user_pass'];
            $settings['email'] = $wp_settings['user_email'];
            $settings['user_type'] = 'user';
        }
    }

    $now = gmdate('Y-m-d H:i:s');
    $token = bin2hex(openssl_random_pseudo_bytes(20));
    $query = 'INSERT INTO sb_users(first_name, last_name, password, email, profile_image, user_type, creation_time, token, department, last_activity) VALUES ("' . sb_db_escape(htmlspecialchars($settings['first_name'], ENT_NOQUOTES)) . '", "' . sb_db_escape(htmlspecialchars($settings['last_name'], ENT_NOQUOTES)) . '", "' . sb_db_escape(htmlspecialchars($settings['password'], ENT_NOQUOTES)) . '", ' . ($settings['email'] == '' ? 'NULL' : '"' . sb_db_escape(htmlspecialchars($settings['email'], ENT_NOQUOTES)) . '"') . ', "' . sb_db_escape(htmlspecialchars($settings['profile_image'], ENT_NOQUOTES)) . '", "' . $settings['user_type'] . '", "' . $now . '", "' . $token . '", ' . $settings['department'] . ', "' . $now . '")';
    $result = sb_db_query($query, true);

    if (!sb_is_error($result) && is_int($result) && $result > 0 && isset($settings_extra)) {
        sb_add_new_user_extra($result, $settings_extra);
    }
    if (!sb_is_error($result) && !sb_is_agent() && ($settings['user_type'] == 'user' || sb_get_setting('visitor-autodata'))) {
        sb_user_autodata($result);
    }
    return $result;
}

/*
 * -----------------------------------------------------------
 * # ADD NEW USER EXTRA INFORMATION
 * -----------------------------------------------------------
 *
 * Add a new user extra details
 *
 */

function sb_add_new_user_extra($user_id, $settings) {
    $query = '';
    foreach ($settings as $key => $setting) {
        if (is_array($setting) && $setting[0] != '') {
            $query .= '("' . $user_id . '", "' . $key . '", "' . sb_db_escape(htmlspecialchars($setting[1], ENT_NOQUOTES)) . '", "' . sb_db_escape(htmlspecialchars($setting[0], ENT_NOQUOTES)) . '"),';
        }
    }
    if ($query != '') {
        $query = 'INSERT IGNORE INTO sb_users_data(user_id, slug, name, value) VALUES ' . substr($query, 0, -1);
        return sb_db_query($query);
    }
    return false;
}

/*
 * -----------------------------------------------------------
 * # ADD NEW VISITOR USER
 * -----------------------------------------------------------
 *
 * Add a new visitor user
 *
 */

function sb_add_user_and_login($settings, $settings_extra, $login_app = false) {
    $response = sb_add_user($settings, $settings_extra, $login_app);
    if (is_int($response)) {
        $token = sb_db_get('SELECT token FROM sb_users WHERE id = "' . $response . '"');
        return sb_login('', '', $response, $token['token']);
    }
    return $response;
}

/*
 * -----------------------------------------------------------
 * # DELETE USER
 * -----------------------------------------------------------
 *
 * Delete the users and all the related information (conversations, messages)
 *
 */

function sb_delete_user($user_id) {
    return sb_db_query('DELETE FROM sb_users WHERE id = "' . $user_id . '"');
}

function sb_delete_users($user_ids) {
    $query = '';
    for ($i = 0; $i < count($user_ids); $i++) {
        $query .= $user_ids[$i] . ',';
    }
    return sb_db_query('DELETE FROM sb_users WHERE id IN (' . substr($query, 0, -1) . ')');
}

function sb_delete_leads() {
    return sb_db_query('DELETE FROM sb_users WHERE user_type = "lead"');
}

/*
 * -----------------------------------------------------------
 * # UPDATE USERS AND AGENTS
 * -----------------------------------------------------------
 *
 * Update a user or agent.
 *
 */

function sb_update_user($user_id, $settings, $settings_extra = []) {
    $keys = ['profile_image', 'first_name', 'last_name', 'email', 'user_type', 'password', 'department'];
    for ($i = 0; $i < count($keys); $i++) {
        $settings[$keys[$i]] = sb_isset($settings, $keys[$i], '');
        if (!is_string($settings[$keys[$i]])) {
            $settings[$keys[$i]] = $settings[$keys[$i]][0];
        }
    }

    $profile_image = $settings['profile_image'];
    $first_name = $settings['first_name'];
    $last_name = $settings['last_name'];
    $email = $settings['email'];
    $user_type = $settings['user_type'];
    $password = isset($settings['password']) && $settings['password'] != '********' ? $settings['password'] : '';
    $department = sb_isset($settings, 'department', 'NULL');
    $active_user = sb_get_active_user();
    $query = ', last_name = "' . sb_db_escape($last_name) . '"';

    if ($email != '' && intval(sb_db_get('SELECT COUNT(*) as count FROM sb_users WHERE email = "' . $email . '" AND id <> ' . $user_id)['count']) > 0) {
        return new SBValidationError('duplicate-email');
    }

    if ($profile_image == '') {
        $profile_image = SB_URL . '/media/user.svg';
    }
    if ($first_name != '') {
        $query .= ', first_name = "' . sb_db_escape($first_name) . '"';
    }
    if ($password != '') {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $query .= ', password = "' . sb_db_escape($password) . '"';
    }
    if ($department == '') {
        $department = 'NULL';
    }
    if (!sb_is_agent($user_type)) {
        $user_type = $email != '' ? 'user' : (intval(sb_db_get('SELECT COUNT(*) as count FROM sb_users WHERE id = "' . $user_id . '"')['count']) > 0 ? 'lead' : 'visitor');
    }

    $query_final = 'UPDATE sb_users SET profile_image = "' . sb_db_escape($profile_image) . '", user_type = "' . $user_type . '", email = ' . (strlen($email) == 0 ? 'NULL' : '"' . sb_db_escape($email) . '"') . ', department = ' . $department . $query . ' WHERE id = "' . $user_id . '"';
    $result = sb_db_query($query_final);

    foreach ($settings_extra as $key => $setting) {
        if (is_array($setting)) {
            sb_db_query('REPLACE INTO sb_users_data SET name = "' . sb_db_escape($setting[1]) . '", value = "' . sb_db_escape($setting[0]) . '", slug = "' . $key . '", user_id = "' . $user_id . '"');
        }
    }
    sb_db_query('DELETE FROM sb_users_data WHERE user_id = "' . $user_id . '" AND value = ""');

    if (defined('SB_SLACK') && $first_name != '' && $last_name != '' && sb_get_setting('slack-active')) {
        sb_slack_rename_channel($user_id, $first_name . ' ' . $last_name);
    }
    if ($active_user != false && $active_user['id'] == $user_id) {
        $result = sb_update_login($profile_image, $first_name, $last_name, $email, $department, $user_type);
        sb_user_autodata($user_id);
    }
    return $result;
}

/*
 * -----------------------------------------------------------
 * # UPDATE A USER AND AGENT DETAIL
 * -----------------------------------------------------------
 *
 * Update a user or agent detail or extra detail.
 *
 */

function sb_update_user_value($user_id, $slug, $value, $name = '') {
    if (empty($value)) {
        return sb_db_query('DELETE FROM sb_users_data WHERE user_id = "' . $user_id . '" AND slug = "' . sb_db_escape($slug) . '"');
    }
    if (in_array($slug, ['profile_image', 'first_name', 'last_name', 'email', 'password', 'department', 'user_type', 'last_activity', 'typing'])) {
        if ($slug == 'password') $value = password_hash($value, PASSWORD_DEFAULT);
        return sb_db_query('UPDATE sb_users SET ' . sb_db_escape($slug) . ' = "' . sb_db_escape($value) . '" WHERE id = "' . $user_id . '"');
    }
    return sb_db_query('REPLACE INTO sb_users_data SET name = "' . sb_db_escape($name) . '", value = "' . sb_db_escape($value) . '", slug = "' . sb_db_escape($slug) . '", user_id = "' . $user_id . '"');
}

/*
 * -----------------------------------------------------------
 * # UPDATE VISITOR TO LEAD
 * -----------------------------------------------------------
 *
 * Update a visitor to convert it to lead
 *
 */

function sb_update_user_to_lead($user_id) {
    sb_user_autodata($user_id);
    return sb_update_user_value($user_id, 'user_type', 'lead');
}

/*
 * -----------------------------------------------------------
 * # UPDATE USER AND MESSAGE
 * -----------------------------------------------------------
 *
 * Update the current user and a conversation message
 *
 */

function sb_update_user_and_message($user_id, $settings, $settings_extra = [], $message_id = '', $message = '', $payload = false) {
    $result = sb_update_user($user_id, $settings, $settings_extra);
    if (sb_is_validation_error($result) && $result->code() == 'duplicate-email') {
        return new SBValidationError('duplicate-email');
    }
    if ($message_id != '' && $message != '') {
        sb_update_message($message_id, $message, false, $payload);
    }
    return $result;
}

/*
 * -----------------------------------------------------------
 * # GET ALL USERS
 * -----------------------------------------------------------
 *
 * Return all users, agents and admins
 *
 */

function sb_get_users($sorting = ['creation_time' , 'DESC'], $user_types = [], $search = '', $pagination = 0) {
    $query = '';
    $query_search = '';
    $count = count($user_types);
    if ($count) {
        for ($i = 0; $i < $count; $i++) {
            $query .= 'user_type = "' . $user_types[$i] . '" OR ';
        }
        $query = '(' . substr($query, 0, strlen($query) - 4) . ')';
    }
    if ($search != '') {
        $searched_users = sb_search_users($search);
        $count_search = count($searched_users);
        if ($count_search > 0) {
            for ($i = 0; $i < $count_search; $i++) {
                $query_search .= $searched_users[$i]['id'] . ',';
            }
            $query .= ($query != '' ? ' AND ' : '') . 'id IN (' . substr($query_search, 0, -1) . ')';
        }
    }
    if ($query != '') {
        $query = ' WHERE user_type <> "bot" AND ' . $query;
    } else {
        $query = ' WHERE user_type <> "bot"';
    }
    $users = sb_db_get(SELECT_FROM_USERS . ' FROM sb_users ' . $query . ' ORDER BY ' . $sorting[0] . ' ' . $sorting[1] . ' LIMIT ' . ($pagination * 100) . ',100', false);
    if (isset($users) && is_array($users)) {
        return $users;
    } else {
        return new SBError('db-error', 'sb_get_users', $users);
    }
}

/*
 * -----------------------------------------------------------
 * # SEARCH USERS
 * -----------------------------------------------------------
 *
 * Search users based on the gived keyword
 *
 */

function sb_search_users($search) {
    $search = trim(sb_db_escape($search));
    $query = '';
    if (strpos($search, ' ') > 0) {
        $search = explode(' ', $search);
    } else {
        $search = [$search];
    }
    for ($i = 0; $i < count($search); $i++) {
    	$query .= 'first_name LIKE "%' . $search[$i] . '%" OR last_name LIKE "%' . $search[$i] . '%" OR ';
    }
    $result = sb_db_get('SELECT * FROM sb_users WHERE user_type <> "bot" AND ' . $query . ' email LIKE "%' . $search[0] . '%" OR id IN (SELECT user_id FROM sb_users_data WHERE value LIKE "%' . $search[0] . '%") GROUP BY sb_users.id;', false);
    if (isset($result) && is_array($result)) {
        return $result;
    } else {
        return new SBError('db-error', 'sb_search_users', $result);
    }
}

/*
 * -----------------------------------------------------------
 * # GET ALL NEW USERS
 * -----------------------------------------------------------
 *
 * Return the users registered after the given date
 *
 */

function sb_get_new_users($datetime) {
    $users = sb_db_get(SELECT_FROM_USERS . ' FROM sb_users WHERE  user_type <> "bot" AND creation_time > "' . $datetime . '" ORDER BY creation_time DESC', false);
    if (isset($users) && is_array($users)) {
        return $users;
    } else {
        return new SBError('db-error', 'sb_get_new_users', $users);
    }
}

/*
 * -----------------------------------------------------------
 * # GET USER
 * -----------------------------------------------------------
 *
 * Return the user with the given id
 *
 */

function sb_get_user($user_id, $extra = false) {
    $user = sb_db_get(SELECT_FROM_USERS . ', password FROM sb_users WHERE id = ' . $user_id);
    if (isset($user) && is_array($user)) {
        if ($extra) {
            $user['details'] = sb_get_user_extra($user_id);
        }
        return $user;
    } else {
        return false;
    }
}

/*
 * -----------------------------------------------------------
 * # COUNT USERS
 * -----------------------------------------------------------
 *
 * Return the users count grouped by user type
 *
 */

function sb_count_users() {
    return sb_db_get('SELECT SUM(CASE WHEN user_type <> "bot" THEN 1 ELSE 0 END) AS `all`, SUM(CASE WHEN user_type = "lead" THEN 1 ELSE 0 END) AS `lead`, SUM(CASE WHEN user_type = "user" THEN 1 ELSE 0 END) AS `user`, SUM(CASE WHEN user_type = "visitor" THEN 1 ELSE 0 END) AS `visitor` FROM sb_users');
}

/*
 * -----------------------------------------------------------
 * # GET USER ADDITINAL DETAILS
 * -----------------------------------------------------------
 *
 * Return the user additional details
 *
 */

function sb_get_user_extra($user_id) {
    $details = sb_db_get('SELECT slug, name, value FROM sb_users_data WHERE user_id = ' . $user_id, false);
    if (isset($details) && is_array($details)) {
        return $details;
    } else {
        return new SBError('db-error', 'sb_get_user_extra', $details);
    }
}

/*
 * -----------------------------------------------------------
 * # GET AGENT
 * -----------------------------------------------------------
 *
 * Return the agent or admin with the given id
 *
 */

function sb_get_agent($agent_id) {
    $user = sb_db_get('SELECT id, first_name, last_name, profile_image, department FROM sb_users WHERE (user_type = "admin" OR user_type = "agent" OR user_type = "bot") AND id = ' . $agent_id);
    if (isset($user) && is_array($user)) {
        $user['details'] = sb_get_user_extra($agent_id);
        for ($i = 0; $i < count($user['details']); $i++) {
            if ($user['details'][$i]['slug'] == 'country') {
                $country = $user['details'][$i]['value'];
                $countries = json_decode(file_get_contents(SB_PATH . '/resources/json/countries.json'), true);
                $user['country_code'] = $countries[$country];
                if (isset($countries[$country]) && file_exists(SB_PATH . '/media/flags/' . strtolower($countries[$country]) . '.png')) {
                    $user['flag'] = strtolower($countries[$country]) . '.png';
                }
                break;
            }
        }
        return $user;
    } else {
        return false;
    }
}

/*
 * -----------------------------------------------------------
 * # GET CONVERSATION USERS DETAILS
 * -----------------------------------------------------------
 *
 * Return the user details of each conversation. This function is used internally by other functions.
 *
 */

function sb_get_conversations_users($conversations) {
    if (count($conversations) > 0) {
        $code = '(';
        for ($i = 0; $i < count($conversations); $i++) {
            $code .= $conversations[$i]['conversation_id'] . ',';
        }
        $code = substr($code, 0, -1) . ')';
        $result = sb_db_get('SELECT sb_users.id, sb_users.first_name, sb_users.last_name, sb_users.profile_image, sb_users.user_type, sb_conversations.id as conversation_id, sb_conversations.status_code, sb_conversations.title FROM sb_users, sb_conversations WHERE sb_users.id = sb_conversations.user_id AND sb_conversations.id IN ' . $code, false);
        for ($i = 0; $i < count($conversations); $i++) {
            $conversation_id = $conversations[$i]['conversation_id'];
            for ($j = 0; $j < count($result); $j++) {
                if ($conversation_id == $result[$j]['conversation_id']) {
                    $conversations[$i]['first_name'] = $result[$j]['first_name'];
                    $conversations[$i]['last_name'] = $result[$j]['last_name'];
                    $conversations[$i]['profile_image'] = $result[$j]['profile_image'];
                    $conversations[$i]['user_id'] = $result[$j]['id'];
                    $conversations[$i]['conversation_status_code'] = $result[$j]['status_code'];
                    $conversations[$i]['user_type'] = $result[$j]['user_type'];
                    break;
                }
            }
        }
    }
    return $conversations;
}

/*
 * -----------------------------------------------------------
 * # GET USER NAME
 * -----------------------------------------------------------
 *
 * Return the user name
 *
 */

function sb_get_user_name($user = false) {
    $user = $user === false ? sb_get_active_user() : $user;
    return substr(sb_isset($user, 'last_name', '-'), 0, 1) != '#' ? trim(sb_isset($user, 'first_name', '') . ' ' . sb_isset($user, 'last_name', '')) : sb_get_setting('visitor-default-name', '');
}

function sb_subscribe_email($email) {
    $settings = sb_isset(sb_get_external_settings('emails'), 'email-subscribe');
    if ($settings) {
        return sb_email_send($email, sb_merge_fields($settings[0]['email-subscribe-subject'][0]), sb_merge_fields($settings[0]['email-subscribe-content'][0]));
    }
    return false;
}

/*
 * -----------------------------------------------------------
 * # GET CONVERSATIONS
 * -----------------------------------------------------------
 *
 * Return the messages grouped by conversation
 *
 */

function sb_get_conversations($pagination = 0, $status_code = 0, $routing = false) {
    $status_code = $status_code == 0 ? 'AND sb_conversations.status_code <> 3 AND sb_conversations.status_code <> 4' : 'AND sb_conversations.status_code = ' . $status_code;
    $result = sb_db_get('SELECT sb_messages.*, sb_users.user_type as message_user_type FROM sb_messages, sb_users, sb_conversations WHERE sb_users.id = sb_messages.user_id ' . $status_code . ' AND sb_conversations.id = sb_messages.conversation_id' . (sb_get_agent_department() !== false ? ' AND sb_conversations.department = ' . sb_get_agent_department() : '') . sb_routing_db($routing) . ' AND sb_messages.creation_time IN (SELECT max(creation_time) latest_creation_time FROM sb_messages GROUP BY conversation_id) GROUP BY conversation_id ORDER BY sb_conversations.status_code DESC, sb_messages.creation_time DESC LIMIT ' . ($pagination * 100) . ',100', false);
    if (isset($result) && is_array($result)) {
        return sb_get_conversations_users($result);
    } else {
        return new SBError('db-error', 'versations', $result);
    }
}

/*
 * -----------------------------------------------------------
 * # GET NEW CONVERSATIONS
 * -----------------------------------------------------------
 *
 * Return only the conversations or messages older than the given date
 *
 */

function sb_get_new_conversations($datetime, $routing = false) {
    $result = sb_db_get('SELECT m.*, u.user_type as message_user_type FROM sb_messages m, sb_users u, sb_conversations c WHERE m.creation_time IN (SELECT max(creation_time) latest_creation_time FROM sb_messages WHERE creation_time > "' . $datetime . '" GROUP BY conversation_id) AND u.id = m.user_id AND c.id = m.conversation_id AND c.status_code <> 3 AND c.status_code <> 4' . (sb_get_agent_department() !== false ? ' AND c.department = ' . sb_get_agent_department() : '') . sb_routing_db($routing, 'c') . ' GROUP BY conversation_id ORDER BY m.creation_time DESC', false);
    if (isset($result) && is_array($result)) {
        if (count($result) > 0) {
            return sb_get_conversations_users($result);
        } else {
            return [];
        }
    } else {
        return new SBError('db-error', 'sb_get_new_conversations', $result);
    }
}

/*
 * -----------------------------------------------------------
 * # GET CONVERSATION
 * -----------------------------------------------------------
 *
 * Return the messages of the requested conversation
 *
 */

function sb_get_conversation($user_id = false, $conversation_id) {
    $messages = sb_db_get('SELECT sb_messages.*, sb_users.first_name, sb_users.last_name, sb_users.profile_image, sb_users.user_type FROM sb_messages, sb_users, sb_conversations WHERE sb_messages.conversation_id = "' . $conversation_id . '"' . ($user_id === false && sb_is_agent() ? '' : ' AND sb_conversations.user_id = "' . $user_id . '"') . ' AND sb_messages.conversation_id = sb_conversations.id AND sb_users.id = sb_messages.user_id ORDER BY sb_messages.creation_time ASC', false);
    if (isset($messages) && is_array($messages)) {
        $details = sb_db_get('SELECT sb_users.id as user_id, sb_users.first_name, sb_users.last_name, sb_users.profile_image, sb_users.user_type, sb_conversations.id, sb_conversations.title, sb_conversations.creation_time, sb_conversations.status_code as conversation_status_code, sb_conversations.department, sb_conversations.agent_id FROM sb_users, sb_conversations WHERE sb_conversations.id = "' . $conversation_id . '"' . ($user_id === false && sb_is_agent() ? '' : ' AND sb_users.id = "' . $user_id . '"') . ' AND sb_users.id = sb_conversations.user_id LIMIT 1');
        return ['messages' => $messages, 'details' => $details];
    } else {
        return new SBError('db-error', 'sb_get_conversation', $messages);
    }
}

/*
 * -----------------------------------------------------------
 * # GET NEW MESSAGES OF A CONVERSATION
 * -----------------------------------------------------------
 *
 * Return only the conversation messages older than the given date
 *
 */

function sb_get_new_messages($user_id, $conversation_id, $datetime) {
    $result = sb_db_get('SELECT sb_messages.*, sb_users.first_name, sb_users.last_name, sb_users.profile_image, sb_users.user_type FROM sb_messages, sb_users, sb_conversations WHERE sb_messages.creation_time > "' . $datetime . '" AND sb_messages.conversation_id = "' . $conversation_id . '" AND sb_users.id = sb_messages.user_id AND sb_conversations.user_id = "' . $user_id . '" AND sb_messages.conversation_id = sb_conversations.id ORDER BY sb_messages.creation_time ASC', false);
    if (isset($result) && is_array($result)) {
        return $result;
    } else {
        return new SBError('db-error', 'sb_get_new_messages', $result);
    }
}

/*
 * -----------------------------------------------------------
 * # SEARCH CONVERSATIONS
 * -----------------------------------------------------------
 *
 * Search conversations by searching user details and messages contents
 *
 */

function sb_search_conversations($search, $routing) {
    $search = sb_db_escape(mb_strtolower($search));
    $department = sb_get_agent_department();
    $result = sb_db_get('SELECT sb_messages.*, sb_users.user_type as message_user_type FROM sb_messages, sb_users' . ($department !== false || $routing ? ', sb_conversations' : '') . ' WHERE sb_users.id = sb_messages.user_id' . ($department !== false ? ' AND sb_conversations.id = sb_messages.conversation_id AND sb_conversations.department = ' . $department : '') . sb_routing_db($routing) . ($routing && $department === false ? ' AND sb_conversations.id = sb_messages.conversation_id' : '') .' AND (LOWER(sb_messages.message) LIKE "%' . $search . '%" OR LOWER(sb_messages.attachments) LIKE "%' . $search . '%" OR LOWER(sb_users.first_name) LIKE "%' . $search . '%" OR LOWER(sb_users.last_name) LIKE "%' . $search . '%" OR LOWER(sb_users.email) LIKE "%' . $search . '%") GROUP BY sb_messages.conversation_id ORDER BY sb_messages.creation_time DESC', false);
    if (isset($result) && is_array($result)) {
        return sb_get_conversations_users($result);
    } else {
        return new SBError('db-error', 'sb_search_conversations', $result);
    }
}

function sb_search_user_conversations($search, $user_id = false) {
    $search = sb_db_escape(mb_strtolower($search));
    return sb_db_get('SELECT sb_messages.*, sb_users.first_name, sb_users.last_name, sb_users.profile_image, sb_users.user_type, sb_conversations.status_code AS conversation_status_code, sb_conversations.title FROM sb_messages, sb_users, sb_conversations WHERE sb_messages.conversation_id = sb_conversations.id AND sb_users.id = sb_conversations.user_id AND sb_users.id = ' . ($user_id === false ? sb_get_active_user()['id'] : $user_id) . ' AND (LOWER(sb_messages.message) LIKE "%' . $search . '%" OR LOWER(sb_messages.attachments) LIKE "%' . $search . '%" OR LOWER(sb_conversations.title) LIKE "%' . $search . '%") GROUP BY sb_messages.conversation_id ORDER BY sb_messages.creation_time DESC', false);
}

/*
 * -----------------------------------------------------------
 * # NEW CONVERSATION
 * -----------------------------------------------------------
 *
 * Create a new user covnersation and return his id
 *
 */

function sb_new_conversation($user_id, $status_code = 0, $title = '', $department = -1, $agent_id = -1, $routing = false) {
    if (!sb_isset_num($agent_id) && $routing) {
        $agent_id = sb_routing(-1, $department);
    }
    $conversation_id = sb_db_query('INSERT INTO sb_conversations(user_id, title, status_code, creation_time, department, agent_id) VALUES ("' . $user_id . '", "' . sb_db_escape(htmlspecialchars(ucfirst($title), ENT_NOQUOTES)) . '", "' . ($status_code == -1 ? 2 : $status_code) . '", "' . gmdate('Y-m-d H:i:s') . '", ' . (sb_isset_num($department) ? $department : 'NULL') . ', ' . (sb_isset_num($agent_id) ? $agent_id : 'NULL') . ')', true);
    if (is_int($conversation_id)) {
        return sb_get_conversation($user_id, $conversation_id);
    } else if (sb_is_error($conversation_id) && sb_db_get('SELECT count(*) as count FROM sb_users WHERE id = "' . $user_id . '"')['count'] == 0) {
        return new SBValidationError('user-not-found');
    }
    return $conversation_id;
}

/*
 * -----------------------------------------------------------
 * # GET USER CONVERSATIONS
 * -----------------------------------------------------------
 *
 * Return all the conversations of the user
 *
 */

function sb_get_user_conversations($user_id, $exclude_id = -1) {
    $exclude = $exclude_id != -1 ? ' AND sb_messages.conversation_id <> "' . $exclude_id . '"' : '';
    return sb_db_get('SELECT sb_messages.*, sb_users.first_name, sb_users.last_name, sb_users.profile_image, sb_users.user_type, sb_conversations.status_code AS conversation_status_code, sb_conversations.department, sb_conversations.title FROM sb_messages, sb_users, sb_conversations WHERE sb_users.id = sb_messages.user_id' . (sb_get_agent_department() !== false ? ' AND sb_conversations.department = ' . sb_get_agent_department() : '') . ' AND sb_messages.conversation_id = sb_conversations.id AND sb_messages.creation_time IN (SELECT max(sb_messages.creation_time) AS latest_creation_time FROM sb_messages, sb_conversations WHERE sb_messages.conversation_id = sb_conversations.id AND sb_conversations.user_id = "' . $user_id . '"' . $exclude . ' GROUP BY conversation_id) GROUP BY conversation_id ORDER BY creation_time DESC', false);
}

/*
 * -----------------------------------------------------------
 * # GET THE LAST USER CONVERSATION OR CREATE A NEW
 * -----------------------------------------------------------
 *
 * Return the ID of the last user conversation if any, otherwise create a new conversation and return its ID
 *
 */

function sb_get_last_conversation_id_or_create($user_id, $conversation_status_code = 1) {
    $conversations = sb_get_user_conversations($user_id);
    if ($conversations) {
        return $conversations[0]['conversation_id'];
    } else {
        return sb_isset(sb_isset(sb_new_conversation($user_id, $conversation_status_code), 'details'), 'id');
    }
}

/*
 * -----------------------------------------------------------
 * # GET NEW USER CONVERSATIONS
 * -----------------------------------------------------------
 *
 * Return only the conversations older than the given date
 *
 */

function sb_get_new_user_conversations($user_id, $datetime) {
    return sb_db_get('SELECT sb_messages.*, sb_users.first_name, sb_users.last_name, sb_users.profile_image, sb_users.user_type, sb_conversations.status_code AS conversation_status_code, sb_conversations.department, sb_conversations.title FROM sb_messages, sb_users, sb_conversations WHERE sb_users.id = sb_messages.user_id AND sb_messages.conversation_id = sb_conversations.id AND sb_messages.creation_time IN (SELECT max(sb_messages.creation_time) AS latest_creation_time FROM sb_messages, sb_conversations WHERE sb_messages.creation_time > "' . $datetime . '" AND sb_messages.conversation_id = sb_conversations.id AND sb_conversations.user_id = "' . $user_id . '" GROUP BY conversation_id) GROUP BY conversation_id ORDER BY creation_time DESC', false);
}

/*
 * -----------------------------------------------------------
 * # UPDATE CONVERSATION STATUS
 * -----------------------------------------------------------
 *
 * Update a conversation status with one of the allowed stutus:  live = 0, pending = 1, pending user = 2, archive = 3, trash = 4.
 *
 */

function sb_update_conversation_status($conversation_id, $status) {
    if (in_array($status, [0, 1, 2, 3, 4])) {
        return sb_db_query('UPDATE sb_conversations SET status_code = ' . $status . ' WHERE id = '. $conversation_id);
    } else {
        if ($status == 5 && sb_is_agent()) {
            return sb_db_query('DELETE FROM sb_conversations WHERE status_code = 4');
        } else {
            return new SBValidationError('invalid-status-code');
        }
    }
}

/*
 * -----------------------------------------------------------
 * # UPDATE DEPARTMENT
 * -----------------------------------------------------------
 *
 * Update the conversation department and alert the agents of that department
 *
 */

function sb_update_conversation_department($conversation_id, $department, $message = '') {
    $response = sb_db_query('UPDATE sb_conversations SET department = ' . $department . ' WHERE id = '. $conversation_id);
    if ($response) {
        if ($message != '') {
            $agent = sb_get_active_user();
            $name = sb_get_user_name($agent);
            sb_email_create(-1, $name, $agent['profile_image'], $message . '<br><br><span style="color:#a8a8a8;font-size:12px;">' . sb_(str_replace('{name}', $name , 'This message has been sent because {name} assigned this conversation to your department.')) . '</span>', [], $department);
        }
        return true;
    }
    return new SBError('department-update-error', 'sb_update_conversation_department', $response);
}

/*
 * -----------------------------------------------------------
 * # QUEUE AND ROUTING
 * -----------------------------------------------------------
 *
 * Update the queue and return the current queue status
 *
 */

function sb_queue($conversation_id, $department = -1) {
    $position = 0;
    $is_new = false;
    $queue_db = sb_db_get('SELECT value FROM sb_settings WHERE name = "queue"');
    $queue = [];
    $cuncurrent_chats = sb_get_setting('queue');
    $index = 0;
    $unix_now = time();
    $unix_min = strtotime('-1 minutes');

    if (!sb_isset_num($department)) $department = -1;
    if (!isset($queue_db) || $queue_db == '') {
        $queue_db = [];
        $is_new = true;
    } else {
        $queue_db = json_decode($queue_db['value'], true);
    }
    for ($i = 0; $i < count($queue_db); $i++){
        if ($unix_min < intval($queue_db[$i][1])) {
            if ($queue_db[$i][0] == $conversation_id) {
                array_push($queue, [$conversation_id, $unix_now, $department]);
                $position = $index + 1;
            } else {
                array_push($queue, $queue_db[$i]);
            }
            if ($department == -1 || $department == $queue_db[$i][2]){
                $index++;
            }
        }
    }
    if (count($queue) == 0 || $position == 1) {
        $counts = sb_db_get('SELECT COUNT(*) as `count`, agent_id FROM sb_conversations WHERE (status_code = 0 OR status_code = 1 OR status_code = 2) AND agent_id IS NOT NULL' . ($department != -1 ? ' AND department = ' . $department : '' ) . ' GROUP BY agent_id', false);
        $cuncurrent_chats = sb_get_setting('queue');
        $cuncurrent_chats = $cuncurrent_chats == false || $cuncurrent_chats['queue-concurrent-chats'] == '' ? 5 : intval($cuncurrent_chats['queue-concurrent-chats']);
        $smaller = false;
        for ($i = 0; $i < count($counts); $i++) {
            $count = intval($counts[$i]['count']);
            if ($count < $cuncurrent_chats && ($smaller === false || $count < $smaller['count'])) {
                $smaller = $counts[$i];
            }
        }
        if ($smaller === false) {
            $query = '';
            for ($i = 0; $i < count($counts); $i++) {
                $query .= $counts[$i]['agent_id'] . ',';
            }
            $smaller = sb_db_get('SELECT id FROM sb_users WHERE user_type = "agent"' . ($query == '' ? '' : ' AND id NOT IN (' . substr($query, 0, -1) . ')') . ' AND last_activity > "' . gmdate('Y-m-d H:i:s', time() - 15) . '"' . ($department != -1 ? ' AND department = ' . $department : '' ) . ' LIMIT 1');
            if (!isset($smaller) || $smaller == '') {
                $smaller = false;
            } else {
                $smaller = ['agent_id' => $smaller['id']];
            }
        }
        if ($smaller !== false) {
            sb_routing_assign_conversation($smaller['agent_id'], $conversation_id);
            array_shift($queue);
            $position = 0;
        } else if ($position == 0) {
            array_push($queue, [$conversation_id, $unix_now, $department]);
            $position = $index + 1;
        }
    } else if ($position == 0) {
        array_push($queue, [$conversation_id, $unix_now, $department]);
        $position = $index + 1;
    }
    if ($is_new) {
        sb_db_query('INSERT INTO sb_settings(name, value) VALUES ("queue", "' . sb_db_escape(json_encode($queue)) . '")');
    } else {
        sb_db_query('UPDATE sb_settings SET value = "' . sb_db_escape(json_encode($queue)) . '" WHERE name = "queue"');
    }
    return $position;
}

function sb_routing_db($routing, $table_name = 'sb_conversations') {
    return $routing && sb_get_active_user()['user_type'] == 'agent' ? ' AND ' . $table_name. '.agent_id = ' . sb_get_active_user()['id'] . '' : '';
}

function sb_routing_assign_conversation($agent_id, $conversation_id) {
    return sb_db_query('UPDATE sb_conversations SET agent_id = ' . (is_null($agent_id) ? 'NULL' : $agent_id) . ' WHERE id = "' . $conversation_id . '"');
}

function sb_routing($conversation_id = -1, $department = -1) {
    $agents = sb_db_get('SELECT id FROM sb_users WHERE user_type = "agent" AND last_activity > "' . gmdate('Y-m-d H:i:s', time() - 15) . '"' . (sb_isset_num($department) ? ' AND department = ' . $department : '' ), false);
    $count_last = 0;
    $index = 0;
    $count = count($agents);
    if ($count == 0) {
        $agents = sb_db_get('SELECT id FROM sb_users WHERE user_type = "agent"' . (sb_isset_num($department) ? ' AND department = ' . $department : '' ), false);
        $count = count($agents);
    }
    if ($count) {
        for ($i = 0; $i < $count; $i++) {
            $count = intval(sb_db_get('SELECT COUNT(*) as `count` FROM sb_conversations WHERE (status_code = 0 OR status_code = 1 OR status_code = 2) AND agent_id = ' . $agents[$i]['id'])['count']);
            if ($count_last > $count) {
                $index = $i;
                break;
            }
            $count_last = $count;
        }
        return $conversation_id == -1 ? $agents[$index]['id'] : sb_routing_assign_conversation($agents[$index]['id'], $conversation_id);
    }
    return false;
}

/*
 * -----------------------------------------------------------
 * # SEND MESSAGE
 * -----------------------------------------------------------
 *
 * Add a message to a conversation
 *
 */

function sb_send_message($user_id = -1, $conversation_id, $message = '', $attachments = [], $conversation_status_code = -1, $payload = false, $queue = false) {
    if ($user_id == -1) {
        $active_user = sb_get_active_user();
        if ($active_user != false && isset($active_user['id'])) {
            $user_id = $active_user['id'];
        }
    }
    if ($user_id != -1) {
        $attachments_json = '';
        $security = sb_is_agent();
        $attachments = sb_json_array($attachments);
        if (count($attachments) > 0) {
            $attachments_json = '[';
            for ($i = 0; $i < count($attachments); $i++) {
            	$attachments_json .= '[\"' . sb_db_escape($attachments[$i][0]) . '\", \"' . sb_db_escape($attachments[$i][1]) . '\"],';
            }
            $attachments_json = substr($attachments_json, 0, -1) . ']';
        }
        if (!$security) {
            $check_id = sb_db_get('SELECT user_id FROM sb_conversations WHERE id = "' . $conversation_id . '" LIMIT 1');
            if (!is_string($check_id) && isset($check_id['user_id']) && $check_id['user_id'] == sb_get_active_user()['id']) {
                $security = true;
            }
        }
        if ($security) {
            sb_set_typing($user_id, -1);
            if ($payload !== false) $payload = sb_json_array($payload);
            $response = sb_db_query('INSERT INTO sb_messages(user_id, message, status_code, creation_time, attachments, conversation_id, payload) VALUES ("' . $user_id . '", "' . sb_db_escape(htmlspecialchars(sb_merge_fields($message), ENT_NOQUOTES)) . '", 0, "' . gmdate('Y-m-d H:i:s') . '", "' . $attachments_json . '", "' . $conversation_id . '", "' . ($payload != false ? sb_db_json_enconde($payload) : '') . '")', true);
            if ($queue && !sb_is_agent()) {
                $status = sb_db_get('SELECT status_code FROM sb_conversations WHERE id = "' . $conversation_id . '" LIMIT 1');
                if ($status['status_code'] == 3) {
                    sb_routing_assign_conversation(null, $conversation_id);
                    $queue = true;
                } else {
                    $queue = false;
                }
            }
            if ($conversation_status_code != -1) {
                if (in_array($conversation_status_code, [0, 1, 2, 3, 4])) {
                    sb_db_query('UPDATE sb_conversations SET status_code = ' . $conversation_status_code . ' WHERE id = "' . $conversation_id . '" LIMIT 1');
                } else {
                    return new SBValidationError('invalid-status-code');
                }
            }
            return sb_is_error($response) ? $response : ['message-id' => $response, 'queue' => $queue];
        }
        return new SBError('security-error', 'sb_send_message');
    } else {
        return new SBError('active-user-not-found', 'sb_send_message');
    }
}

/*
 * -----------------------------------------------------------
 * # UPDATE MESSAGE
 * -----------------------------------------------------------
 *
 * Update an existin message
 *
 */

function sb_update_message($message_id, $message = false, $attachments = false, $payload = false) {
    if ($message === false && $payload === false && $attachments === false) return new SBValidationError('missing-arguments');
    $security = sb_is_agent();
    if (!$security) {
        $check_id = sb_db_get('SELECT user_id FROM sb_conversations WHERE id = (SELECT conversation_id FROM sb_messages WHERE id = "' . $message_id . '" LIMIT 1) LIMIT 1');
        if (isset($check_id['user_id']) && $check_id['user_id'] == sb_get_active_user()['id']) {
            $security = true;
        }
    }
    if ($security) {
        if ($attachments !== false) $attachments = sb_json_array($attachments, false);
        if ($payload !== false) $payload = sb_json_array($payload, false);
        return sb_db_query('UPDATE sb_messages SET ' . ($message !== false ? 'message = "' . sb_db_escape($message) . '",' : '') . ' creation_time = "' . gmdate('Y-m-d H:i:s') . '"' . ($payload !== false ? ', payload = "' . sb_db_json_enconde($payload) . '"' : '') . ($attachments !== false ? ', attachments = "' . sb_db_json_enconde($attachments) . '"' : '') . ' WHERE id = "' . $message_id . '" LIMIT 1');
    }
    return new SBError('security-error', 'sb_update_message');
}

/*
 * -----------------------------------------------------------
 * # DELETE MESSAGE
 * -----------------------------------------------------------
 *
 * Delete a message
 *
 */

function sb_delete_message($message_id) {
    $security = sb_is_agent();
    if (!$security) {
        $check_id = sb_db_get('SELECT user_id FROM sb_conversations WHERE id = (SELECT conversation_id FROM sb_messages WHERE id = "' . $message_id . '" LIMIT 1) LIMIT 1');
        if (isset($check_id['user_id']) && $check_id['user_id'] == sb_get_active_user()['id']) {
            $security = true;
        }
    }
    if ($security) {
        return sb_db_query('DELETE FROM sb_messages WHERE id = "' . $message_id . '" LIMIT 1');
    }
    return new SBError('security-error', 'sb_delete_message');
}

/*
 * -----------------------------------------------------------
 * # CLOSE MESSAGE
 * -----------------------------------------------------------
 *
 * Send the default close message
 *
 */

function sb_close_message($bot_id, $conversation_id) {
    $message = sb_get_multi_setting('close-message', 'close-msg');
    if ($message != false) {
        return sb_send_message($bot_id, $conversation_id, $message, [], -1, ['type' => 'close-message']);
    }
    return false;
}

/*
 * -----------------------------------------------------------
 * MERGE FIELDS
 * -----------------------------------------------------------
 *
 * Convert the merge fields to the final values
 *
 */

function sb_merge_fields($message) {
    $replace = '';
    $marge_fields = ['user_name', 'user_email'];
    $marge_field = '';
    if (defined('SB_WOOCOMMERCE')) {
        $message = sb_woocommerce_merge_fields($message);
    }
    for ($i = 0; $i < count($marge_fields); $i++){
        if (strpos($message, '{' . $marge_fields[$i]) !== false) {
            $marge_field = '{' . $marge_fields[$i] . '}';
            switch ($marge_fields[$i]){
                case 'user_name':
                    $replace = sb_get_user_name();
                    break;
                case 'user_email':
                    $replace = sb_isset(sb_get_active_user(), 'email', '');
                    break;
            }
        }
    }
    $message = str_replace($marge_field, $replace, $message);
    return $message;
}

/*
 * -----------------------------------------------------------
 * # ONLINE STATUS
 * -----------------------------------------------------------
 *
 * 1. Update the user last activity date
 * 2. Check if a date is considered online
 * 3. Check if at least one agent or admin is online
 * 4. Return the online users
 *
 */

function sb_update_users_last_activity($user_id = -1, $return_user_id = -1, $check_slack = false) {
    $result = $user_id != -1 ? sb_db_query('UPDATE sb_users SET last_activity = "' . gmdate('Y-m-d H:i:s') . '" WHERE id = "' . $user_id . '"') : false;
    if ($return_user_id != -1) {
        $last_activity = sb_db_get('SELECT last_activity FROM sb_users WHERE id = "' . $return_user_id . '" LIMIT 1');
        if (!isset($last_activity['last_activity'])) {
            return 'offline';
        }
        if (sb_is_online($last_activity['last_activity'])) {
            return 'online';
        } else {
            return defined('SB_SLACK') && $check_slack ? sb_slack_agent_online($return_user_id) : 'offline';
        }
    }
    return $result;
}

function sb_is_online($datetime) {
    return strtotime($datetime) > strtotime(gmdate('Y-m-d H:i:s', time() - 15));
}

function sb_agents_online() {
    return intval(sb_db_get('SELECT COUNT(*) as count FROM sb_users WHERE (user_type = "agent" OR user_type = "admin") AND last_activity > "' . gmdate('Y-m-d H:i:s', time() - 15) . '"')['count']) > 0;
}

function sb_get_online_users($exclude_id = -1, $sorting = 'creation_time') {
    $users = sb_db_get(SELECT_FROM_USERS . ' FROM sb_users WHERE user_type <> "bot" AND last_activity > "' . gmdate('Y-m-d H:i:s', time() - 15) . '"' . ($exclude_id > 0 ? ' AND id <> "' . $exclude_id . '"' : '') . ' ORDER BY ' . $sorting . ' DESC', false);
    if (isset($users) && is_array($users)) {
        return $users;
    } else {
        return new SBError('db-error', 'sb_get_online_users', $users);
    }
}

/*
 * -----------------------------------------------------------
 * # TYPING STATUS
 * -----------------------------------------------------------
 *
 * Check if the user is typing on the chat
 * Check if an agent is typing in a conversation
 * Set the user typing status
 *
 */

function sb_is_typing($user_id, $conversation_id) {
    $typing = sb_db_get('SELECT COUNT(*) as typing FROM sb_users WHERE id = "' . $user_id . '" AND typing = "' . $conversation_id . '"');
    return $typing['typing'] != 0;
}

function sb_is_agent_typing($conversation_id) {
    return sb_db_get('SELECT id, first_name, last_name FROM sb_users WHERE typing = "' . $conversation_id . '" AND (user_type = "agent" OR user_type = "admin")');
}

function sb_set_typing($user_id, $conversation_id) {
    return sb_db_query('UPDATE sb_users SET typing = ' . $conversation_id . ' WHERE id = "' . $user_id . '"');
}

/*
 * -----------------------------------------------------------
 * # TRANSLATIONS
 * -----------------------------------------------------------
 *
 * Translations functions
 *
 */

function sb_($string) {
    global $SB_TRANSLATIONS;
    if (!isset($SB_TRANSLATIONS)) {
        sb_init_translations();
    }
    if ($SB_TRANSLATIONS != false && isset($SB_TRANSLATIONS[$string]) && $SB_TRANSLATIONS[$string] != '') {
        return $SB_TRANSLATIONS[$string];
    }
    return $string;
}

function sb_e($string) {
    echo sb_($string);
}

function sb_init_translations() {
    global $SB_TRANSLATIONS;
    global $SB_LANGUAGE;

    if (!empty($SB_LANGUAGE) && $SB_LANGUAGE[0] !='en') {
        $path = SB_PATH . '/resources/languages/' . $SB_LANGUAGE[1] . '/' . $SB_LANGUAGE[0] . '.json';
        if (file_exists($path)) {
            $SB_TRANSLATIONS = json_decode(file_get_contents($path), true);
        }  else {
            $SB_TRANSLATIONS = false;
        }
    } else if (!isset($SB_LANGUAGE)) {
        $language = sb_get_user_language();
        if ($language == '' || $language == 'en') {
            $SB_TRANSLATIONS = false;
        } else {
            switch($language) {
                case 'nn':
                case 'nb':
                    $language = 'no';
                    break;
            }
            $active_user = sb_get_active_user();
            $area = 'front';
            if ($active_user == false) {
                if ((isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], sb_dir_name() . '/admin'))) {
                    $area = 'admin';
                }
            } else if (sb_is_agent($active_user['user_type'])) {
                $area = 'admin';
            }
            if ($area == 'admin' && !sb_get_setting('admin-auto-translations')) {
                $SB_TRANSLATIONS = false;
            } else if ($area == 'front' && !isset($_GET['lang']) && !sb_get_setting('front-auto-translations')) {
                $SB_TRANSLATIONS = false;
            } else {
                $path = SB_PATH . '/resources/languages/' . $area . '/' . $language . '.json';
                if (file_exists($path)) {
                    $SB_TRANSLATIONS = json_decode(file_get_contents($path), true);
                    $SB_LANGUAGE = [$language, $area];
                }  else {
                    $SB_TRANSLATIONS = false;
                }
            }
        }
        if ($SB_TRANSLATIONS == false) {
            $SB_LANGUAGE = false;
        }
    } else {
        $SB_TRANSLATIONS = false;
    }
}

function sb_get_current_translations() {
    global $SB_TRANSLATIONS;
    if (!isset($SB_TRANSLATIONS)) {
        sb_init_translations();
    }
    return $SB_TRANSLATIONS;
}

function sb_get_translations($is_user = false) {
    $translations = [];
    if ($is_user && !file_exists(SB_PATH . '/uploads/languages')) {
        return [];
    }
    $path = $is_user ? '/uploads' : '/resources';
    $language_codes = json_decode(file_get_contents(SB_PATH . '/resources/languages/language-codes.json'), true);
    $front_files = scandir(SB_PATH . $path . '/languages/front');
    $admin_files = scandir(SB_PATH . $path . '/languages/admin');
    for ($i = 0; $i < count($front_files); $i++)  {
        $code = substr($front_files[$i], 0, -5);
        if (isset($language_codes[$code])) {
            $translations[$code] = ['name' => $language_codes[$code], 'front' => json_decode(file_get_contents(SB_PATH . $path . '/languages/front/' . $front_files[$i]), true)];
        }
    }
    for ($i = 0; $i < count($admin_files); $i++)  {
        $code = substr($admin_files[$i], 0, -5);
        if (isset($translations[$code])) {
            $translations[$code]['admin'] = json_decode(file_get_contents(SB_PATH . $path . '/languages/admin/' . $admin_files[$i]), true);
        }
    }
    return $translations;
}

function sb_save_translations($translations) {
    if (!file_exists(SB_PATH . '/uploads/languages')) {
        mkdir(SB_PATH . '/uploads/languages', 0777, true);
        mkdir(SB_PATH . '/uploads/languages/front', 0777, true);
        mkdir(SB_PATH . '/uploads/languages/admin', 0777, true);
    }
    foreach ($translations as $key => $translation) {
        foreach ($translation as $key_area => $translations_list) {
            $json = json_encode($translations_list);
            if ($json != false) {
                $paths = ['resources', 'uploads'];
                for ($i = 0; $i < 2; $i++)  {
                    sb_file(SB_PATH . '/' . $paths[$i] . '/languages/' . $key_area . '/' . $key . '.json', $json);
                }
            }
        }
    }
    return true;
}

function sb_restore_user_translations() {
    $translations_all = sb_get_translations();
    $translations_user = sb_get_translations(true);
    foreach ($translations_user as $key => $translations) {
        $paths = ['front', 'admin'];
        for ($i = 0; $i < 2; $i++)  {
            if (isset($translations_all[$key]) && isset($translations_all[$key][$paths[$i]])) {
                foreach ($translations_all[$key][$paths[$i]] as $key_two => $translation) {
                    if (!isset($translations[$paths[$i]][$key_two])) {
                        $translations[$paths[$i]][$key_two] = $translations_all[$key][$paths[$i]][$key_two];
                    }
                }
            }
            sb_file(SB_PATH . '/resources/languages/' . $paths[$i] . '/'. $key . '.json', json_encode($translations[$paths[$i]]));
        }
    }
}

function sb_get_user_language() {
    global $SB_LANGUAGE;
    if (empty($SB_LANGUAGE)) {
        return isset($_GET['lang']) ? $_GET['lang'] : strtolower(substr(sb_isset($_SERVER, 'HTTP_ACCEPT_LANGUAGE', '  '), 0, 2));
    }
    return $SB_LANGUAGE[0] != 'en' ? $SB_LANGUAGE[0] : '';
}

/*
 * -----------------------------------------------------------
 * # SETTINGS
 * -----------------------------------------------------------
 *
 * Populate the admin area with the settings of the file /resources/json/settings.json
 * Save the settings of the plugin settings area
 * Return the settings array
 *
 */

function sb_populate_settings($category, $settings, $echo = true) {
    if (!isset($settings) && file_exists(SB_PATH . '/resources/json/settings.json')) {
        $settings = json_decode(file_get_contents(SB_PATH . '/resources/json/settings.json'), true);
    }
    $settings = $settings[$category];
    $code = '';
    for ($i = 0; $i < count($settings); $i++) {
        $code .= sb_get_setting_code($settings[$i]);
    }
    if ($echo) {
        echo $code;
        return true;
    } else {
        return $code;
    }
}

function sb_populate_app_settings($app_name) {
    $file = SB_PATH . '/apps/' . $app_name . '/settings.json';
    $settings = [$app_name => []];
    if (file_exists($file)) {
        $settings[$app_name] = json_decode(file_get_contents($file), true);
    }
    return sb_populate_settings($app_name, $settings, false);
}

function sb_get_setting_code($array) {
    if (isset($array)) {
        $id = $array['id'];
        $type = $array['type'];
        $content = '<div id="' . $id . '" data-type="' . $type . '"' . (isset($array['setting']) ? ' data-setting="' . $array['setting'] . '"' : '') .' class="sb-setting sb-type-' . $type . '"><div class="content"><h2>' . sb_($array["title"]) . '</h2><p>' . sb_($array["content"]) . (isset($array["help"]) ? '<a href="' . $array["help"] . '" target="_blank" class="sb-icon-help"></a>' : '') . '</p></div><div class="input">';
        switch ($type) {
            case 'color':
                $content .= '<input type="text"><i class="sb-close sb-icon-close"></i>';
                break;
            case 'text':
                $content .= '<input type="text">';
                break;
            case 'password':
                $content .= '<input type="password">';
                break;
            case 'textarea':
                $content .= '<textarea></textarea>';
                break;
            case 'select':
                $content .= '<select>';
                for ($i = 0; $i < count($array['value']); $i++) {
                    $content .= '<option value="' . $array['value'][$i][0] . '">' . $array['value'][$i][1] . '</option>';
                }
                $content .= '</select>';
                break;
            case 'checkbox':
                $content .= '<input type="checkbox">';
                break;
            case 'radio':
                for ($i = 0; $i < count($array['value']); $i++) {
                    $content .= '<div><input type="radio" name="' . $id . '" value="' . strtolower(str_replace(' ', '-', $array['value'][$i])) . '"><label>' . $array["value"][$i] . '</label></div>';
                }
                break;
            case 'number':
                $content .= '<input type="number">' . (key_exists('unit', $array) ? '<label>' . $array['unit'] . '</label>' : '');
                break;
            case 'upload':
                $content .= '<input type="url"><button type="button">' . sb_('Choose file') . '</button>';
                break;
            case 'upload-image':
                $content .= '<div class="image"' . (isset($array['background-size']) ? ' style="background-size: ' . $array['background-size'] . '"' : '')  . '><i class="sb-icon-close"></i></div>';
                break;
            case 'input-button':
                $content .= '<input type="text"><a class="sb-btn">' . sb_($array['button-text']) . '</a>';
                break;
            case 'button':
                $content .= '<a class="sb-btn" target="_blank" href="' . sb_($array['button-url']) . '">' . sb_($array['button-text']) . '</a>';
                break;
            case 'multi-input':
                for ($i = 0; $i < count($array['value']); $i++) {
                    $type = $array['value'][$i]['type'];
                    $content .= '<div id="' . $array['value'][$i]['id'] . '" data-type="' . $type . '" class="multi-input-' . $array['value'][$i]['type'] . '"><label>' . $array['value'][$i]['title'] . '</label>';
                    switch ($type) {
                        case 'text':
                            $content .= '<input type="text">';
                            break;
                        case 'password':
                            $content .= '<input type="password">';
                            break;
                        case 'number':
                            $content .= '<input type="number">';
                            break;
                        case 'textarea':
                            $content .= '<textarea></textarea>';
                            break;
                        case 'upload':
                            $content .= '<input type="url"><button type="button">' . sb_('Choose file') . '</button>';
                            break;
                        case 'upload-image':
                            $content .= '<div class="image"><i class="sb-icon-close"></i></div>';
                            break;
                        case 'checkbox':
                            $content .= '<input type="checkbox">';
                            break;
                        case 'select':
                            $content .= '<select>';
                            $items = $array['value'][$i]['value'];
                            for ($j = 0; $j < count($items); $j++) {
                                $content .= '<option value="' . $items[$j][0] . '">' . $items[$j][1] . '</option>';
                            }
                            $content .= '</select>';
                            break;
                        case 'button':
                            $content .= '<a class="sb-btn" target="_blank" href="' . sb_($array['value'][$i]['button-url']) . '">' . sb_($array['value'][$i]['button-text']) . '</a>';
                    }
                    $content .= '</div>';
                }
                break;
            case 'range':
                $range = (key_exists('range', $array) ? $array['range'] : array(0, 100));
                $unit = (key_exists('unit', $array) ? '<label>' . $array['unit'] . '</label>' : '');
                $content .= '<label class="range-value">' . $range[0] . '</label><input type="range" min="' . $range[0] .'" max="' . $range[1] .'" value="' . $range[0] . '" />' . $unit;
                break;
            case 'select-image':
                $html = '<div class="thumbs" data-columns="' . (key_exists('columns', $array) ? $array['columns'] : '3') . '">';
                for ($i = 0; $i < count($array['value']); $i++) {
                    $name = $array['value'][$i][0];
                    $html .= '<div data-id="' . strtolower(str_replace(' ', '-', $name)) . '"><span><img src="' . $array['value'][$i][1] . '"></span><p>' . $name . '</p></div>';
                }
                $content .= $html . '</div>';
                break;
            case 'repeater':
                $content .= '<div class="sb-repeater"><div class="repeater-item">';
                for ($i = 0; $i < count($array['items']); $i++) {
                    $item = $array['items'][$i];
                    $content .= '<div>' . (isset($item['name']) ? '<label>' . sb_($item['name']) . '</label>' : '');
                    switch ($item['type']) {
                        case 'text':
                            $content .= '<input data-id="' . $item['id'] . '" type="text">';
                            break;
                        case 'textarea':
                            $content .= '<textarea data-id="' . $item['id'] . '"></textarea>';
                            break;
                        case 'auto-id':
                            $content .= '<input data-type="auto-id" data-id="' . $item['id'] . '" value="1" type="text" readonly="true">';
                            break;
                        case 'hidden':
                            $content .= '<input data-id="' . $item['id'] . '" type="hidden">';
                            break;
                        case 'color-palette':
                            $content .= sb_color_palette($item['id']);
                            break;
                        case 'upload-image':
                            $content .= '<div data-type="upload-image"><div data-id="' . $item['id'] . '" class="image"><i class="sb-icon-close"></i></div></div>';
                            break;
                    }
                    $content .= '</div>';
                }
                $content .= '<i class="sb-icon-close"></i></div></div><a class="sb-btn sb-repeater-add">' . sb_('Add new item') . '</a>';
                break;
            case 'timetable':
                $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                $hours = [['', ''], ['00:00', '12:00 am'], ['00:30', '12:30 am'], ['01:00', '1:00 am'], ['01:30', '1:30 am'], ['02:00', '2:00 am'], ['02:30', '2:30 am'], ['03:00', '3:00 am'], ['03:30', '3:30 am'], ['04:00', '4:00 am'], ['04:30', '4:30 am'], ['05:00', '5:00 am'], ['05:30', '5:30 am'], ['06:00', '6:00 am'], ['06:30', '6:30 am'], ['07:00', '7:00 am'], ['07:30', '7:30 am'], ['08:00', '8:00 am'], ['08:30', '8:30 am'], ['09:00', '9:00 am'], ['09:30', '9:30 am'], ['10:00', '10:00 am'], ['10:30', '10:30 am'], ['11:00', '11:00 am'], ['11:30', '11:30 am'], ['12:00', '12:00 pm'], ['12:30', '12:30 pm'], ['13:00', '1:00 pm'], ['13:30', '1:30 pm'], ['14:00', '2:00 pm'], ['14:30', '2:30 pm'], ['15:00', '3:00 pm'], ['15:30', '3:30 pm'], ['16:00', '4:00 pm'], ['16:30', '4:30 pm'], ['17:00', '5:00 pm'], ['17:30', '5:30 pm'], ['18:00', '6:00 pm'], ['18:30', '6:30 pm'], ['19:00', '7:00 pm'], ['19:30', '7:30 pm'], ['20:00', '8:00 pm'], ['20:30', '8:30 pm'], ['21:00', '9:00 pm'], ['21:30', '9:30 pm'], ['22:00', '10:00 pm'], ['22:30', '10:30 pm'], ['23:00', '11:00 pm'], ['23:30', '11:30 pm'], ['closed', sb_('Closed')]];
                $select = '<div class="sb-custom-select">';
                for ($i = 0; $i < count($hours); $i++) {
                    $select .= '<span data-value="' . $hours[$i][0] . '">' . $hours[$i][1] . '</span>';
                }
                $content .= '<div class="sb-timetable">';
                for ($i = 0; $i < 7; $i++) {
                    $content .= '<div data-day="' . strtolower($days[$i]) . '"><label>' . sb_($days[$i]) . '</label><div><div></div><span>' . sb_('To') . '</span><div></div><span>' . sb_('And') . '</span><div></div><span>' . sb_('To') . '</span><div></div></div></div>';
                }
                $content .= $select . '</div></div>';
                break;
        }
        return $content . '</div></div>';
    }
    return '';
}

function sb_save_settings($settings, $external_settings = []) {
    if (isset($settings)) {
        global $SB_SETTINGS;
        $settings_encoded = sb_db_json_enconde($settings);
        if (isset($settings_encoded) && is_string($settings_encoded)) {

            // Save main settings
            $query = 'INSERT INTO sb_settings(name, value) VALUES (\'settings\', \'' . $settings_encoded . '\') ON DUPLICATE KEY UPDATE value = \'' . $settings_encoded . '\'';
            $result = sb_db_query($query);
            if (sb_is_error($result)) {
                return $result;
            }

            // Save external settings
            foreach ($external_settings as $key => $value) {
                sb_save_external_setting($key, $value);
            }

            // Update bot
            sb_update_bot($settings['bot-name'][0], $settings['bot-image'][0]);

            $SB_SETTINGS = $settings;
            return true;
        } else {
            return new SBError('json-encode-error', 'sb_save_settings');
        }
    } else {
        return new SBError('settings-not-found', 'sb_save_settings');
    }
}

function sb_save_external_setting($name, $value) {
    $settings_encoded = sb_db_json_enconde($value);
    return sb_db_query('INSERT INTO sb_settings(name, value) VALUES (\'' . $name . '\', \'' . $settings_encoded . '\') ON DUPLICATE KEY UPDATE value = \'' . $settings_encoded . '\'');
}

function sb_get_settings() {
    global $SB_SETTINGS;
    if (isset($SB_SETTINGS)) {
        return $SB_SETTINGS;
    } else {
        $value = sb_db_get('SELECT value FROM sb_settings WHERE name="settings" LIMIT 1');
        if ($value != '' && !sb_is_error($value) && is_string($value['value'])) {
            $value = json_decode($value['value'], true);
            $SB_SETTINGS = $value;
            return $value;
        }
        return [];
    }
}

function sb_get_all_settings() {
    return array_merge(sb_get_settings(), sb_get_external_settings());
}

function sb_get_setting($id, $default = false) {
    $settings = sb_get_settings();
    if (!sb_is_error($settings)) {
        if (isset($settings[$id])) {
            $setting = $settings[$id][0];
            if (is_array($setting) && !isset($setting[0])) {
                $settings_result = [];
                foreach ($setting as $key => $value) {
                    $settings_result[$key] = $value[0];
                }
                return $settings_result;
            } else {
                return $setting;
            }
        } else {
            return $default;
        }
    } else {
        return $settings;
    }
}

function sb_get_multi_setting($id, $sub_id, $default = false) {
    $setting = sb_get_setting($id);
    if ($setting != false && (!isset($setting[$id . '-active']) || $setting[$id . '-active'] == true) && !empty($setting[$sub_id])) {
        return $setting[$sub_id];
    }
    return $default;
}
function sb_get_external_settings($name = '') {
    if ($name == '') {
        $name = 'name="emails" || name="rich-messages" || name="wc-emails"';
    } else {
        $name = 'name="' . $name . '"';
    }
    $result = sb_db_get('SELECT value FROM sb_settings WHERE ' . $name, false);
    $settings = [];
    if (!is_array($result)) {
        return $result;
    }
    if (count($result) == 1) {
        return json_decode($result[0]['value'], true);
    }
    for ($i = 0; $i < count($result); $i++) {
        $settings = array_merge($settings, json_decode($result[$i]['value'], true));
    }
    return $settings;
}

function sb_get_front_settings() {
    global $SB_LANGUAGE;
    $return = [
        'registration-required' => sb_get_setting('registration-required'),
        'registration-timetable' => sb_get_setting('registration-timetable'),
        'registration-offline' => sb_get_setting('registration-offline'),
        'registration-link' => sb_get_setting('registration-link', ''),
        'visitors-registration' => sb_get_setting('visitors-registration'),
        'privacy' => sb_get_multi_setting('privacy', 'privacy-active'),
        'popup' => sb_get_multi_setting('popup-message', 'popup-active'),
        'popup-mobile-hidden' => sb_get_multi_setting('popup-message', 'popup-mobile-hidden'),
        'welcome' => sb_get_multi_setting('welcome-message', 'welcome-active'),
        'welcome-trigger' => sb_get_multi_setting('welcome-message', 'welcome-trigger', 'load'),
        'welcome-delay' => sb_get_multi_setting('welcome-message', 'welcome-delay', 2000),
        'subscribe' => sb_get_multi_setting('subscribe-message', 'subscribe-active'),
        'subscribe-delay' => sb_get_multi_setting('subscribe-message', 'subscribe-delay', 2000),
        'follow' => sb_get_block_setting('follow'),
        'chat-manual-init' => sb_get_setting('chat-manual-init'),
        'chat-login-init' => sb_get_setting('chat-login-init'),
        'chat-sound' => sb_get_setting('chat-sound', 'n'),
        'header-name' => sb_get_setting('header-name', ''),
        'desktop-notifications' => sb_get_setting('desktop-notifications'),
        'flash-notifications' => sb_get_setting('flash-notifications'),
        'push-notifications' => sb_get_multi_setting('push-notifications', 'push-notifications-active'),
        'notifications-icon' => sb_get_setting('notifications-icon', SB_URL . '/media/icon.png'),
        'bot-id' => sb_get_bot_id(),
        'bot-name' => sb_get_setting('bot-name', ''),
        'bot-image' => sb_get_setting('bot-image', ''),
        'bot-delay' => sb_get_setting('dialogflow-bot-delay', 2000),
        'bot-office-hours' => sb_get_setting('dialogflow-timetable'),
        'bot-unknow-notify' => sb_get_setting('bot-unknow-notify'),
        'dialogflow-active' => defined('SB_DIALOGFLOW') && sb_get_setting('dialogflow-active'),
        'dialogflow-human-takeover' => false,
        'dialogflow-welcome' => false,
        'slack-active' => defined('SB_SLACK') && sb_get_setting('slack-active'),
        'rich-messages' => sb_get_rich_messages_ids(),
        'display-users-thumb' => sb_get_setting('display-users-thumb'),
        'hide-agents-thumb' => sb_get_setting('hide-agents-thumb'),
        'notify-user-email' => sb_get_setting('notify-user-email'),
        'notify-agent-email' => sb_get_setting('notify-agent-email'),
        'translations' => sb_get_current_translations(),
        'auto-open' => sb_get_setting('auto-open'),
        'office-hours' => sb_office_hours(),
        'disable-office-hours' => sb_get_setting('chat-timetable-disable'),
        'disable-offline' => sb_get_setting('chat-offline-disable'),
        'timetable' => sb_get_multi_setting('chat-timetable', 'chat-timetable-active'),
        'timetable-hide' => sb_get_multi_setting('chat-timetable', 'chat-timetable-hide'),
        'articles' => sb_get_setting('articles-active'),
        'articles-title' => sb_get_setting('articles-title', ''),
        'init-dashboard' => sb_get_setting('init-dashboard') && !sb_get_setting('disable-dashboard'),
        'wp' => defined('SB_WP'),
        'wp-users-system' => sb_get_setting('wp-users-system', 'sb'),
        'wp-logout' => sb_get_setting('wp-logout'),
        'queue' => sb_get_multi_setting('queue', 'queue-active'),
        'queue-message' => sb_get_multi_setting('queue', 'queue-message', ''),
        'queue-message-success' => sb_get_multi_setting('queue', 'queue-message-success', ''),
        'queue-response-time' => sb_get_multi_setting('queue', 'queue-response-time', 5),
        'routing' => !sb_get_multi_setting('queue', 'queue-active') && sb_get_setting('routing'),
        'webhooks' => sb_get_multi_setting('webhooks', 'webhooks-active'),
        'agents-online' => sb_agents_online(),
        'cron' => date('H', time()) != sb_isset(sb_db_get('SELECT value FROM sb_settings WHERE name="cron"'), 'value')
    ];
    if ($return['timetable-hide']) {
        $return['timetable-message'] = '*' . sb_get_multi_setting('chat-timetable', 'chat-timetable-title') . '*\n' . sb_get_multi_setting('chat-timetable', 'chat-timetable-msg');
    }
    if (defined('SB_TICKETS')) {
        $return['tickets-registration-required'] = sb_get_setting('tickets-registration-required');
        $return['tickets-registration-redirect'] = sb_get_setting('tickets-registration-redirect', '');
        $return['tickets-default-form'] = sb_get_setting('tickets-default-form', 'login');
        $return['tickets-conversations-title-user'] = sb_get_setting('tickets-conversations-title-user');
        $return['tickets-welcome-active'] = sb_get_multi_setting('tickets-welcome-message', 'tickets-welcome-message-active');
        $return['tickets-welcome-message'] = sb_merge_fields(sb_(sb_get_multi_setting('tickets-welcome-message', 'tickets-welcome-message-msg')));
    }
    if (defined('SB_WOOCOMMERCE')) {
        $return['woocommerce-returning-visitor'] = !in_array(sb_isset(sb_get_active_user(), 'user_type'), ['user', 'agent', 'admin']) && sb_get_multi_setting('wc-returning-visitor', 'wc-returning-visitor-active');
    }
    if ($return['dialogflow-active']) {
        $human_takeover = sb_get_setting('dialogflow-human-takeover');
        if ($human_takeover != false && $human_takeover['dialogflow-human-takeover-active']) {
            $return['dialogflow-human-takeover'] = ['message' => sb_($human_takeover['dialogflow-human-takeover-message']), 'success' => sb_($human_takeover['dialogflow-human-takeover-message-confirmation']), 'email' => $human_takeover['dialogflow-human-takeover-email'], 'email-message' => sb_(sb_isset($human_takeover, 'dialogflow-human-takeover-email-message')), 'confirm' => sb_(sb_isset($human_takeover, 'dialogflow-human-takeover-confirm', 'Yes')), 'cancel' => sb_(sb_isset($human_takeover, 'dialogflow-human-takeover-cancel', 'No'))];
        }
        $return['dialogflow-welcome'] = sb_get_setting('dialogflow-welcome');
        $return['dialogflow-disable'] = sb_get_setting('dialogflow-disable');
    }
    return $return;
}

function sb_get_block_setting($value) {
    switch ($value)  {
    	case 'privacy':
            $settings = sb_get_setting('privacy');
            return ['title' => sb_rich_value($settings['privacy-title']), 'message' => sb_rich_value($settings['privacy-msg']), 'decline' => sb_rich_value($settings['privacy-msg-decline']), 'link' => $settings['privacy-link'], 'link-name' => sb_rich_value(sb_isset($settings, 'privacy-link-text', ''), false), 'btn-approve' => sb_rich_value($settings['privacy-btn-approve'], false), 'btn-decline' => sb_rich_value($settings['privacy-btn-decline'], false)];
        case 'popup':
            $settings = sb_get_setting('popup-message');
            return ['title' => sb_rich_value($settings['popup-title']), 'message' => sb_rich_value($settings['popup-msg']), 'image' => $settings['popup-image']];
        case 'welcome':
            $settings = sb_get_setting('welcome-message');
            return ['message' => sb_rich_value($settings['welcome-msg']), 'open' => $settings['welcome-open'], 'sound' => $settings['welcome-sound']];
        case 'follow':
            $settings = sb_get_setting('follow-message');
            return $settings['follow-active'] ? ['title' => sb_rich_value($settings['follow-title']), 'message' => sb_rich_value($settings['follow-msg']), 'name' => sb_rich_value($settings['follow-name']), 'last-name' => sb_rich_value(sb_isset($settings, 'follow-last-name')), 'success' => sb_rich_value($settings['follow-success']), 'placeholder' => sb_rich_value($settings['follow-placeholder'], false)] : false;
        case 'subscribe':
            $settings = sb_get_setting('subscribe-message');
            $settings_follow = sb_get_setting('follow-message');
            $message = '[email id="sb-subscribe-form" title="' . sb_rich_value($settings['subscribe-title']) . '" message="' . sb_rich_value($settings['subscribe-msg']) . '" success="' . sb_rich_value($settings['subscribe-msg-success']) . '" placeholder="' . sb_rich_value($settings_follow['follow-placeholder'], false) . '" name="' . ($settings_follow['follow-name'] ? 'true' : 'false') . '" last-name="' . ($settings_follow['follow-last-name'] ? 'true' : 'false') . '"]';
            return ['message' => $message, 'sound' => $settings['subscribe-sound']];
    }
    return false;
}

function sb_isset($array, $key, $default = false) {
    if (sb_is_error($array) || sb_is_validation_error($array)) return $array;
    return !empty($array) && isset($array[$key]) && $array[$key] !== '' ? $array[$key] : $default;
}

function sb_isset_num($value) {
    return $value != -1 && $value != '' && !is_null($value) && !is_bool($value);
}

function sb_color_palette($id = '') {
    return '<div data-type="color-palette" data-value="" data-id="' . $id . '" class="sb-color-palette"><span></span><ul><li data-value=""></li><li data-value="red"></li><li data-value="yellow"></li><li data-value="green"></li><li data-value="pink"></li><li data-value="gray"></li><li data-value="blue"></li></ul></div>';
}

function sb_get_departments() {
    $items = sb_get_setting('departments');
    $count = is_array($items) ? count($items) : 0;
    $departments = [];
    if ($count) {
        for ($i = 0; $i < $count; $i++) {
            $departments[$items[$i]['department-id']] = ['name' => $items[$i]['department-name'], 'color' => $items[$i]['department-color'], 'image' => (empty($items[$i]['department-image']) ? '' : $items[$i]['department-image'])];
        }
    }
    return $departments;
}

/*
 * -----------------------------------------------------------
 * # OFFICE HOURS
 * -----------------------------------------------------------
 *
 * Check if the current time is within the office hours
 *
 */

function sb_office_hours() {
    $settings = sb_get_settings();
    $timetable = sb_isset($settings, 'timetable', [[]])[0];
    $today = strtolower(gmdate('l'));
    if (isset($timetable[$today]) && $timetable[$today][0][0] != '') {
        $now_hours = intval(gmdate('H'));
        $now_minutes = intval(gmdate('i'));
        $status = false;
        $offset = sb_get_setting('timetable-utc');
        if ($offset === false) {
            $offset = 0;
        } else {
            $offset = intval($offset);
        }
        for ($i = 0; $i < 3; $i+=2){
            if ($timetable[$today][$i][0] != '' && $timetable[$today][$i][0] != 'closed' && $timetable[$today][$i + 1][0] != '') {
                $hours_start = intval(explode(':', $timetable[$today][$i][0])[0]) + $offset;
                $minutes_start = intval(explode(':',$timetable[$today][$i][0])[1]);
                $hours_end = intval(explode(':', $timetable[$today][$i + 1][0])[0]) + $offset;
                $minutes_end = intval(explode(':', $timetable[$today][$i + 1][0])[1]);
                if (($now_hours > $hours_start || ($now_hours == $hours_start && $now_minutes >= $minutes_start)) && ($now_hours < $hours_end || ($now_hours == $hours_end && $now_minutes <= $minutes_end))) {
                    $status = true;
                    break;
                }
            }
        }
        return $status;
    }
    return true;
}

/*
 * -----------------------------------------------------------
 * # ENCRYIPTION
 * -----------------------------------------------------------
 *
 * Encrypt a string or decrypt an encrypted string
 *
 */

function sb_encryption($action, $string) {
    $output = false;
    $encrypt_method = "AES-256-CBC";
    $secret_key = empty(sb_get_setting('envato-purchase-code')) ? 'supportboard' : sb_get_setting('envato-purchase-code');
    $secret_iv = 'supportboard_iv';
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    if ($action == 'encrypt') {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
        if (substr($output, -1) == '=') $output = substr($output, 0, -1);
    } else if ($action == 'decrypt'){
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        if ($output === false && $secret_key != 'supportboard') {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, hash('sha256', 'supportboard'), 0, $iv);
        }
    }
    return $output;
}

/*
 * -----------------------------------------------------------
 * # STRING SLUG
 * -----------------------------------------------------------
 *
 * Convert a string to a slug or a slug to a string
 *
 */

function sb_string_slug($string, $action = 'slug') {
    $string = trim($string);
    if ($action == 'slug') {
        return strtolower(str_replace(' ', '-', $string));
    } else if ($action == 'string') {
        return ucfirst(strtolower(str_replace('-', ' ', $string)));
    }
    return $string;
}

/*
 * -----------------------------------------------------------
 * # CURL
 * -----------------------------------------------------------
 *
 * Send a curl request
 *
 */

function sb_curl($url, $post_fields = '', $header = [], $type = 'POST') {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'SB');
    switch ($type){
        case 'PATCH':
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($post_fields) ? $post_fields : http_build_query($post_fields));
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            if ($type == 'PATCH') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            }
            break;
        case 'GET':
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($ch, CURLOPT_HEADER, false);
            if ($header != '' || (is_array($header) && count($header) > 0)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            }
            break;
        case 'DOWNLOAD':
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            break;

    }
    $response = curl_exec($ch);
    if (curl_errno($ch) > 0) {
        curl_close($ch);
        return curl_error($ch);
    }
    curl_close($ch);
    return $type == 'POST' || $type == 'PATCH' ? json_decode($response, true) : $response;
}

function sb_download($url) {
    return sb_curl($url, '', '', 'DOWNLOAD');
}

function sb_get($url) {
    return sb_curl($url, '', '', 'GET');
}

/*
 * -----------------------------------------------------------
 * # RICH MESSAGES FUNCTIONS
 * -----------------------------------------------------------
 *
 * 1. Get the custom rich messages ids including the built in ones
 * 2. Get the rich message with the given name
 * 3. Escape a rich message shortcode value
 * 4. Return the full shortcode and its parameters
 *
 */

function sb_get_rich_messages_ids() {
    $result = sb_get_external_settings('rich-messages');
    $ids = ['email' , 'registration' , 'login', 'timetable', 'articles'];
    if (is_array($result) && isset($result['rich-messages']) && is_array($result['rich-messages'][0])) {
        for ($i = 0; $i < count($result['rich-messages'][0]); $i++) {
            array_push($ids, $result['rich-messages'][0][$i]['rich-message-name']);
        }
        return $ids;
    }
    if (defined('SB_WOOCOMMERCE')) {
        $ids = array_merge($ids, ['woocommerce-cart']);
    }
    return $ids;
}

function sb_get_rich_message($name) {
    if (in_array($name, ['registration', 'registration-tickets', 'login', 'login-tickets', 'timetable', 'articles', 'woocommerce-cart'])) {
        $title = '';
        $message = '';
        $success = '';
        switch ($name) {
            case 'registration-tickets':
            case 'registration':
                $active_user = sb_get_active_user();
                $last_name = sb_get_setting('registration-last-name');
                $user = $active_user != false && !sb_is_agent($active_user['user_type']) ? sb_get_user($active_user['id'], true) : ['profile_image' => '', 'first_name' => '', 'last_name' => '', 'email' => '', 'password' => '', 'user_type' => 'visitor', 'details' => []];
                $visitor = $user['user_type'] == 'visitor' || $user['user_type'] == 'lead';
                $settings = sb_get_setting('registration');
                $registration_fields = sb_get_setting('registration-fields');
                $title = sb_(sb_isset($settings, 'registration-title', 'Create new account'));
                $message = sb_(sb_isset($settings, 'registration-msg', 'Please create a new account before using our chat.'));
                $success = sb_(sb_isset($settings, 'registration-success', 'Login successful.'));
                $profile_image = sb_get_setting('registration-profile-img') ? '<div id="profile_image" data-type="image" class="sb-input sb-input-image sb-profile-image"><span>' . sb_('Profile image') . '</span><div' . ($user['profile_image'] != '' && strpos($user['profile_image'], 'media/user.svg') == false ? ' data-value="' . $user['profile_image'] . '" style="background-image:url(' . $user['profile_image'] . ')"' : '') . ' class="image">' . ($user['profile_image'] != '' && strpos($user['profile_image'], 'media/user.svg') == false ? '<i class="sb-icon-close"></i>' : '') . '</div></div>' : '';
                $password = sb_get_setting('registration-password') || sb_get_setting('wp-users-system') == 'wp' || $name == 'registration-tickets' ? '<div id="password" data-type="text" class="sb-input sb-input-password"><span>' . sb_('Password') . '</span><input value="' . ($user['password'] != '' ? '********' : '') . '" autocomplete="false" type="password" required></div><div id="password-check" data-type="text" class="sb-input sb-input-password"><span>' . sb_('Repeat password') . '</span><input value="' . ($user['password'] != '' ? '********' : '') . '" autocomplete="false" type="password" required></div>' : '';
                $link = $settings['registration-terms-link'] != '' || $settings['registration-privacy-link'] != '' ? '<div class="sb-link-area">' . sb_('By clicking the button below, you agree to our') . ' <a target="_blank" href="' . sb_isset($settings, 'registration-terms-link', $settings['registration-privacy-link']) . '">' . sb_($settings['registration-terms-link'] != '' ? 'Terms of service' : 'Privacy Policy') . '</a>' . ($settings['registration-privacy-link'] != '' && $settings['registration-terms-link'] != '' ? ' ' . sb_('and') . ' <a target="_blank" href="' . $settings['registration-privacy-link'] . '">' . sb_('Privacy Policy') . '</a>' : '') . '.</div>' : '';
                $code = '<div class="sb-form-main sb-form">' . $profile_image . '<div id="first_name" data-type="text" class="sb-input sb-input-text"><span>' . sb_($last_name ? 'First name' : 'Name') . '</span><input value="' . ($visitor ? '' : $user['first_name']) . '" autocomplete="false" type="text" required></div>' . ($last_name ? '<div id="last_name" data-type="text" class="sb-input sb-input-text"><span>' . sb_('Last name') . '</span><input value="' . ($visitor ? '' : $user['last_name'])  . '" autocomplete="false" type="text" required></div>' : '') . '<div id="email" data-type="text" class="sb-input sb-input-text"><span>' . sb_('Email') . '</span><input value="' . $user['email'] . '" autocomplete="off" type="email" required></div>' . $password . '</div><div class="sb-form-extra sb-form">';

                $extra = [];
                if (isset($user['details'])) {
                    for ($i = 0; $i < count($user['details']); $i++) {
                        $extra[$user['details'][$i]['slug']] = $user['details'][$i]['value'];
                    }
                }

                foreach ($registration_fields as $key => $value) {
                    if ($value) {
                        $name = str_replace(['reg-', '-'], ['', ' '], $key);
                        $filled = (isset($extra[$name]) ? ' value="' . $extra[$name] . '"': '');
                        $code .= '<div id="' . str_replace('reg-', '', $key) . '" data-type="text" class="sb-input sb-input-text"><span>' . sb_(ucfirst($name)) . '</span><input' . $filled . ' autocomplete="false" type="text"></div>';
                    }
                }

                if (sb_get_setting('registration-extra')) {
                    $additional_fields = sb_get_setting('user-additional-fields');
                    if ($additional_fields != false) {
                        for ($i = 0; $i < count($additional_fields); $i++) {
                            $value = $additional_fields[$i];
                            $name = $value['extra-field-name'];
                            $filled = isset($extra[$value['extra-field-slug']]) ? ' value="' . $extra[$value['extra-field-slug']] . '"' : '';
                            if ($name != '') {
                                $code .= '<div id="' . $value['extra-field-slug'] . '" data-type="text" class="sb-input sb-input-text"><span>' . sb_(ucfirst($name)) . '</span><input' . $filled . ' autocomplete="false" type="text"></div>';
                            }
                        }
                    }
                }

                $code .= '</div>' . $link . '<div class="sb-buttons"><div class="sb-btn sb-submit">' . sb_($visitor ? sb_isset($settings, 'registration-btn-text', 'Create account') : 'Update account') . '</div>' . ($password  != '' ? '<div class="sb-btn-text sb-login-area">' . sb_('Sign in instead') . '</div>': '') . '</div>';
                break;
            case 'login-tickets':
            case 'login':
                $settings = sb_get_setting('login');
                $title = sb_(sb_isset($settings, 'login-title', 'Login'));
                $message = sb_($settings['login-msg']);
                $code = '<div class="sb-form"><div id="email" class="sb-input"><span>' . sb_('Email') . '</span><input autocomplete="false" type="email"></div><div id="password" class="sb-input"><span>' . sb_('Password') . '</span><input autocomplete="false" type="password"></div></div><div class="sb-buttons"><div class="sb-btn sb-submit-login">' . sb_('Sign in') . '</div>' . (sb_get_setting('registration-required') == 'login' ? '' : '<div class="sb-btn-text sb-registration-area">' . sb_('Create new account') . '</div>') . '</div>';
                break;
            case 'timetable':
                $settings = sb_get_settings();
                $timetable = sb_isset($settings, 'timetable', [false])[0];
                $title = $settings['chat-timetable'][0]['chat-timetable-title'][0];
                $message = $settings['chat-timetable'][0]['chat-timetable-msg'][0];
                $title = sb_($title == '' ? 'Office hours' : $title);
                $message = sb_($message);
                $code = '<div class="sb-timetable" data-offset="' . sb_get_setting('timetable-utc') . '">';
                if ($timetable != false) {
                    foreach ($timetable as $day => $hours) {
                        if ($hours[0][0] != '') {
                            $code .= '<div><div>' . sb_(ucfirst($day)) . '</div><div data-time="' . $hours[0][0] . '|' . $hours[1][0] . '|' . $hours[2][0] . '|' . $hours[3][0] . '"></div></div>';
                        }
                    }
                }
                $code .= '<span></span></div>';
                break;
            case 'articles':
                $articles_title = sb_get_setting('articles-title');
                $code = '<div class="sb-dashboard-articles"><div class="sb-title">' . sb_($articles_title == '' ? 'Help Center' : $articles_title) . '</div><div class="sb-input sb-input-btn"><input placeholder="' . sb_('Search for articles...') . '" autocomplete="off"><div class="sb-submit-articles sb-icon-arrow-right"></div></div><div class="sb-articles">';
                $articles = sb_get_articles(-1, 2);
                for ($i = 0; $i < count($articles); $i++) {
                    $code .= '<div data-id="' . $articles[$i]['id'] . '"><div>' . $articles[$i]['title'] . '</div><span>' . $articles[$i]['content'] . '</span></div>';
                }
                $code .= '</div><div class="sb-btn sb-btn-all-articles">' .sb_('All articles') . '</div></div>';
                break;
            case 'woocommerce-cart':
                $code = sb_woocommerce_rich_messages($name);
                break;
        }
        return ($title == '' ? '' : '<div class="sb-top">' . $title . '</div>') . ($message == '' ? '' : '<div class="sb-text">' . $message . '</div>') . $code  .  '<div data-success="' . $success . '" class="sb-info"></div>';
    } else {
        $result = sb_get_external_settings('rich-messages');
        if (is_array($result)) {
            for ($i = 0; $i < count($result['rich-messages'][0]); $i++) {
                $item = $result['rich-messages'][0][$i];
                if ($item['rich-message-name'] == $name) {
                    return $item['rich-message-content'];
                }
            }
        }
    }
    return false;
}

function sb_rich_value($value, $merge_fields = true, $tranlsate = true) {
    $value = sb_(str_replace('"', '\'', strip_tags($value)));
    $value = $tranlsate ? sb_($value) : $value;
    return $merge_fields ? sb_merge_fields($value) : $value;
}

function sb_get_shortcode($message, $name, $type = 'merge') {
    $separator =  $type == 'merge' ? ['{', '}'] : ['[', ']'];
    $response = false;
    $name = $separator[0] . $name;
    if (strpos($message, $name) !== false) {
        $position = strpos($message, $name);
        $code = substr($message, $position, strpos($message, $separator[1], $position) + 1);
        $response = ['shortcode' => $code];
        $values = [];
        if (preg_match_all('/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|\'([^\']*)\'(?:\s|$)|(\S+)(?:\s|$)/', substr($code,1,-1), $values, PREG_SET_ORDER)) {
            for ($i = 0; $i < count($values); $i++){
                if (count($values[$i]) == 3 && !empty($values[$i][1]) && !empty($values[$i][2])){
                    $response[$values[$i][1]] = $values[$i][2];
                }
            }
        }
    }
    return $response;
}

/*
 * -----------------------------------------------------------
 * # AGENTS RATING
 * -----------------------------------------------------------
 *
 * Set and get the agetns rating
 *
 */

function sb_set_rating($settings, $payload = false, $message_id = false, $message = false) {
    if (!isset($settings['conversation_id'])) {
        return new SBValidationError('conversation-id-not-found');
    } else {
        if (!sb_is_agent()) {
            $check_id = sb_db_get('SELECT user_id FROM sb_conversations WHERE id = "' . $settings['conversation_id'] . '" LIMIT 1');
            if (!isset($check_id['user_id']) || $check_id['user_id'] != sb_get_active_user()['id']) {
                return new SBError('security-error', 'sb_set_rating');
            }
        }
    }
    if (isset($settings['rating'])) {
        $ratings = sb_get_external_settings('ratings');
        if (!isset($ratings)) {
            $ratings = [];
        }
        $ratings[$settings['conversation_id']] = $settings;
        $json = sb_db_json_enconde($ratings);
        sb_db_query('INSERT INTO sb_settings(name, value) VALUES (\'ratings\', \'' . $json . '\') ON DUPLICATE KEY UPDATE value = \'' . $json . '\'');
        if ($message_id != false) {
            sb_update_message($message_id, $message, false, $payload);
        }
        return true;
    }
    return false;
}

function sb_get_rating($agent_id) {
    $ratings = sb_get_external_settings('ratings');
    $positive = 0;
    $negative = 0;
    if (isset($ratings)) {
        foreach ($ratings as $rating) {
            if (sb_isset($rating, 'agent_id', -1) == $agent_id){
                if ($rating['rating'] == 1) {
                    $positive++;
                } else {
                    $negative++;
                }
            }
        }
    }
    return [$positive, $negative];
}

/*
 * -----------------------------------------------------------
 * # ARTICLES
 * -----------------------------------------------------------
 *
 * Save and get articles functions
 *
 */

function sb_save_articles($articles) {
    $json = sb_db_json_enconde($articles);
    if ($json != false) {
        return sb_db_query('INSERT INTO sb_settings(name, value) VALUES (\'articles\', \'' . $json . '\') ON DUPLICATE KEY UPDATE value = \'' . $json . '\'');
    }
    return false;
}

function sb_get_articles($id = -1, $count = false, $full = false) {
    $articles = sb_get_external_settings('articles');
    $return = [];
    if ($articles != false) {
        $articles_count = count($articles);
        if ($id == -1) {
            $count = $count === false ? $articles_count : ($articles_count < $count ? $articles_count : $count);
            for ($i = 0; $i < $count; $i++) {
                if ($articles[$i]['title'] != '') {
                    if (!$full) {
                        if (strlen($articles[$i]['content']) > 100) {
                            $articles[$i]['content'] = mb_substr($articles[$i]['content'], 0, 100) . '...';
                        }
                        $articles[$i]['content'] = strip_tags($articles[$i]['content']);
                    }
                    array_push($return, $articles[$i]);
                }
            }
        } else {
            for ($i = 0; $i < $articles_count; $i++) {
                if ($articles[$i]['id'] == $id) {
                    return $articles[$i];
                }
            }
            return false;
        }
    }
    return $return;
}

function sb_search_articles($search) {
    $articles = sb_get_external_settings('articles');
    $return = [];
    $search = strtolower($search);
    if ($articles != false) {
        for ($i = 0; $i < count($articles); $i++) {
            if (strpos(strtolower($articles[$i]['title']), $search) !== false || strpos(strtolower($articles[$i]['content']), $search)) {
                $articles[$i]['content'] = mb_substr($articles[$i]['content'], 0, 100);
                array_push($return, $articles[$i]);
            }
        }
    }
    return $return;
}

/*
 * -----------------------------------------------------------
 * # CSV FILES GENERATION
 * -----------------------------------------------------------
 *
 * Create a CSV file from an array
 *
 */

function sb_csv($items, $header, $filename) {
    $file = fopen(SB_PATH . '/uploads/' . $filename . '.csv', 'w');
    if (isset($header)) {
        fputcsv($file, $header);
    }
    for ($i = 0; $i < count($items); $i++) {
    	fputcsv($file, $items[$i]);
    }
    fclose($file);
    return SB_URL . '/uploads/' . $filename . '.csv?v=' . rand(1, 100);
}

function sb_csv_users() {
    $custom_fields = sb_get_setting('user-additional-fields');
    $header = ['Birthdate', 'City', 'Company', 'Country', 'Facebook', 'Language', 'LinkedIn', 'Phone', 'Twitter', 'Website'];
    $users = sb_db_get('SELECT id, first_name, last_name, email, profile_image, user_type, creation_time FROM sb_users WHERE user_type <> "bot" ORDER BY first_name', false);
    if (isset($custom_fields) && is_array($custom_fields)) {
        for ($i = 0; $i < count($custom_fields); $i++) {
            array_push($header, $custom_fields[$i]['extra-field-name']);
        }
    }
    for ($i = 0; $i < count($users); $i++) {
        $user = $users[$i];
        if ($user['user_type'] != 'visitor' && $user['user_type'] != 'lead') {
            $user_extra = sb_db_get('SELECT * FROM sb_users_data WHERE user_id = ' . $user['id'], false);
            for ($j = 0; $j < count($header); $j++) {
                $key = $header[$j];
                $user[$key] = '';
                for ($y = 0; $y < count($user_extra); $y++) {
                    if ($user_extra[$y]['name'] == $key) {
                        $user[$key] = $user_extra[$y]['value'];
                        break;
                    }
                }
            }
        } else {
            for ($j = 0; $j < count($header); $j++) {
                $user[$header[$j]] = '';
            }
        }
        $users[$i] = $user;
    }
    return sb_csv($users, array_merge(['ID', 'First Name', 'Last Name', 'Email', 'Profile Image', 'Type', 'Creation Time'], $header), 'users');
}

function sb_csv_conversations($conversation_id) {
    if ($conversation_id != -1) {
        $conversation = sb_db_get('SELECT * FROM sb_messages WHERE conversation_id="' . $conversation_id . '"', false);
        return sb_csv($conversation, ['ID', 'User ID', 'Title', 'Status Code', 'Creation date'], 'conversation-' . $conversation_id);
    }
    return false;
}

/*
 * ----------------------------------------------------------
 * CSS
 * ----------------------------------------------------------
 *
 * Generate the CSS with values setted in the settings area
 *
 */

function sb_css() {
    $css = '';
    $color_1 = sb_get_setting('color-1');
    $color_2 = sb_get_setting('color-2');
    $color_3 = sb_get_setting('color-3');
    if ($color_1 != '') {
        $css .= '.sb-chat-btn, .sb-chat>div>.sb-header,.sb-chat .sb-dashboard>div>.sb-btn:hover,.sb-chat .sb-scroll-area .sb-header,.sb-input.sb-input-btn>div,div ul.sb-menu li:hover,
                 .sb-select ul li:hover,.sb-popup.sb-emoji .sb-emoji-bar>div.sb-active, .sb-popup.sb-emoji .sb-emoji-bar>div:hover,.sb-btn,a.sb-btn,.sb-rich-message[disabled] .sb-buttons .sb-btn { background-color: ' . $color_1 . '; }';
        $css .= '.sb-chat .sb-dashboard>div>.sb-btn,.sb-search-btn>input,.sb-input>input:focus, .sb-input>select:focus, .sb-input>textarea:focus,.sb-input.sb-input-image .image:hover { border-color: ' . $color_1 . '; }';
        $css .= '.sb-chat .sb-dashboard>div>.sb-btn,.sb-editor .sb-bar-icons>div:hover:before,.sb-articles>div:hover>div,.sb-main .sb-btn-text:hover,.sb-editor .sb-submit,
                 .sb-select p:hover,div ul.sb-menu li.sb-active, .sb-select ul li.sb-active,.sb-search-btn>i:hover,.sb-search-btn.sb-active i,.sb-rich-message .sb-input>span.sb-active:not(.sb-filled),
                 .sb-input.sb-input-image .image:hover:before,.sb-rich-message .sb-card .sb-card-btn,.sb-slider-arrow:hover,.sb-loading:before { color: ' . $color_1 . '; }';
        $css .= '.sb-search-btn>input:focus,.sb-input>input:focus, .sb-input>select:focus, .sb-input>textarea:focus,.sb-input.sb-input-image .image:hover { box-shadow: 0 0 5px rgba(104, 104, 104, 0.2); }';
        $css .= '.sb-list>div.sb-rich-cnt { border-top-color: ' . $color_1 . '; }';
    }
    if ($color_2 != '') {
        $css .= '.sb-chat-btn:hover,.sb-input.sb-input-btn>div:hover,.sb-btn:hover,a.sb-btn:hover,.sb-rich-message .sb-card .sb-card-btn:hover { background-color: ' . $color_2 . '; }';
        $css .= '.sb-list>.sb-right .sb-message, .sb-list>.sb-right .sb-message a,.sb-editor .sb-submit:hover { color: ' . $color_2 . '; }';
    }
    if ($color_3 != '') {
        $css .= '.sb-list>.sb-right,.sb-user-conversations>li:hover { background-color: ' . $color_3 . '; }';
    }
    if ($css != '') {
        echo '<style>' . $css . '</style>';
    }
}

/*
 * ----------------------------------------------------------
 * # EMAIL
 * ----------------------------------------------------------
 *
 * All the email functions
 *
 */

function sb_email_create($recipient_id, $sender_name, $sender_profile_image, $message, $attachments = [], $department = false, $conversation_id = false) {
    $recipient = false;
    $recipient_name = '';
    $recipient_email = '';
    $recipient_user_type = 'agent';

    if ($recipient_id == 'email-test') {
        $recipient_name = 'Test user';

    } else if ($recipient_id == -1) {
        $agents = sb_db_get('SELECT first_name, last_name, email FROM sb_users WHERE (user_type = "agent" OR user_type = "admin")' . (empty($department) ? '' : ' AND department = ' . $department), false);
        for ($i = 0; $i < count($agents); $i++) {
            $recipient_name .= sb_get_user_name($agents[$i]) . ', ';
            $recipient_email .= $agents[$i]['email'] . ',';
        }
        $recipient_name = mb_substr($recipient_name, 0, -2);
        $recipient_email = substr($recipient_email, 0, -1);
    } else {
        if (!sb_email_security($recipient_id)) {
            return new SBError('security-error', 'sb_email_create');
        }
        $recipient = sb_get_user($recipient_id);
        if ($recipient == false || $recipient['email'] == '') {
            return new SBValidationError('email-not-found');
        }
        $recipient_name = sb_get_user_name($recipient);
        $recipient_email = $recipient['email'];
        $recipient_user_type = $recipient['user_type'];
    }

    $suffix = sb_is_agent($recipient_user_type) ? 'agent' : 'user';
    $settings = sb_get_external_settings('emails')['email-' . $suffix][0];
    $email = sb_email_create_content($settings['email-' . $suffix . '-subject'][0], $settings['email-' . $suffix . '-content'][0], $attachments, ['message' => $message, 'recipient_name' => $recipient_name, 'sender_name' => $sender_name, 'sender_profile_image' => $sender_profile_image, 'conversation_id' => $conversation_id]);
    return sb_email_send($recipient_email, $email[0], $email[1]);
}

function sb_email_create_content($subject, $message, $attachments, $replacements) {
    $attachments_code = '';
    for ($i = 0; $i < count($attachments); $i++) {
        $attachments_code .= '<a style="display:block;text-decoration:none;line-height:25px;color:rgb(102, 102, 102);" href="' . str_replace(' ', '%20', $attachments[$i][1]) . '">' . $attachments[$i][0] . '</a>';
    }
    if ($attachments_code != '') {
        $attachments_code = '<div style="margin-top: 30px">' . $attachments_code . '</div>';
    }
    if ($subject == '') {
        $subject = '[Support Board] New message from {sender_name}';
    }
    if ($message == '') {
        $message = '<br />Hello {recipient_name}!<br /><br />{message}{attachments}
                              <table cellspacing="0" border="0" cellpadding="0" bgcolor="transparent" style="border:none;border-collapse:separate;border-spacing:0;margin:0;table-layout:fixed">
                                <tr>
                                    <td valign="middle" width="50" align="left" style="border:none;padding:0;vertical-align:middle">
                                      <img width="40" height="40" src="{sender_profile_image}" style="border-radius:20px;display:inline-block;outline:none;">
                                    </td>
                                  <td valign="middle" align="left" style="border:none;font-family:Helvetica,Arial,sans-serif;padding:0;vertical-align:middle">
                                    <strong style="color:#000">{sender_name}</strong>
                                  </td>
                                </tr>
                             </table>';
    }
    $subject = str_replace(['{recipient_name}', '{sender_name}'], [$replacements['recipient_name'], $replacements['sender_name']], $subject);
    $message = str_replace(['{recipient_name}', '{sender_name}', '{sender_profile_image}', '{message}', '{attachments}', '{conversation_link}'], [$replacements['recipient_name'], $replacements['sender_name'],  $replacements['sender_profile_image'], $replacements['message'], $attachments_code, (SB_URL . '/admin.php?conversation=' . $replacements['conversation_id'])], $message);
    return [$subject, $message];
}

function sb_email_send($to, $subject, $message) {
    $settings = sb_get_setting('email-server');
    if (file_exists(SB_PATH . '/resources/phpmailer/PHPMailerAutoload.php') && $settings['email-server-host'] != '') {
        require_once SB_PATH . '/resources/phpmailer/PHPMailerAutoload.php';
        $port = $settings['email-server-port'];
        $mail = new PHPMailer;
        $message = nl2br(trim(sb_text_formatting_to_html($message)));
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        $mail->isSMTP();
        $mail->Host = $settings['email-server-host'];
        $mail->SMTPAuth = true;
        $mail->Username = $settings['email-server-user'];
        $mail->Password = $settings['email-server-password'];
        $mail->SMTPSecure = $port == 25 ? '' : ($port == 465 ? 'ssl' : 'tls');
        $mail->Port = $port;
        $mail->setFrom($settings['email-server-from'], sb_isset($settings,  'email-sender-name', ''));
        $mail->isHTML(true);
        $mail->Subject = trim($subject);
        $mail->Body    = $message;
        $mail->AltBody = $message;

        if (strpos($to, ',') > 0) {
            $emails = explode(',', $to);
            for ($i = 0; $i < count($emails); $i++) {
                $mail->addAddress($emails[$i]);
            }
        } else {
            $mail->addAddress($to);
        }
        if(!$mail->send()) {
            return new SBError('email-error', 'sb_email_send', $mail->ErrorInfo);
        } else {
            return true;
        }
    } else {
        return mail($to, $subject, $message);
    }
}

function sb_email($recipient_id, $message, $attachments = [], $sender_id = -1) {
    if ($recipient_id == false || $message == false || $message == '') {
        return new SBValidationError('missing-user-id-or-message');
    }
    if (!sb_email_security($recipient_id)) {
        return new SBError('security-error', 'sb_email');
    }
    $sender = $sender_id == -1 ? sb_get_active_user() : sb_get_user($sender_id);
    $user = sb_get_user($recipient_id);
    if ($sender != false && isset($sender['id']) && $user != false && isset($user['id'])) {
        if ($user['email'] == '') {
            return new SBValidationError('user-email-not-found');
        }
        $email_type = sb_is_agent($user['id']) ? 'agent' : 'user';
        $emails = sb_get_external_settings('emails')['email-' . $email_type][0];
        $email = sb_email_create_content($emails['email-' . $email_type . '-subject'][0], $emails['email-' . $email_type . '-content'][0], $attachments, ['message' => $message, 'recipient_name' => sb_get_user_name($user), 'sender_name' => sb_get_user_name($sender), 'sender_profile_image' => $sender['profile_image']]);
        return sb_email_send($user['email'], $email[0], $email[1]);
    } else {
        return new SBError('user-or-sender-not-found', 'sb_email');
    }
}

function sb_email_send_test($to, $email_type) {
    $user = sb_get_active_user();
    $name = sb_get_user_name($user);
    $image = SB_URL . '/media/user.png';
    $attachments = [['Example link', $image], ['Example link two', $image]];
    $settings = sb_get_external_settings('emails')['email-' . $email_type][0];
    $email = sb_email_create_content($settings['email-' . $email_type . '-subject'][0], $settings['email-' . $email_type . '-content'][0], $attachments, ['message' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam', 'recipient_name' => $name, 'sender_name' => $name, 'sender_profile_image' => $user['profile_image']]);
    return sb_email_send($to, $email[0], $email[1]);
}

function sb_email_security($user_id) {
    if (sb_is_agent()) {
        return true;
    } else {
        $user = sb_db_get('SELECT user_type FROM sb_users WHERE id = "' . $user_id . '"');
        return !sb_is_error($user) && isset($user['user_type']) && sb_is_agent($user['user_type']);
    }
}

function sb_text_formatting_to_html($message) {
    $regex = [['/\*(.*?)\*/', '<b>', '</b>'], ['/__(.*?)__/', '<em>', '</em>'], ['/~(.*?)~/', '<del>', '</del>'], ['/```(.*?)```/', '<code>', '</code>'], ['/`(.*?)`/', '<code>', '</code>']];
    for ($i = 0; $i < count($regex); $i++) {
        $values = [];
        if (preg_match_all($regex[$i][0], $message, $values, PREG_SET_ORDER)) {
            for ($j = 0; $j < count($values); $j++){
                $message = str_replace($values[$j][0], $regex[$i][1] . $values[$j][1] . $regex[$i][2], $message);
            }
        }
    }
    return $message;
}

/*
 * ----------------------------------------------------------
 * # USER AUTO DATA
 * ----------------------------------------------------------
 *
 * Save automatic inoformation from the user: IP, Country, OS, Browser
 *
 */

function sb_user_autodata($user_id) {

    if (!defined('SB_API')) {
        $settings = [];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        // IP and related data
        if (strlen($_SERVER['REMOTE_ADDR']) > 6) {
            $settings['ip'] = [$_SERVER['REMOTE_ADDR'], 'IP'];
            $ip_data = json_decode(sb_download('http://ip-api.com/json/' . $settings['ip'][0] . '?fields=status,country,countryCode,city,timezone,currency'), true);
            if (isset($ip_data['status']) && $ip_data['status'] == 'success') {
                if (isset($ip_data['city']) && isset($ip_data['country'])) {
                    $settings['location'] = [$ip_data['city'] . ', ' . $ip_data['country'], 'Location'];
                }
                if (isset($ip_data['timezone'])) {
                    $settings['timezone'] = [$ip_data['timezone'], 'Timezone'];
                }
                if (isset($ip_data['currency'])) {
                    $settings['currency'] = [$ip_data['currency'], 'Currency'];
                }
                if (isset($ip_data['countryCode'])) {
                    $settings['country_code'] = [$ip_data['countryCode'], 'Country Code'];
                }
            }
        }

        // Browser
        $browser = '';
        $agent = strtolower($user_agent);
        if (strpos($agent, 'safari/') and strpos($agent, 'opr/')) {
            $browser = 'Opera';
        } else if (strpos($agent, 'safari/') and strpos($agent, 'chrome/') and strpos($agent, 'edge/') == false) {
            $browser = 'Chrome';
        } else if (strpos($agent, 'msie')) {
            $browser = 'Internet Explorer';
        } else if (strpos($agent, 'firefox/')) {
            $browser = 'Firefox';
        } else if (strpos($agent, 'edge/')) {
            $browser = 'Microsoft Edge';
        } else if (strpos($agent, 'safari/') and strpos($agent, 'opr/') == false and strpos($agent, 'chrome/') == false) {
            $browser = 'Safari';
        };
        if ($browser != '') {
            $settings['browser'] = [$browser, 'Browser'];
        }

        // Browser language
        $settings['browser_language'] = [strtoupper(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2)), 'Language'];

        // OS
        $os = '';
        $os_array = ['/windows nt 10/i' =>  'Windows 10', '/windows nt 6.3/i' => 'Windows 8.1', '/windows nt 6.2/i' => 'Windows 8',  '/windows nt 6.1/i' => 'Windows 7',  '/windows nt 6.0/i' => 'Windows Vista', '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',  '/windows xp/i' => 'Windows XP', '/windows nt 5.0/i' => 'Windows 2000', '/windows me/i' => 'Windows ME', '/macintosh|mac os x/i' => 'Mac OS X', '/mac_powerpc/i' => 'Mac OS 9', '/linux/i' => 'Linux', '/ubuntu/i' => 'Ubuntu', '/iphone/i' => 'iPhone', '/ipod/i' => 'iPod', '/ipad/i' => 'iPad', '/android/i' => 'Android', '/blackberry/i' => 'BlackBerry', '/webos/i' => 'Mobile' ];
        foreach ($os_array as $regex => $value) {
            if (preg_match($regex, $user_agent)) {
                $os = $value;
            }
        }
        if ($os != '') {
            $settings['os'] = [$os, 'OS'];
        }

        // Current url
        if (isset($_SERVER['HTTP_REFERER'])) {
            $settings['current_url'] = [$_SERVER['HTTP_REFERER'], 'Current URL'];
        }

        // Save the data
        return sb_add_new_user_extra($user_id, $settings);
    }
    return false;
}

/*
 * ----------------------------------------------------------
 * # CLEAN DATA
 * ----------------------------------------------------------
 *
 * Delete visitors older than 24h.
 * Delete messages in trash older than 30 days.
 * Archive conversation older than 24h with status code equal to 4 (pending user reply).
 *
 */

function sb_clean_data() {
    $time_24h = gmdate('Y-m-d H:i:s', time() - 86400);
    $time_30d = gmdate('Y-m-d H:i:s', time() - 2592000);
    sb_db_query('DELETE FROM sb_users WHERE user_type = "visitor" AND creation_time < "' . $time_24h . '"');
    sb_db_query('DELETE FROM sb_conversations WHERE status_code = 4 AND creation_time < "' . $time_30d . '"');
    if (sb_get_setting('admin-auto-archive')) {
        sb_db_query('UPDATE sb_conversations SET status_code = 3 WHERE (status_code = 1 OR status_code = 0) AND id IN (SELECT conversation_id FROM sb_messages WHERE creation_time IN (SELECT max(creation_time) latest_creation_time FROM sb_messages GROUP BY conversation_id) AND creation_time < "' . $time_24h . '")');
    }
    return true;
}

/*
 * ----------------------------------------------------------
 * # CURRENT URL
 * ----------------------------------------------------------
 *
 * Set and get the current page url of the user
 *
 */

function sb_current_url($user_id = false, $url = false) {
    if ($user_id != '' && $user_id !== false) {
        if ($url === false) {
            $url = sb_db_get('SELECT value FROM sb_users_data WHERE user_id ="' . $user_id . '" and slug = "current_url" LIMIT 1');
            return isset($url['value']) ? $url['value'] : false;
        }
        return sb_update_user_value($user_id, 'current_url', $url, 'Current URL');
    }
    return false;
}

/*
 * ----------------------------------------------------------
 * # INSTALLATION
 * ----------------------------------------------------------
 *
 * Plugin installation
 *
 */

function sb_installation($details) {
    $database = [];
    if (sb_db_check_connection() === true) {
        return true;
    }
    if (!isset($details['db-name']) || !isset($details['db-user']) || !isset($details['db-password']) || !isset($details['db-host'])) {
        return new SBValidationError('missing-details');
    } else {
        $database = ['name' => $details['db-name'][0], 'user' => $details['db-user'][0], 'password' => $details['db-password'][0], 'host' => $details['db-host'][0], 'port' => (isset($details['db-port']) && $details['db-port'][0] != '' ? intval($details['db-port'][0]) : ini_get('mysqli.default_port'))];
    }
    if (!isset($details['url'])) {
        return new SBValidationError('missing-url');
    } else {
        if (substr($details['url'], -1) == '/') {
            $details['url'] = substr($details['url'], 0, -1);
        }
    }
    $connection_check = sb_db_check_connection($database['name'], $database['user'], $database['password'], $database['host'], $database['port']);
    $db_respones = [];
    $success = '';
    if ($connection_check === true) {

        // Create the database
        $connection = new mysqli($database['host'], $database['user'], $database['password'], $database['name'], $database['port']);
        $connection->set_charset('utf8mb4');
        $db_respones['users'] = $connection->query('CREATE TABLE IF NOT EXISTS sb_users (id INT NOT NULL AUTO_INCREMENT, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, password VARCHAR(100), email VARCHAR(255) UNIQUE, profile_image VARCHAR(255), user_type VARCHAR(10) NOT NULL, creation_time DATETIME NOT NULL, token VARCHAR(50) NOT NULL UNIQUE, last_activity DATETIME, typing INT DEFAULT -1, department TINYINT, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
        $db_respones['users_data'] = $connection->query('CREATE TABLE IF NOT EXISTS sb_users_data (id INT NOT NULL AUTO_INCREMENT, user_id INT NOT NULL, slug VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, value TEXT NOT NULL, PRIMARY KEY (id), FOREIGN KEY (user_id) REFERENCES sb_users(id) ON DELETE CASCADE, UNIQUE INDEX sb_users_data_index (user_id, slug)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
        $db_respones['conversations'] = $connection->query('CREATE TABLE IF NOT EXISTS sb_conversations (id int NOT NULL AUTO_INCREMENT, user_id INT NOT NULL, title VARCHAR(255), creation_time DATETIME NOT NULL, status_code TINYINT DEFAULT 0, department TINYINT, agent_id INT,  PRIMARY KEY (id), FOREIGN KEY (agent_id) REFERENCES sb_users(id) ON DELETE CASCADE, FOREIGN KEY (user_id) REFERENCES sb_users(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
        $db_respones['messages'] = $connection->query('CREATE TABLE IF NOT EXISTS sb_messages (id int NOT NULL AUTO_INCREMENT, user_id INT NOT NULL, message TEXT NOT NULL, creation_time DATETIME NOT NULL, status_code TINYINT DEFAULT 0, attachments TEXT, payload TEXT, conversation_id INT NOT NULL, PRIMARY KEY (id), FOREIGN KEY (user_id) REFERENCES sb_users(id) ON DELETE CASCADE, FOREIGN KEY (conversation_id) REFERENCES sb_conversations(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin');
        $db_respones['settings'] = $connection->query('CREATE TABLE IF NOT EXISTS sb_settings (name VARCHAR(255) NOT NULL, value TEXT, PRIMARY KEY (name)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

        // Create the admin user
        if (isset($details['first-name']) && isset($details['last-name']) && isset($details['email']) && isset($details['password'])) {
            $now = gmdate('Y-m-d H:i:s');
            $token = bin2hex(openssl_random_pseudo_bytes(20));
            $db_respones['admin'] = $connection->query('INSERT IGNORE INTO sb_users(first_name, last_name, password, email, profile_image, user_type, creation_time, token, last_activity) VALUES ("' . sb_db_escape($details['first-name'][0]) . '", "' . sb_db_escape($details['last-name'][0]) . '", "' . sb_db_escape(defined('SB_WP') ? $details['password'][0] : password_hash($details['password'][0], PASSWORD_DEFAULT)) . '", "' . $details['email'][0] . '", "' . $details['url'] . '/media/user.svg' . '", "admin", "' . $now . '", "' . $token . '", "' . $now . '")');
        }

        // Create the config.php file
        $raw = file_get_contents(SB_PATH . '/resources/config-source.php');
        $raw = str_replace(['[url]', '[name]', '[user]', '[password]', '[host]', '[port]'], [$details['url'], $database['name'], $database['user'], $database['password'], $database['host'], (isset($details['db-port']) && $details['db-port'][0] != '' ? $database['port'] : '')], $raw);
        if (defined('SB_WP')) {
            $raw = str_replace('/* [extra] */', sb_wp_config(), $raw);
        }
        sb_file(SB_PATH . '/config.php', $raw);
        foreach ($db_respones as $key => $value) {
            if ($value !== true) {
                $success .= $key . ': ' . ($value === false ? 'false' : $value) . ',';
            }
        }
        if ($success == '') {
            return true;
        } else {
            return substr($success, 0, -1);
        }
    } else {
        return $connection_check;
    }
}

/*
 * ----------------------------------------------------------
 * # APPS AND UPDATES
 * ----------------------------------------------------------
 *
 * Get the plugin and apps versions and install, activate and update apps
 *
 */

function sb_get_versions() {
    return json_decode(sb_download('https://board.support/synch/versions.json'), true);
}

function sb_app_get_key($app_name) {
    $keys = sb_get_external_settings('app-keys');
    return isset($keys[$app_name]) ? $keys[$app_name] : '';
}

function sb_app_activation($app_name, $key) {
    $key = trim($key);
    $response = json_decode(sb_download('https://board.support/synch/updates.php?' . $app_name . '=' . $key), true);
    if (!isset($response[$app_name]) || $response[$app_name] == '') {
        return new SBValidationError('invalid-key');
    }
    if ($response[$app_name] == 'expired') {
        return new SBValidationError('expired');
    }
    return sb_app_update($app_name, $response[$app_name], $key);
}

function sb_app_update($app_name, $file_name, $key = false) {
    if ($file_name == '') {
        return new SBValidationError('temporary-file-name-not-found');
    }
    $key = trim($key);
    $error = '';
    $zip = sb_download('http://board.support/synch/temp/' . $file_name);
    if ($zip != false) {
        $file_path = SB_PATH . '/uploads/' . $app_name . '.zip';
        file_put_contents($file_path, $zip);
        if (file_exists($file_path)) {
            $zip = new ZipArchive;
            if ($zip->open($file_path) === true) {
                $zip->extractTo(SB_PATH . ($app_name == 'sb' ? '' : '/apps'));
                $zip->close();
                unlink($file_path);
                if ($app_name == 'sb') {
                    sb_restore_user_translations();
                    return 'success';
                }
                if (file_exists(SB_PATH . '/apps/' . $app_name)) {
                    if ($key !== false) {
                        $keys = sb_get_external_settings('app-keys');
                        $keys[$app_name] = $key;
                        sb_save_external_setting('app-keys', $keys);
                    }
                    return 'success';
                } else {
                    $error = 'zip-extraction-error';
                }
            } else {
                $error = 'zip-error';
            }
        } else {
            $error = 'file-not-found';
        }
    } else {
        $error = 'download-error';
    }
    if ($error != '') {
        return new SBValidationError($error);
    }
    return false;
}

function sb_update() {
    $envato_code = sb_get_setting('envato-purchase-code');
    if ($envato_code == '') {
        return new SBValidationError('envato-purchase-code-not-found');
    }
    $latest_versions = sb_get_versions();
    $installed_apps_versions = ['dialogflow' => defined('SB_DIALOGFLOW') ? SB_DIALOGFLOW : -1, 'slack' => defined('SB_SLACK') ? SB_SLACK : -1, 'tickets' => defined('SB_TICKETS') ? SB_TICKETS : -1, 'woocommerce' => defined('SB_WOOCOMMERCE') ? SB_WOOCOMMERCE : -1];
    $keys = sb_get_external_settings('app-keys');
    $result = [];
    $link = SB_VERSION != $latest_versions['sb'] ? 'sb=' . sb_get_setting('envato-purchase-code') . '&' : '';
    foreach ($installed_apps_versions as $key => $value) {
        if ($value != -1 && $value != $latest_versions[$key]) {
            if (isset($keys[$key])) {
                $link .= $key . '=' . $keys[$key] . '&';
            } else {
                $result[$key] = 'license-key-not-found';
            }
        }
    }
    $downloads = json_decode(sb_download('https://board.support/synch/updates.php?' . substr($link, 0, -1)), true);
    foreach ($downloads as $key => $value) {
        if ($value != '') {
            $result[$key] = sb_app_update($key, $value);
        }
    }
    return $result;
}

function sb_updates_check() {
    if (!isset($_COOKIE['sb-updates']) || $_COOKIE['sb-updates'] != SB_VERSION) {

        // Support Board 3.0.4 | 05-2020
        sb_db_query('ALTER TABLE sb_conversations ADD COLUMN agent_id INT, ADD FOREIGN KEY (agent_id) REFERENCES sb_users(id) ON DELETE CASCADE');

        // Support Board 3.0.1 | 04-2020
        sb_db_query('ALTER TABLE sb_users ADD COLUMN department TINYINT');
        sb_db_query('ALTER TABLE sb_conversations ADD COLUMN department TINYINT');

        // Save the cookie for 1 year
        if (!headers_sent()) {
            setcookie('sb-updates', SB_VERSION, time() + 31556926, '/');
        }
    }
}

/*
 * ----------------------------------------------------------
 * # BOT CREATION AND UPDATE
 * ----------------------------------------------------------
 *
 * Create or update the bot
 *
 */

function sb_update_bot($name = '', $profile_image = '') {
    $bot = sb_db_get('SELECT id, profile_image, first_name, last_name FROM sb_users WHERE user_type = "bot" LIMIT 1');
    if ($name == '') {
        $name = 'Bot';
    }
    if ($profile_image == '') {
        $profile_image = SB_URL . '/media/user.svg';
    }
    $settings = ['profile_image' => [$profile_image], 'first_name' => [$name], 'user_type' => ['bot']];
    if ($bot == '') {
        return sb_add_user($settings);
    } else if ($bot['profile_image'] != $profile_image || $bot['first_name'] != $name){
        return sb_update_user($bot['id'], $settings);
    }
    return false;
}

function sb_get_bot_id() {
    $bot_id = sb_isset(sb_db_get('SELECT id FROM sb_users WHERE user_type = "bot" LIMIT 1'), 'id');
    if ($bot_id == false) {
        $bot_id = sb_update_bot();
    }
    return $bot_id;
}

/*
 * ----------------------------------------------------------
 * # WEBHOOKS
 * ----------------------------------------------------------
 *
 * Send all the webhooks functions to the destination URL
 *
 */

function sb_webhooks($function_name, $parameters) {
    if (isset($function_name) && $function_name != '' && isset($parameters)) {
        $names = ['SBLoginForm' => 'login', 'SBRegistrationForm' => 'registration', 'SBUserDeleted' => 'user-deleted', 'SBMessageSent' => 'message-sent', 'SBBotMessage' => 'bot-message', 'SBEmailSent' => 'email-sent', 'SBNewMessagesReceived' => 'new-message', 'SBNewConversationReceived' => 'new-conversation', 'SBNewConversationCreated' => 'new-conversation-created', 'SBActiveConversationStatusUpdated' => 'conversation-status-updated', 'SBSlackMessageSent' => 'slack-message-sent', 'SBMessageDeleted' => 'message-deleted',  'SBRichMessageSubmit' => 'rich-message', 'SBNewEmailAddress' => 'new-email-address'];
        if (isset($names[$function_name])) {
            $webhooks = sb_get_setting('webhooks');
            if ($webhooks !== false && $webhooks['webhooks-url'] != '' && $webhooks['webhooks-active']) {
                if (isset($_SERVER['HTTP_REFERER'])) {
                    $settings['current_url'] = [$_SERVER['HTTP_REFERER'], 'Current URL'];
                }
                $query = json_encode(['function' => $names[$function_name], 'key' =>$webhooks['webhooks-key'], 'data' => $parameters, 'sender-url' => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '')]);
                if ($query != false) {
                    return sb_curl($webhooks['webhooks-url'], $query, [ 'Content-Type: application/json', 'Content-Length: ' . strlen($query)]);
                } else {
                    return new SBError('webhook-json-error');
                }
            } else {
                return new SBValidationError('webhook-not-active-or-empty-url');
            }
        } else {
            return new SBValidationError('webhook-not-found');
        }
    } else {
        return new SBError('invalid-function-or-parameters');
    }
}

/*
 * ----------------------------------------------------------
 * # WRITE FILE
 * ----------------------------------------------------------
 *
 * Create a new file containing the given content and save it in the destination path.
 *
 */

function sb_file($path, $content) {
    try {
        $file = fopen($path, 'w');
        fwrite($file, $content);
        fclose($file);
        return true;
    }
    catch (Exception $e) {
        return $e->getMessage();
    }
}

/*
 * ----------------------------------------------------------
 * # UPLOAD PATH AND URL
 * ----------------------------------------------------------
 *
 * Return the upload path or url
 *
 */

function sb_upload_path($url = false) {
    return defined('SB_UPLOAD_PATH') && SB_UPLOAD_PATH != '' && defined('SB_UPLOAD_URL') && SB_UPLOAD_URL != '' ? ($url ? SB_UPLOAD_URL : SB_UPLOAD_PATH) : ($url ? SB_URL : SB_PATH) . '/uploads';
}

function sb_dir_name() {
    return substr(SB_URL, strrpos(SB_URL, '/') + 1);
}

/*
 * ----------------------------------------------------------
 * # DEBUG
 * ----------------------------------------------------------
 *
 * Debug function
 *
 */

function sb_debug($value) {
    $value = is_string($value) ? $value : json_encode($value);
    if (file_exists('debug.txt')) {
        $value = file_get_contents('debug.txt') . PHP_EOL . $value;
    }
    sb_file('debug.txt', $value);
}

/*
 * ----------------------------------------------------------
 * # JSON ARRAY TO ARRAY
 * ----------------------------------------------------------
 *
 * Convert a JSON string to an array
 *
 */

function sb_json_array($json, $default = []) {
    if (is_string($json)) {
        $json = json_decode($json, true);
        return $json === false || $json === null ? $default : $json;
    } else {
        return $json;
    }
}

/*
 * ----------------------------------------------------------
 * # SYSTEM REQUIREMENTS
 * ----------------------------------------------------------
 *
 * Check the system for requirements and issues
 *
 */

function sb_system_requirements() {
    $checks = [];

    // PHP version
    $checks['php-version'] = PHP_MAJOR_VERSION >= 7;

    // ZipArchive
    $checks['zip-archive'] = class_exists('ZipArchive');

    // File permissions
    $permissions = [['plugin', SB_PATH], ['uploads', sb_upload_path()], ['apps', SB_PATH . '/apps'], ['languages', SB_PATH . '/resources/languages']];
    for ($i = 0; $i < count($permissions); $i++) {
        $path = $permissions[$i][1] . '/sb-permissions-check.txt';
        sb_file($path, 'permissions-check');
        $checks[$permissions[$i][0] . '-folder'] = file_exists($path) && file_get_contents($path) == 'permissions-check';
        if (file_exists($path)) {
            unlink($path);
        }
    }

    // AJAX file
    $checks['ajax'] = sb_download(SB_URL . '/include/ajax.php') == 'true';

    // cURL
    $checks['curl'] = function_exists('curl_version') && is_array(sb_get_versions());

    // MySQL UTF8MB4 support

    //SET NAMES UTF8mb4
    return $checks;
}

/*
 * ----------------------------------------------------------
 * # PUSH NOTIFICATION
 * ----------------------------------------------------------
 *
 * Send a Push notification. This function is powered by Pusher
 *
 */

function sb_push_notification($title = '', $message = '', $icon = '', $interest = false, $conversation_id = false) {
    if (sb_get_multi_setting('push-notifications', 'push-notifications-active')) {
        $user = sb_get_active_user();
        if (is_numeric($interest) || is_array($interest)) {
            $user_type = $user['user_type'];
            $error = new SBError('security-error', 'sb_push_notification');
            if ($user == false) return $error;
            if (!sb_is_agent($user_type)) {
                $agents_ids = sb_db_get('SELECT id FROM sb_users WHERE user_type = "agent" OR user_type = "admin"', false);
                for ($i = 0; $i < count($agents_ids); $i++) {
                    $agents_ids[$i] = intval($agents_ids[$i]['id']);
                }
                if (is_numeric($interest)) {
                    if (!in_array(intval($interest), $agents_ids)) return $error;
                } else {
                    for ($i = 0; $i < count($interest); $i++){
                        if (!in_array(intval($interest[$i]), $agents_ids)) {
                            return $error;
                        }
                    }
                }
            }
        }
        if ($icon == '') {
            $icon = sb_get_setting('notifications-icon');
            if ($icon == '') {
                $icon = SB_URL . '/media/icon.png';
            }
        }
        $attributes = '';
        if ($conversation_id) {
            $attributes = '?conversation=' . $conversation_id . '&user_id=' . $user['id'];
        }
        $instance_ID = sb_get_multi_setting('push-notifications', 'push-notifications-id');
        $query = '{"interests":["' . $interest . '"],"web":{"notification":{"title":"' . str_replace('"', '', $title) . '","body":"' . str_replace('"', '', $message) . '","icon":"' . $icon . '","deep_link":"' . SB_URL . '/admin.php' . $attributes . '","hide_notification_if_site_has_focus":true}}}';
        return sb_curl('https://' . $instance_ID . '.pushnotifications.pusher.com/publish_api/v1/instances/' . $instance_ID . '/publishes',  $query, [ 'Content-Type: application/json', 'Authorization: Bearer ' . sb_get_multi_setting('push-notifications', 'push-notifications-key'), 'Content-Length: ' . strlen($query) ]);
    }
    return false;
}

/*
 * ----------------------------------------------------------
 * # CRON JOBS
 * ----------------------------------------------------------
 *
 * Simulate cron jobs by executing this function everytime a user visit the website.
 * The cron jobs are executed maximum 1 time per hour
 *
 */

function sb_cron_jobs() {
    $now = date('H', time());
    $cron = sb_db_get('SELECT value FROM sb_settings WHERE name="cron"');
    if ($cron == '' || $now != sb_isset($cron, 'value')) {
        $cron_functions = sb_get_external_settings('cron-functions');
        if (defined('SB_WOOCOMMERCE')) {
            sb_woocommerce_cron_jobs($cron_functions);
        }
        sb_clean_data();
        sb_db_query('DELETE FROM sb_settings WHERE name="cron-functions"');
        sb_db_query($cron == '' ? 'INSERT INTO sb_settings(name, value) VALUES ("cron", "' . $now . '")' : 'UPDATE sb_settings SET value = "' . $now . '" WHERE name = "cron"');
    }
}

function sb_cron_jobs_add($key, $content = false, $job_time = false) {

    // Add the job to the cron jobs
    $cron_functions = sb_db_get('SELECT value FROM sb_settings WHERE name="cron-functions"');
    if (empty($cron_functions) || empty($cron_functions['value'])) {
        sb_db_query('INSERT INTO sb_settings(name, value) VALUES ("cron-functions",\'["' . $key . '"]\') ON DUPLICATE KEY UPDATE value = \'["' . $key . '"]\'');
    } else {
        $cron_functions = json_decode($cron_functions['value'], true);
        if (!in_array($key, $cron_functions)) {
            array_push($cron_functions, $key);
            sb_db_query('UPDATE sb_settings SET value = \'' . sb_db_json_enconde($cron_functions) . '\' WHERE name = "cron-functions"');
        }
    }

    // Set the cron job data
    if (!empty($content) && !empty($job_time)) {
        $user = sb_get_active_user();
        if ($user) {
            $key = 'cron-' . $key;
            $scheduled = sb_get_external_settings($key);
            if (empty($scheduled)) {
                $scheduled = [];
            }
            $scheduled[$user['id']] = [$content, strtotime('+' . $job_time)];
            sb_save_external_setting($key, $scheduled);
        }
    }
}
 