<?php

/*
 * ==========================================================
 * COMPONENTS.PHP
 * ==========================================================
 *
 * Library of static html components. This file must not be executed directly. © 2020 board.support. All rights reserved.
 *
 */

?>

<?php

/*
 * ----------------------------------------------------------
 * COMPONENT CHAT
 * ----------------------------------------------------------
 *
 * The chat main block that render the whole chat code.
 *
 */

function sb_component_chat() {
    sb_js_global();
    sb_css();
    $header_headline = sb_get_setting('header-headline');
    $header_message = sb_get_setting('header-msg');
    $background = sb_get_setting('header-img');
    $icon = sb_get_setting('chat-icon');
    $header_type = sb_get_setting('header-type', 'agents');
    $disable_dashboard = sb_get_setting('disable-dashboard');
    $css = '';
    if (sb_get_setting('rtl') || in_array(sb_get_user_language(), ['ar', 'he', 'ku', 'fa', 'ur'])) {
        $css .= ' sb-rtl';
    }
    if (sb_get_setting('chat-position') == 'left') {
        $css .= ' sb-chat-left';
    }
    if ($disable_dashboard) {
        $css .= ' sb-dashboard-disabled';
    }
?>
    <div class="sb-main sb-chat sb-no-conversations<?php echo $css ?>" style="display: none; transition: none;">
        <div class="sb-body">
            <div class="sb-scroll-area">
                <div class="sb-header sb-header-main sb-header-type-<?php echo $header_type ?>" <?php if ($background != '') echo 'style="background-image: url(' . $background . ')"' ?>>
                    <?php if (!$disable_dashboard) echo '<i class="sb-icon-close sb-dashboard-btn"></i>'; ?>
                    <div class="sb-content">
                        <?php if ($header_type == 'brand') echo '<div class="sb-brand"><img src="' . sb_get_setting('brand-img') . '" alt="" /></div>' ?>
                        <div class="sb-title">
                            <?php echo sb_($header_headline != '' ? $header_headline : 'Support Board Chat') ?>
                        </div>
                        <div class="sb-text">
                            <?php echo sb_($header_message != '' ? $header_message : 'We are an experienced team that provides fast and accurate answers to your questions.') ?>
                        </div>
                        <?php

    if ($header_type == 'agents') {
        $agents = sb_db_get('SELECT first_name, profile_image FROM sb_users WHERE user_type = "agent" OR user_type = "admin" LIMIT 3', false);
        $code = '';
        for ($i = 0; $i < count($agents); $i++) {
            $code .= '<div><span>' . $agents[$i]['first_name'] . '</span><img src="' . $agents[$i]['profile_image'] . '" alt="" /></div>';
        }
        echo '<div class="sb-profiles">' . $code . '</div>';
    }

                        ?>
                    </div>
                </div>
                <div class="sb-list sb-active"></div>
                <div class="sb-dashboard">
                    <div class="sb-dashboard-conversations">
                        <div class="sb-title"><?php sb_e('Conversations') ?></div>
                        <ul class="sb-user-conversations"></ul>
                        <?php if (!sb_get_multi_setting('departments-settings', 'departments-dashboard') && !$disable_dashboard) echo '<div class="sb-btn sb-btn-new-conversation">' . sb_('New conversation') . '</div>' ?>
                    </div>
                    <?php if (sb_get_multi_setting('departments-settings', 'departments-dashboard')) echo sb_departments('dashboard') ?>
                    <?php if (sb_get_setting('articles-active')) echo sb_get_rich_message('articles') ?>
                </div>
                <?php if (sb_get_setting('articles-active')) echo '<div class="sb-panel sb-panel-articles"></div>' ?>
            </div>
            <?php sb_component_editor(); ?>
        </div>
        <div class="sb-chat-btn">
            <span data-count="0"></span>
            <img class="sb-icon" alt="" src="<?php echo $icon != '' ? $icon : SB_URL . '/media/button-chat.svg' ?>" />
            <img class="sb-close" alt="" src="<?php echo SB_URL ?>/media/button-close.svg" />
        </div>
        <i class="sb-icon sb-icon-close sb-responsive-close-btn"></i>
        <audio id="sb-audio" preload="auto">
            <source src="<?php echo SB_URL ?>/media/sound.mp3" type="audio/mpeg">
        </audio>
        <audio id="sb-audio-out" preload="auto">
            <source src="<?php echo SB_URL ?>/media/sound-out.mp3" type="audio/mpeg">
        </audio>
        <div class="sb-lightbox-media">
            <div></div>
            <i class="sb-icon-close"></i>
        </div>
        <div class="sb-lightbox-overlay"></div>
    </div>
<?php } ?>
<?php

/*
 * ----------------------------------------------------------
 * COMPONENT EDITOR
 * ----------------------------------------------------------
 *
 * The editor to write the message text and send attachments.
 *
 */

function sb_component_editor($admin = false) { ?>
    <div class="sb-editor">
        <?php if ($admin) echo '<div class="sb-labels"></div>' ?>
        <div class="sb-textarea"><textarea placeholder="<?php sb_e('Write a message...') ?>"></textarea></div>
        <div class="sb-attachments"></div>
        <div class="sb-bar">
            <div class="sb-bar-icons">
                <?php if ($admin || !sb_get_setting('disable-uploads')) echo '<div class="sb-btn-attachment" data-sb-tooltip="' . sb_('Attach a file') . '"></div>'; ?> 
                <div class="sb-btn-saved-replies" data-sb-tooltip="<?php sb_e('Add a saved reply') ?>"></div>
                <div class="sb-btn-emoji" data-sb-tooltip="<?php sb_e('Add an emoji') ?>"></div>
                <?php if ($admin && defined('SB_WOOCOMMERCE')) echo '<div class="sb-btn-woocommerce" data-sb-tooltip="' . sb_('Add a product') . '"></div>' ?>
            </div>
            <div class="sb-icon-send sb-submit" data-sb-tooltip="<?php sb_e('Send message') ?>"></div>
            <img class="sb-loader" src="<?php echo SB_URL ?>/media/loader.svg" alt="" />
        </div>
        <div class="sb-popup sb-emoji">
            <div class="sb-header">
                <div class="sb-select">
                    <p>
                        <?php sb_e('All') ?>
                    </p>
                    <ul>
                        <li data-value="all" class="sb-active">
                            <?php sb_e('All') ?>
                        </li>
                        <li data-value="Smileys">
                            <?php sb_e('Smileys & Emotions') ?>
                        </li>
                        <li data-value="People">
                            <?php sb_e('People & Body') ?>
                        </li>
                        <li data-value="Animals">
                            <?php sb_e('Animals & Nature') ?>
                        </li>
                        <li data-value="Food">
                            <?php sb_e('Food & Drink') ?>
                        </li>
                        <li data-value="Travel">
                            <?php sb_e('Travel & Places') ?>
                        </li>
                        <li data-value="Activities">
                            <?php sb_e('Activities') ?>
                        </li>
                        <li data-value="Objects">
                            <?php sb_e('Objects') ?>
                        </li>
                        <li data-value="Symbols">
                            <?php sb_e('Symbols') ?>
                        </li>
                    </ul>
                </div>
                <div class="sb-search-btn">
                    <i class="sb-icon sb-icon-search"></i>
                    <input type="text" placeholder="<?php sb_e('Search emoji...') ?>" />
                </div>
            </div>
            <div class="sb-emoji-list">
                <ul></ul>
            </div>
            <div class="sb-emoji-bar"></div>
        </div>
        <?php if ($admin) { ?>
            <div class="sb-popup sb-replies">
                <div class="sb-header">
                    <div class="sb-title">
                        <?php sb_e('Saved replies') ?>
                    </div>
                    <div class="sb-search-btn">
                        <i class="sb-icon sb-icon-search"></i>
                        <input type="text" autocomplete="false" placeholder="<?php sb_e('Search replies...') ?>" />
                    </div>
                </div>
                <div class="sb-replies-list sb-scroll-area">
                    <ul class="sb-loading"></ul>
                </div>
            </div>
            <?php if (defined('SB_WOOCOMMERCE')) sb_woocommerce_products_popup() ?>
        <?php } ?>
        <form class="sb-upload-form-editor" action="#" method="post" enctype="multipart/form-data">
            <input type="file" name="files[]" class="sb-upload-files" multiple />
        </form>
    </div>
<?php } ?>
<?php

/*
 * ----------------------------------------------------------
 * PROFILE BOX
 * ----------------------------------------------------------
 *
 * Profile information area used in admin side
 *
 */

function sb_profile_box() { ?>
    <div class="sb-profile-box sb-lightbox">
        <div class="sb-top-bar">
            <div class="sb-profile">
                <img src="<?php echo SB_URL ?>/media/user.svg" />
                <span class="sb-name"></span>
            </div>
            <div>
                <a class="sb-edit sb-btn sb-icon" data-button="toggle" data-hide="sb-profile-area" data-show="sb-edit-area">
                    <i class="sb-icon-user"></i><?php sb_e('Edit user') ?>
                </a>
                <a class="sb-start-conversation sb-btn sb-icon">
                    <i class="sb-icon-message"></i><?php sb_e('Start a conversation') ?>
                </a>
                <a class="sb-close sb-btn-icon" data-button="toggle" data-hide="sb-profile-area" data-show="sb-table-area">
                    <i class="sb-icon-close"></i>
                </a>
            </div>
        </div>
        <div class="sb-main sb-scroll-area">
            <div>
                <div class="sb-title">
                    <?php sb_e('Details') ?>
                </div>
                <div class="sb-profile-list"></div>
                <div class="sb-agent-area"></div>
            </div>
            <div>
                <div class="sb-title">
                    <?php sb_e('User conversations') ?>
                </div>
                <ul class="sb-user-conversations"></ul>
            </div>
        </div>
    </div>
<?php } ?>
<?php

/*
 * ----------------------------------------------------------
 * PROFILE EDIT BOX
 * ----------------------------------------------------------
 *
 * Profile editing area used in admin side
 *
 */

function sb_profile_edit_box() { ?>
    <div class="sb-profile-edit-box sb-lightbox">
        <div class="sb-info"></div>
        <div class="sb-top-bar">
            <div class="sb-profile">
                <img src="<?php echo SB_URL ?>/media/user.svg" />
                <span class="sb-name"></span>
            </div>
            <div>
                <a class="sb-save sb-btn sb-icon">
                    <i class="sb-icon-check"></i><?php sb_e('Save changes') ?>
                </a>
                <a class="sb-close sb-btn-icon" data-button="toggle" data-hide="sb-profile-area" data-show="sb-table-area">
                    <i class="sb-icon-close"></i>
                </a>
            </div>
        </div>
        <div class="sb-main sb-scroll-area">
            <div class="sb-details">
                <div class="sb-title">
                    <?php sb_e('Edit details') ?>
                </div>
                <div class="sb-edit-box">
                    <div id="profile_image" data-type="image" class="sb-input sb-input-image sb-profile-image">
                        <span><?php sb_e('Profile image') ?></span>
                        <div class="image">
                            <div class="sb-icon-close"></div>
                        </div>
                    </div>
                    <div id="user_type" data-type="select" class="sb-input sb-input-select">
                        <span><?php sb_e('Type') ?></span>
                        <select>
                            <option value="agent"><?php sb_e('Agent') ?></option>
                            <option value="admin"><?php sb_e('Admin') ?></option>
                        </select>
                    </div>
                    <?php echo sb_departments('select') ?>
                    <div id="first_name" data-type="text" class="sb-input">
                        <span><?php sb_e('First name') ?></span>
                        <input type="text" required />
                    </div>
                    <div id="last_name" data-type="text" class="sb-input">
                        <span><?php sb_e('Last name') ?></span>
                        <input type="text" required />
                    </div>
                    <div id="password" data-type="text" class="sb-input">
                        <span><?php sb_e('Password') ?></span>
                        <input type="password" />
                    </div>
                    <div id="email" data-type="email" class="sb-input">
                        <span><?php sb_e('Email') ?></span>
                        <input type="email" required />
                    </div>
                </div>
                <a class="sb-delete sb-btn-text sb-btn-red">
                    <i class="sb-icon-delete"></i><?php sb_e('Delete user') ?>
                </a>
            </div>
            <div class="sb-additional-details">
                <div class="sb-title">
                    <?php sb_e('Edit additional details') ?>
                </div>
                <div class="sb-edit-box">
                    <div id="phone" data-type="text" class="sb-input">
                        <span><?php sb_e('Phone') ?></span>
                        <input type="text" />
                    </div>
                    <div id="city" data-type="text" class="sb-input">
                        <span><?php sb_e('City') ?></span>
                        <input type="text" />
                    </div>
                    <div id="country" data-type="text" class="sb-input">
                        <span><?php sb_e('Country') ?></span>
                        <input type="text" />
                    </div>
                    <div id="language" data-type="text" class="sb-input">
                        <span><?php sb_e('Language') ?></span>
                        <input type="text" />
                    </div>
                    <div id="birthdate" data-type="text" class="sb-input">
                        <span><?php sb_e('Birthdate') ?></span>
                        <input type="text" />
                    </div>
                    <div id="company" data-type="text" class="sb-input">
                        <span><?php sb_e('Company') ?></span>
                        <input type="text" />
                    </div>
                    <div id="facebook" data-type="text" class="sb-input">
                        <span><?php sb_e('Facebook') ?></span>
                        <input type="text" />
                    </div>
                    <div id="twitter" data-type="text" class="sb-input">
                        <span><?php sb_e('Twitter') ?></span>
                        <input type="text" />
                    </div>
                    <div id="linkedin" data-type="text" class="sb-input">
                        <span><?php sb_e('LinkedIn') ?></span>
                        <input type="text" />
                    </div>
                    <div id="website" data-type="text" class="sb-input">
                        <span><?php sb_e('Website') ?></span>
                        <input type="text" />
                    </div>
                    <div id="timezone" data-type="text" class="sb-input">
                        <span><?php sb_e('Timezone') ?></span>
                        <input type="text" />
                    </div>
                    <?php

    $additional_fields = sb_get_setting('user-additional-fields');
    if ($additional_fields != false && is_array($additional_fields)) {
        $code = '';
        for ($i = 0; $i < count($additional_fields); $i++) {
            $value = $additional_fields[$i];
            if ($value['extra-field-name'] != '') {
                $code .= '<div id="' . $value['extra-field-slug'] . '" data-type="text" class="sb-input"><span>' . $value['extra-field-name'] . '</span><input type="text"></div>';
            }
        }
        echo $code;
    }

                    ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php

/*
 * ----------------------------------------------------------
 * LOGIN BOX
 * ----------------------------------------------------------
 *
 * Login box used by the admin area
 *
 */

function sb_login_box() { ?>
    <form class="sb sb-rich-login sb-admin-box">
        <div class="sb-info"></div>
        <div class="sb-top-bar">
            <img src="<?php echo sb_get_setting('login-icon') != false ? sb_get_setting('login-icon') : SB_URL . '/media/logo.svg' ?>" />
            <div class="sb-title"><?php sb_e(empty(sb_get_setting('admin-title')) ? 'Sign into' : sb_get_setting('admin-title')) ?></div>
            <div class="sb-text"><?php sb_e(empty(sb_get_setting('login-message')) ? 'To continue to Support Board' : sb_get_setting('login-message')) ?></div>
        </div>
        <div class="sb-main">
            <div id="email" class="sb-input">
                <span><?php sb_e('Email') ?></span>
                <input type="text" />
            </div>
            <div id="password" class="sb-input">
                <span><?php sb_e('Password') ?></span>
                <input type="password" />
            </div>
            <div class="sb-bottom">
                <div class="sb-btn sb-submit-login"><?php sb_e('Login') ?></div>
            </div>
        </div>
    </form>
    <script>
        (function ($) {
            $(document).on('SBReady', function () {
                $('.sb-admin-start').removeAttr('style');
                $('.sb-submit-login').on('click', function () {
                    SBF.loginForm(this, false, function () {
                        location.reload();
                    });
                });
            });
        }(jQuery)); 
    </script>
<?php } ?>
<?php

/*
 * ----------------------------------------------------------
 * CONFIRMATION ALERT BOX
 * ----------------------------------------------------------
 *
 * Ask a yes / no question to confirm an operation
 *
 */

function sb_dialog() { ?>
    <div class="sb-dialog-box sb-lightbox">
        <p></p>
        <div>
            <a class="sb-confirm sb-btn"><?php sb_e('Confirm') ?></a>
            <a class="sb-cancel sb-btn sb-btn-red"><?php sb_e('Cancel') ?></a>
            <a class="sb-close sb-btn"><?php sb_e('Close') ?></a>
        </div>
    </div>
<?php } ?>
<?php

/*
 * ----------------------------------------------------------
 * DIALOGFLOW INTENT BOX
 * ----------------------------------------------------------
 *
 * Display the form to create a new intent for Dialogflow
 *
 */

function sb_dialogflow_intent_box() { ?>
    <div class="sb-lightbox sb-dialogflow-intent-box">
        <div class="sb-info"></div>
        <div class="sb-top-bar">
            <div>Dialogflow Intent</div>
            <div>
                <a class="sb-send sb-btn sb-icon">
                    <i class="sb-icon-check"></i><?php sb_e('Send') ?> Intent
                </a>
                <a class="sb-close sb-btn-icon">
                    <i class="sb-icon-close"></i>
                </a>
            </div>
        </div>
        <div class="sb-main sb-scroll-area">
            <div class="sb-title sb-intent-add"><?php sb_e('Add user expressions') ?> <i data-sb-tooltip="<?php sb_e('Add expression') ?>" class="sb-btn-icon sb-icon-plus"></i></div>
            <div class="sb-input-setting sb-type-text sb-first">
                <input type="text">
            </div>
            <div class="sb-title"><?php sb_e('Response from the bot') ?></div>
            <div class="sb-input-setting sb-type-textarea">
                <textarea></textarea>
            </div>
            <div class="sb-title"><?php sb_e('Language') ?></div>
            <?php echo sb_dialogflow_languages_list() ?>
        </div>
    </div>
<?php } ?>
<?php

/*
 * ----------------------------------------------------------
 * UPDATES BOX
 * ----------------------------------------------------------
 *
 * Display the updates area
 *
 */

function sb_updates_box() { ?>
    <div class="sb-lightbox sb-updates-box">
        <div class="sb-info"></div>
        <div class="sb-top-bar">
            <div><?php sb_e('Update center') ?></div>
            <div>
                <a class="sb-close sb-btn-icon">
                    <i class="sb-icon-close"></i>
                </a>
            </div>
        </div>
        <div class="sb-main">
            <div class="sb-bottom">
                <a class="sb-update sb-btn sb-icon">
                    <i class="sb-icon-reload"></i><?php sb_e('Update now') ?>
                </a>
                <a href="http://board.support/changes" target="_blank" class="sb-btn-text">
                    <i class="sb-icon-clock"></i><?php sb_e('Change Log') ?>
                </a>
            </div>
        </div>
    </div>
<?php } ?>
<?php
/*
 * ----------------------------------------------------------
 * SYSTEM REQUIREMENTS BOX
 * ----------------------------------------------------------
 *
 * Display the system requirements box
 *
 */

function sb_requirements_box() { ?>
    <div class="sb-lightbox sb-requirements-box">
        <div class="sb-info"></div>
        <div class="sb-top-bar">
            <div><?php sb_e('System requirements') ?></div>
            <div>
                <a class="sb-close sb-btn-icon">
                    <i class="sb-icon-close"></i>
                </a>
            </div>
        </div>
        <div class="sb-main"></div>
    </div>
<?php } ?>
<?php

/*
 * ----------------------------------------------------------
 * APP BOX
 * ----------------------------------------------------------
 *
 * Display the app box
 *
 */

function sb_app_box() { ?>
    <div class="sb-lightbox sb-app-box" data-app="">
        <div class="sb-info"></div>
        <div class="sb-top-bar">
            <div></div>
            <div>
                <a class="sb-close sb-btn-icon">
                    <i class="sb-icon-close"></i>
                </a>
            </div>
        </div>
        <div class="sb-main">
            <p></p>
            <div class="sb-title"><?php sb_e('License key') ?></div>
            <div class="sb-input-setting sb-type-text">
                <input type="text" required>
            </div>
            <div class="sb-bottom">
                <a class="sb-activate sb-btn sb-icon">
                    <i class="sb-icon-check"></i><?php sb_e('Activate') ?>
                </a>
                <a class="sb-btn sb-icon sb-btn-app-puchase" target="_blank" href="#">
                    <i class="sb-icon-plane"></i><?php sb_e('Purchase license') ?>
                </a>
                <a class="sb-btn-text sb-btn-app-details" target="_blank" href="#">
                    <i class="sb-icon-help"></i><?php sb_e('Read more') ?>
                </a>
            </div>
        </div>
    </div>
<?php } ?>
<?php

/*
 * ----------------------------------------------------------
 * INSTALLATION BOX
 * ----------------------------------------------------------
 *
 * Display the form to install Support Board
 *
 */

function sb_installation_box($error = false) {
    global $SB_LANGUAGE;
    $SB_LANGUAGE = isset($_GET['lang']) ? $_GET['lang'] : strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));

?>
    <div class="sb-main sb-admin sb-admin-start">
        <form class="sb-intall sb-admin-box">
            <?php echo $error === false || $error == 'installation' ? '<div class="sb-info"></div>' : '<div class="sb-info sb-active">' . sb_('We\'re having trouble connecting to your database. Please re-enter your database connection details below. Error: ') . $error . '.</div>'; ?>
            <div class="sb-top-bar">
                <img src="<?php echo (SB_URL == '' || SB_URL == '[url]' ? '' : SB_URL . '/') ?>media/logo.svg" />
                <div class="sb-title"><?php sb_e('Installation') ?></div>
                <div class="sb-text">
                    <?php sb_e('Please complete the installation process by entering your database connection details below. If you are not sure about this, contact your hosting provider for support.') ?>
                </div>
            </div>
            <div class="sb-main">
                <div id="db-name" class="sb-input">
                    <span><?php sb_e('Database Name') ?></span>
                    <input type="text" required />
                </div>
                <div id="db-user" class="sb-input">
                    <span><?php sb_e('Username') ?></span>
                    <input type="text" required />
                </div>
                <div id="db-password" class="sb-input">
                    <span><?php sb_e('Password') ?></span>
                    <input type="text" />
                </div>
                <div id="db-host" class="sb-input">
                    <span><?php sb_e('Host') ?></span>
                    <input type="text" required />
                </div>
                <div id="db-port" class="sb-input">
                    <span><?php sb_e('Port') ?></span>
                    <input type="text" placeholder="Default" />
                </div>
                <?php if ($error === false || $error == 'installation') { ?>
                    <div class="sb-text">
                        <?php sb_e('Enter the user details of the main account you will use to login into the administration area. You can update these details later.') ?>
                    </div>
                    <div id="first-name" class="sb-input">
                        <span><?php sb_e('First name') ?></span>
                        <input type="text" required />
                    </div>
                    <div id="last-name" class="sb-input">
                        <span><?php sb_e('Last name') ?></span>
                        <input type="text" required />
                    </div>
                    <div id="email" class="sb-input">
                        <span><?php sb_e('Email') ?></span>
                        <input type="email" required />
                    </div>
                    <div id="password" class="sb-input">
                        <span><?php sb_e('Password') ?></span>
                        <input type="password" required />
                    </div>
                    <div id="password-check" class="sb-input">
                        <span><?php sb_e('Repeat password') ?></span>
                        <input type="password" required />
                    </div>
                <?php } ?>
                <div class="sb-bottom">
                    <div class="sb-btn sb-submit-installation"><?php sb_e('Complete installation') ?></div>
                </div>
            </div>
        </form>
    </div>
<?php } ?>
<?php

/*
 * ----------------------------------------------------------
 * ADMIN AREA
 * ----------------------------------------------------------
 *
 * Display the administration area
 *
 */

function sb_component_admin() {
    $sb_settings = json_decode(file_get_contents(SB_PATH . '/resources/json/settings.json'), true);
    $active_user = sb_get_active_user();
    $apps = [
        ['SB_WP', 'wordpress', 'WordPress'], 
        ['SB_SLACK', 'slack', 'Slack', 'Communicate with your users right from Slack. Send and receive messages and attachments, use emojis, and much more.'], 
        ['SB_DIALOGFLOW', 'dialogflow', 'Dialogflow', 'Utilize an artificially intelligent chat bot to automatically answer your users\' questions and perform other complex tasks.'],
        ['SB_TICKETS', 'tickets', 'Tickets', 'Provide help desk support to your customers by including a tickets area, with all the chat features, into any web page.'],
        ['SB_WOOCOMMERCE', 'woocommerce', 'WooCommerce', 'Increase sales, provide better support, and faster solutions, by integrating WooCommerce with Support Board.'],
        ['SB_UMP', 'ump', 'Ultimate Membership Pro', 'Enable ticket and chat support for subscribers only, view member profile details and subscription details in the admin area.']
    ];
    $logged = $active_user != false && sb_is_agent($active_user['user_type']);
    $is_admin = $active_user['user_type'] == 'admin';
    if (sb_get_multi_setting('push-notifications', 'push-notifications-active') && sb_get_active_user()) {
        echo '<script src="https://js.pusher.com/beams/1.0/push-notifications-cdn.js"></script><script>window.navigator.serviceWorker.ready.then((serviceWorkerRegistration) => {
            const sb_beams_client = new PusherPushNotifications.Client({
                instanceId: "' . sb_get_multi_setting('push-notifications', 'push-notifications-id') . '",
                serviceWorkerRegistration: serviceWorkerRegistration,
            });
            sb_beams_client.start().then(() => sb_beams_client.setDeviceInterests(["' . sb_get_active_user()['id'] . '", "agents"])).catch(console.error);
        });</script>';
    }
?>
    <div class="sb-main <?php echo $logged ? 'sb-admin' : 'sb-admin-start' ?>" style="opacity: 0">
        <?php if ($logged) { ?>
        <div class="sb-header">
            <div class="sb-admin-nav">
                <img src="<?php echo SB_URL ?>/media/icon.svg" />
                <div>
                    <a id="sb-messages" class="sb-active">
                        <span>
                            <?php sb_e('Messages') ?>
                        </span>
                    </a>
                    <?php 
                  if ($is_admin || sb_get_setting('admin-agents-users-area')) echo '<a id="sb-users"><span>' . sb_('Users') . '</span></a>';
                  if ($is_admin) echo '<a id="sb-settings"><span>' . sb_('Settings') . '</span></a>' 
                    ?>
                </div>
            </div>
            <div class="sb-admin-nav-right sb-menu-mobile">
                <i class="sb-icon-menu"></i>
                <div class="sb-desktop">
                    <div class="sb-account">
                        <img src="<?php echo SB_URL ?>/media/user.svg" />
                        <div>
                            <a class="sb-profile">
                                <img src="<?php echo SB_URL ?>/media/user.svg" />
                                <span class="sb-name"></span>
                            </a>
                            <ul class="sb-menu">
                                <li data-value="status" class="sb-online">
                                    <?php sb_e('Online') ?>
                                </li>
                                <?php if ($is_admin) echo '<li data-value="edit-profile">' . sb_('Edit profile') . '</li>' ?>
                                <li data-value="logout">
                                    <?php sb_e('Logout') ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <?php if ($is_admin) echo '<a href="http://board.support/docs/" target="_blank" class="sb-docs"><i class="sb-icon-help"></i></a><a href="#" class="sb-version">' . SB_VERSION . '</a>' ?> 
                </div>
                <div class="sb-mobile">
                    <?php if ($is_admin) echo '<a href="#" class="edit-profile">' . sb_('Edit profile') . '</a><a href="#" class="sb-docs">' . sb_('Docs') . '</a><a href="#" class="sb-version">' . sb_('Updates') . '</a>' ?>
                    <a href="#" class="logout">
                        <?php sb_e('Logout') ?>
                    </a>
                </div>
            </div>
        </div>
        <main>
            <div class="sb-active sb-area-conversations">
                <div class="sb-board">
                    <div class="sb-admin-list">
                        <div class="sb-top">
                            <div class="sb-select">
                                <p>
                                    <?php sb_e('Inbox') ?>
                                    <span></span>
                                </p>
                                <ul>
                                    <li data-value="0" class="sb-active">
                                        <?php sb_e('Inbox') ?>
                                        <span></span>
                                    </li>
                                    <li data-value="3">
                                        <?php sb_e('Archive') ?>
                                    </li>
                                    <li data-value="4">
                                        <?php sb_e('Trash') ?>
                                    </li>
                                </ul>
                            </div>
                            <div class="sb-search-btn">
                                <i class="sb-icon sb-icon-search"></i>
                                <input type="text" autocomplete="false" placeholder="<?php sb_e('Search for keywords or users...') ?>" />
                            </div>
                        </div>
                        <div class="sb-scroll-area">
                            <ul></ul>
                        </div>
                    </div>
                    <div class="sb-conversation">
                        <div class="sb-top">
                            <i class="sb-btn-back sb-icon-arrow-left"></i>
                            <a></a>
                            <div class="sb-labels"></div>
                            <div class="sb-menu-mobile">
                                <i class="sb-icon-menu"></i>
                                <ul>
                                    <li>
                                        <a data-value="archive" class="sb-btn-icon" data-sb-tooltip="<?php sb_e('Archive conversation') ?>">
                                            <i class="sb-icon-check"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a data-value="read" class="sb-btn-icon" data-sb-tooltip="<?php sb_e('Mark as read') ?>">
                                            <i class="sb-icon-check-circle"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a data-value="csv" class="sb-btn-icon" data-sb-tooltip="<?php sb_e('Download CSV') ?>">
                                            <i class="sb-icon-file"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a data-value="inbox" class="sb-btn-icon" data-sb-tooltip="<?php sb_e('Send to inbox') ?>">
                                            <i class="sb-icon-back"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a data-value="delete" class="sb-btn-icon sb-btn-red" data-sb-tooltip="<?php sb_e('Delete conversation') ?>">
                                            <i class="sb-icon-delete"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a data-value="empty-trash" class="sb-btn-icon sb-btn-red" data-sb-tooltip="<?php sb_e('Empty trash') ?>">
                                            <i class="sb-icon-delete"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="sb-list"></div>
                        <?php sb_component_editor(true); ?>
                        <div class="sb-no-conversation-message">
                            <div>
                                <label>
                                    <?php sb_e('Select a conversation or start a new one') ?>
                                </label>
                                <p>
                                    <?php sb_e('Select a conversation from the left menu or start a new conversation from the users area.') ?>
                                </p>
                            </div>
                        </div>
                        <audio id="sb-audio" preload="auto">
                            <source src="<?php echo SB_URL ?>/media/sound.mp3" type="audio/mpeg">
                        </audio>
                        <audio id="sb-audio-out" preload="auto">
                            <source src="<?php echo SB_URL ?>/media/sound-out.mp3" type="audio/mpeg">
                        </audio>
                    </div>
                    <div class="sb-user-details">
                        <div class="sb-top">
                            <?php sb_e('Details') ?>
                        </div>
                        <div class="sb-scroll-area">
                            <div class="sb-profile">
                                <img src="<?php echo SB_URL ?>/media/user.svg" />
                                <span class="sb-name"></span>
                            </div>
                            <div class="sb-profile-list sb-profile-list-conversation<?php if (sb_get_setting('collapse')) echo ' sb-collapse' ?>"></div>
                            <?php if (defined('SB_UMP')) echo '<div class="sb-panel-details sb-panel-ump' . (sb_get_setting('collapse') ? ' sb-collapse' : '') . '"></div>' ?>
                            <?php if (defined('SB_WOOCOMMERCE')) echo '<div class="sb-panel-details sb-panel-woocommerce' . (sb_get_setting('collapse') ? ' sb-collapse' : '') . '"></div>' ?>
                            <?php echo sb_departments('custom-select') ?>
                            <h3>
                                <?php sb_e('User conversations') ?>
                            </h3>
                            <ul class="sb-user-conversations"></ul>
                        </div>
                        <div class="sb-no-conversation-message"></div>
                    </div>
                </div>
            </div>
            <div class="sb-area-users">
                <div class="sb-top-bar">
                    <div>
                        <h2>
                            <?php sb_e('Users list') ?>
                        </h2>
                        <div class="sb-menu-wide sb-menu-users">
                            <div>
                                <?php sb_e('All') ?>
                                <span data-count="0">(0)</span>
                            </div>
                            <ul>
                                <li data-type="all" class="sb-active">
                                    <?php sb_e('All') ?>
                                    <span data-count="0">(0)</span>
                                </li>
                                <li data-type="user">
                                    <?php sb_e('Users') ?>
                                    <span data-count="0">(0)</span>
                                </li>
                                <li data-type="lead">
                                    <?php sb_e('Leads') ?>
                                    <span data-count="0">(0)</span>
                                </li>
                                <li data-type="visitor">
                                    <?php sb_e('Visitors') ?>
                                    <span data-count="0">(0)</span>
                                </li>
                                <li data-type="online">
                                    <?php sb_e('Online') ?>
                                </li>
                                <?php if ($is_admin) { echo '<li data-type="agent">' . sb_('Agents & Admins') . '</li>'; } ?>
                            </ul>
                        </div>
                        <div class="sb-menu-buttons">
                            <a data-value="csv" class="sb-btn-icon" data-sb-tooltip="Download CSV">
                                <i class="sb-icon-file"></i>
                            </a>
                            <a data-value="delete" class="sb-btn-icon" data-sb-tooltip="Delete users" style="display: none;">
                                <i class="sb-icon-delete"></i>
                            </a>
                        </div>
                    </div>
                    <div>
                        <div class="sb-search-btn">
                            <i class="sb-icon sb-icon-search"></i>
                            <input type="text" autocomplete="false" placeholder="<?php sb_e('Search users ...') ?>" />
                        </div>
                        <a class="sb-btn sb-icon sb-new-user">
                            <i class="sb-icon-user"></i><?php sb_e('Add new user') ?>
                        </a>
                    </div>
                </div>
                <div class="sb-scroll-area">
                    <table class="sb-table sb-table-users">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" />
                                </th>
                                <th data-field="first_name">
                                    <?php sb_e('Full name') ?>
                                </th>
                                <th data-field="email">
                                    <?php sb_e('Email') ?>
                                </th>
                                <th data-field="user_type">
                                    <?php sb_e('Type') ?>
                                </th>
                                <th data-field="last_activity">
                                    <?php sb_e('Last activity') ?>
                                </th>
                                <th data-field="creation_time" class="sb-active">
                                    <?php sb_e('Registration date') ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="sb-loading sb-loading-table"></div>
            </div>
            <?php if ($is_admin) { ?>
                <div class="sb-area-settings">
                    <div class="sb-top-bar">
                        <div>
                            <h2>
                                <?php sb_e('Settings') ?>
                            </h2> 
                        </div>
                        <div>
                            <a class="sb-btn sb-save-changes sb-icon">
                                <i class="sb-icon-check"></i><?php sb_e('Save changes') ?>
                            </a>
                        </div>
                    </div>
                    <div class="sb-tab">
                        <div class="sb-nav">
                            <div><?php sb_e('Settings') ?></div>
                            <ul>
                                <li id="tab-chat" class="sb-active">
                                    <?php sb_e('Chat') ?>
                                </li>
                                  <li id="tab-admin">
                                    <?php sb_e('Admin') ?>
                                </li>
                                <li id="tab-notifications">
                                    <?php sb_e('Notifications') ?>
                                </li>
                                <li id="tab-users">
                                    <?php sb_e('Users') ?>
                                </li>
                                <li id="tab-design">
                                    <?php sb_e('Design') ?>
                                </li>
                                <li id="tab-various">
                                    <?php sb_e('Miscellaneous') ?>
                                </li>
                                <?php for ($i = 0; $i < count($apps); $i++) { if (defined($apps[$i][0])) echo '<li id="tab-' . $apps[$i][1] . '">' . $apps[$i][2] . '</li>'; } ?>
                                <li id="tab-apps">
                                    <?php sb_e('Apps') ?>
                                </li>
                                <li id="tab-articles">
                                    <?php sb_e('Articles') ?>
                                </li>
                                <li id="tab-translations">
                                    <?php sb_e('Translations') ?>
                                </li>
                            </ul>
                        </div>
                        <div class="sb-content sb-scroll-area">
                            <div class="sb-active">
                                <?php sb_populate_settings('chat', $sb_settings) ?>
                            </div>
                             <div>
                                <?php sb_populate_settings('admin', $sb_settings) ?>
                            </div>
                            <div>
                                <?php sb_populate_settings('notifications', $sb_settings) ?>
                            </div>
                            <div>
                                <?php sb_populate_settings('users', $sb_settings) ?>
                            </div>
                            <div>
                                <?php sb_populate_settings('design', $sb_settings) ?>
                            </div>
                            <div>
                                <?php sb_populate_settings('miscellaneous', $sb_settings) ?>
                            </div>
                            <?php sb_apps_area($apps) ?>
                            <div>
                                <div class="sb-articles-area sb-tab">
                                    <div class="sb-nav">
                                        <div><?php sb_e('Articles') ?></div>
                                        <ul></ul>
                                    </div>
                                    <div class="sb-content" data-article-id="-1">
                                        <h2>
                                            <?php sb_e('Article title') ?>
                                            <a class="sb-btn sb-icon sb-add-article">
                                                <i class="sb-icon-plus"></i><?php sb_e('Add new article') ?>
                                            </a>
                                        </h2>
                                        <div class="sb-input-setting sb-type-text sb-article-title">
                                            <div>
                                                <input type="text" />
                                            </div>
                                        </div>
                                        <h2>
                                            <?php sb_e('Content') ?>
                                        </h2>
                                        <div class="sb-input-setting sb-type-textarea sb-article-content">
                                            <div>
                                                <textarea></textarea>
                                            </div>
                                        </div>
                                        <h2>
                                            <?php sb_e('External link') ?>
                                        </h2>
                                        <div class="sb-input-setting sb-type-text sb-article-link">
                                            <div>
                                                <input type="text" />
                                            </div>
                                        </div>
                                        <h2 id="sb-article-id"></h2>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="sb-translations sb-tab"></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </main>
        <?php

                  sb_profile_box();
                  sb_profile_edit_box();
                  sb_dialog();
                  sb_dialogflow_intent_box();
                  sb_updates_box();
                  sb_requirements_box();
                  sb_app_box();

        ?>
        <form class="sb-upload-form-admin sb-upload-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
            <input type="file" name="files[]" class="sb-upload-files" multiple />
        </form>
        <div class="sb-info-card"></div>
        <?php } else { sb_login_box(); } ?>
        <div class="sb-lightbox sb-lightbox-media">
            <div></div>
            <i class="sb-icon-close"></i>
        </div>
        <div class="sb-lightbox-overlay"></div>
        <div class="sb-loading-global sb-loading sb-lightbox"></div>
        <input type="email" name="email" style="display:none" autocomplete="email" />
        <input type="password" name="hidden" style="display:none" autocomplete="new-password" />
    </div>
<?php } ?>
<?php

/*
 * ----------------------------------------------------------
 * DIALOGFLOW LANGUAGES LIST
 * ----------------------------------------------------------
 *
 * Return the Dialogflow languages list
 *
 */

function sb_dialogflow_languages_list() {
    if (sb_get_setting('dialogflow-active')) {
        $languages = [['', sb_('Default'), 'pt-BR', 'Brazilian Portuguese'], ['zh-HK', 'Chinese (Cantonese)'], ['zh-CN', 'Chinese (Simplified)'], ['zh-TW', 'Chinese (Traditional)'], ['da', 'Danish'], ['hi', 'Hindi'], ['id', 'Indonesian'], ['no', 'Norwegian'], ['pl', 'Polish'], ['sv', 'Swedish'], ['th', 'Thai'], ['tr', 'Turkish'], ['en', 'English'], ['nl', 'Dutch'], ['fr', 'French'], ['de', 'German'], ['it', 'Italian'], ['ja', 'Japanese'], ['ko', 'Korean'], ['pt', 'Portuguese'], ['ru', 'Russian'], ['es', 'Spanish'], ['uk', 'Ukranian']];
        $code = '<div data-type="select" class="sb-input-setting sb-type-select sb-dialogflow-languages"><div class="input"><select>';
        for ($i = 0; $i < count($languages); $i++) {
            $code .= '<option value="' . $languages[$i][0] . '">' . $languages[$i][1] . '</option>';
        }
        return $code . '</select></div></div>';
    }
    return '';
}

/*
 * ----------------------------------------------------------
 * DEPARTMENTS LIST
 * ----------------------------------------------------------
 *
 * Return the departments list save in Settings > Miscellaneous
 *
 */

function sb_departments($type) {
    $items = sb_get_setting('departments');
    $count = is_array($items) ? count($items) : 0;
    if ($count) {
        switch ($type) {
            case 'select':
                $code = '<div id="department" data-type="select" class="sb-input sb-input-select"><span>' . sb_('Department') . '</span><select><option value=""></option>';
                for ($i = 0; $i < $count; $i++) {
                    $code .= '<option value="' . $items[$i]['department-id'] . '">' . ucfirst(sb_($items[$i]['department-name'])) . '</option>';
                }
                return $code . '</select></div>';
            case 'custom-select':
                $code = '<div class="sb-inline sb-inline-departments"><h3>' . sb_('Department') . '</h3><div id="conversation-department" class="sb-select sb-select-colors"><p>' . sb_('General') . '</p><ul><li data-id="-1" data-value="-1">' . sb_('General') . '</li>';
                for ($i = 0; $i < $count; $i++) {
                    $code .= '<li data-id="' . $items[$i]['department-id'] . '" data-value="' . (isset($items[$i]['department-color']) ? $items[$i]['department-color'] : '') . '">' . ucfirst(sb_($items[$i]['department-name'])) . '</li>';
                }
                return $code . '</ul></div></div>';
            case 'dashboard':
                $settings = sb_get_setting('departments-settings');
                if ($settings != false) {
                    $is_image = sb_isset($settings, 'departments-images') && sb_isset($items[0],'department-image') != '';
                    $code = '<div class="sb-dashboard-departments"><div class="sb-title">' .  sb_(sb_isset($settings, 'departments-title', 'Departments')) . '</div><div class="sb-departments-list">';
                    for ($i = 0; $i < $count; $i++) {
                        $code .= '<div data-id="' . $items[$i]['department-id'] . '">' . ($is_image ? '<img src="' . $items[$i]['department-image'] . '">' : '<div data-color="' . sb_isset($items[$i], 'department-color') . '"></div>') . '<span>' . $items[$i]['department-name'] . '</span></div>';
                    }
                    return $code . '</div></div>';
                }
        }
    }
    return '';
}

/*
 * ----------------------------------------------------------
 * APPS AREA
 * ----------------------------------------------------------
 *
 * Print the apps settings and apps area
 *
 */

function sb_apps_area($apps) {
    $apps_wp = ['SB_WP', 'SB_WOOCOMMERCE', 'SB_UMP'];
    $wp = defined('SB_WP');
    $code = '';
    for ($i = 0; $i < count($apps); $i++) {
        if (defined($apps[$i][0])) {
            $code .= '<div>' . sb_populate_app_settings($apps[$i][1]) . '</div>';
        }
    }
    $code .= '<div><div class="sb-apps">';
    for ($i = 1; $i < count($apps); $i++) { 
        if ($wp || !in_array($apps[$i][0], $apps_wp)) {
            $code .= '<div data-app="' . $apps[$i][1] . '">' . (defined($apps[$i][0]) ? '<i class="sb-icon-check"></i>' : '' ) . ' <img src="' . SB_URL . '/media/apps/' . $apps[$i][1] . '.svg" /><h2>' . $apps[$i][2] . '</h2><p>' . sb_($apps[$i][3]) . '</p></div>';
        }
    }
    echo $code . '</div></div>';
}

?>