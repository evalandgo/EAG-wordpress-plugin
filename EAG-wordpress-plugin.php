<?php

use src\Controller\AuthController;
use src\Controller\SettingsController;
use src\View\SettingsView;

require __DIR__ . '/vendor/autoload.php';

/**
 * Plugin Name: Eval&GO Wordpress
 * Plugin URI: https://www.evalandgo.com
 * Description: This plugin validates user authentication for users accessing a restricted form on Eval&GO.
 * Version: 1.0
 * Author: Eval&GO
 * Author URI: https://www.evalandgo.com
 **/

register_activation_hook(__FILE__, 'eag_activate');
register_deactivation_hook(__FILE__, 'eag_deactivation_hook');
add_action('init', 'initialize_plugin');

function initialize_plugin(): void
{
    $settingsController = new SettingsController(new SettingsView());
    $authController = new AuthController();
    add_rewrite_rule('^eag/v1/auth$', 'index.php?eag_auth=1', 'top');
    add_filter('query_vars', [$authController, 'eag_add_query_vars']);
    add_action('template_redirect', [$authController, 'eag_check_route']);
}

function eag_activate(): void
{
    flush_rewrite_rules();
    wp_safe_redirect(admin_url('options-general.php?page=eag-wordpress'));
}
function eag_deactivation_hook() {
    flush_rewrite_rules();
}


