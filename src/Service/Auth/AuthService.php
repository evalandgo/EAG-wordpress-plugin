<?php

namespace src\Service\Auth;

use Firebase\JWT\JWT;
use WP_User;
use function \get_option;

class AuthService
{

    public static function eag_auth_function(): bool
    {
//    if (!wp_verify_nonce($request->get_query_params()['_wpnonce'], 'eag_auth_function')) {
//        die('Invalid request.');
//    }
        $redirect_uri = $_GET['_redirect_uri'] ?? null;

        if(!$redirect_uri || !self::plugin_configured()) {
            //throw an error on the admin page
            self::log_plugin_error(sprintf('An attempt to authenticate a user through the EAG plugin was made, but the plugin is not configured. Redirect URI: %s. If you are seeing this error, please contact support.', $redirect_uri));

            return wp_redirect(home_url());
        }

        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            $token = self::create_signature($user);
        }
        else {
            $token = '';
        }

        header('X-EAG-WP-AUTH: ' . $token);
        if (str_contains($redirect_uri, '?')) {
            header(sprintf('Location: %s&eag-wp-t=%s', $redirect_uri, $token));
        }
        else {
            header(sprintf('Location: %s?eag-wp-t=%s', $redirect_uri, $token));
        }

        return true;
    }

    private static function plugin_configured(): bool
    {
        $settings = get_option('eag_wordpress_settings', []);
        return !empty($settings['eag_api_key']) && !empty($settings['eag_host_private_key']);
    }

    public static function create_signature(WP_User $user): string
    {
        $data = [
            'exp' => time() + 3600,
            'u' => $user->ID,
        ];
        $settings = get_option('eag_wordpress_settings');
        $private_key = $settings['eag_host_private_key'];

        // if there are data to be added to the token - as defined in the settings - add them
        // if() {}

        return JWT::encode($data, $private_key, 'RS256');
    }

    public static function log_plugin_error($error_message): void
    {
        update_option('eag-plugin_last_error', $error_message);
    }

//function eag_nonce_function(WP_REST_Request $request) {
//    return wp_create_nonce('eag_auth_function');
//}
}