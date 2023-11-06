<?php

namespace src\Controller;

use src\Service\API\API;
use src\Service\Settings\SettingsProcessor;
use src\View\SettingsView;

class SettingsController
{
    public function __construct(private SettingsView $settingsView)
    {
        add_action('admin_menu', [$this, 'eag_menu']);
        register_activation_hook(__FILE__, [$this, 'eag_activate']);
        register_deactivation_hook(__FILE__, [$this, 'eag_deactivate']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_notices', [$this, 'display_plugin_error_notice']);
    }

    function display_plugin_error_notice(): void
    {
        $error_message = get_option('eag-plugin_last_error', '');

        if (!empty($error_message)) {
            $this->settingsView->display_last_error_message($error_message);

            //remove the notice
            delete_option('eag-plugin_last_error');
        }
    }
    public function eag_menu(): void
    {
        add_options_page(
            'Eval&GO Settings',
            'Eval&GO Wordpress Plugin',
            'manage_options',
            'eag-wordpress',
            [$this->settingsView, 'eag_settings_page']
        );
    }

    public function register_settings(): void
    {
        // registers the settings
        register_setting('eag_wordpress', 'eag_wordpress_settings', ['sanitize_callback' => [$this, 'eag_wordpress_settings_validate_and_sanitize']]);

        // registers the sections and fields
        add_settings_section('eag_wordpress_section_api_key', 'Eval&GO API key', [$this->settingsView, 'eag_wordpress_section_api_key_callback'], 'eag_wordpress');
        add_settings_field('eag_api_key', 'API Key', [$this->settingsView, 'eag_api_key_field_callback'], 'eag_wordpress', 'eag_wordpress_section_api_key');

        add_settings_section('eag_wordpress_section_host_keys', 'Encryption key', [$this->settingsView, 'eag_wordpress_section_host_keys_callback'], 'eag_wordpress');
        add_settings_field('eag_host_private_key', 'Your Private Key', [$this->settingsView, 'eag_private_key_field_callback'], 'eag_wordpress', 'eag_wordpress_section_host_keys');

        add_settings_section('eag_wordpress_section_connection', 'Connection between the form and your users', [$this->settingsView, 'eag_wordpress_section_connection_callback'], 'eag_wordpress');
        add_settings_field('eag_send_email', 'Connect users\' email with their responses ?', [$this->settingsView, 'eag_send_email_field_callback'], 'eag_wordpress', 'eag_wordpress_section_connection');
        add_settings_field('eag_send_identities', 'Connect users\' identities with their responses ?', [$this->settingsView, 'eag_send_identities_field_callback'], 'eag_wordpress', 'eag_wordpress_section_connection');
    }

    /**
     * @throws \JsonException
     */
    public function eag_wordpress_settings_validate_and_sanitize($input)
    {
        $errors = [];

        // Check the private key
        if (empty($input['eag_host_private_key'])) {
            $errors[] = 'Private Key is required.';
        }
        // Check the API key
        if (empty($input['eag_api_key'])) {
            $errors[] = 'API Key is required.';
        }


        if (!empty($errors)) {
            return $this->handleErrors($errors);
        }

        // If there are no errors, sanitize and return the API Key (not the private key to avoid any sanitization issues)
        $input['eag_api_key'] = sanitize_text_field($input['eag_api_key']);

        $api = new API($input['eag_api_key']);
        $processor = new SettingsProcessor($api);
        $errors = $processor->handle_settings_update($errors, $input);

        if(!empty($errors)) {
            return $this->handleErrors($errors);
        }

        return $input;
    }

    private function handleErrors($errors)
    {
        global $wp_settings_errors;
        foreach ($errors as $error) {
            $wp_settings_errors[] = [
                'type' => 'error',
                'message' => $error,
                'setting' => 'eag_wordpress_settings',
                'code' => 'validation_failed'
            ];
        }
        return get_option('eag_wordpress_settings');
    }

}