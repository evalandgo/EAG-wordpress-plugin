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
        // register the settings
        register_setting('eag_wordpress', 'eag_wordpress_settings', ['sanitize_callback' => [$this, 'eag_wordpress_settings_validate_and_sanitize']]);

        // register the section and fields
        add_settings_section('eag_wordpress_section_api_key', 'Eval&GO API key', [$this->settingsView, 'eag_wordpress_section_api_key_callback'], 'eag_wordpress');
        add_settings_field('eag_api_key', 'API Key', [$this->settingsView, 'eag_api_key_field_callback'], 'eag_wordpress', 'eag_wordpress_section_api_key');

        add_settings_section('eag_wordpress_section_host_keys', 'Encryption key', [$this->settingsView, 'eag_wordpress_section_host_keys_callback'], 'eag_wordpress');
        add_settings_field('eag_host_private_key', 'Your Private Key', [$this->settingsView, 'eag_private_key_field_callback'], 'eag_wordpress', 'eag_wordpress_section_host_keys');
    }

    /**
     * @throws \JsonException
     */
    public function eag_wordpress_settings_validate_and_sanitize($input)
    {
        $errors = [];

        // Check the private key
        if (!isset($input['eag_host_private_key']) || empty($input['eag_host_private_key'])) {
            $errors[] = 'Private Key is required.';
        }
        // Check the API key
        if (!isset($input['eag_api_key']) || empty($input['eag_api_key'])) {
            $errors[] = 'API Key is required.';
        }


        if (!empty($errors)) {
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

        // If there are no errors, sanitize and return the input as usual
        $input['eag_api_key'] = sanitize_text_field($input['eag_api_key']);

        $api = new API($input['eag_api_key']);
        $processor = new SettingsProcessor($api);
        $errors = $processor->handle_settings_update($errors, $input);

        if(!empty($errors)) {
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

        return $input;
    }

}