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
            self::log_plugin_error(sprintf('An attempt to authenticate a user through the Eval&GO plugin was made, but the plugin was not configured. Redirect URI: %s. Please contact Eval&GO support.', $redirect_uri ?? 'null'));

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
            'exp' => time() + 3600
        ];
        $settings = get_option('eag_wordpress_settings');
        $private_key = $settings['eag_host_private_key'];

        $data = self::handleTokenData($data, $user);

        return JWT::encode($data, $private_key, 'RS256');
    }

    public static function log_plugin_error($error_message): void
    {
        update_option('eag-plugin_last_error', $error_message);
    }

    private static function handleTokenData(array $data, WP_User $user): array
    {
        if(get_option('eag_wordpress_settings')['eag_send_email'] === '1') {
            $data['m'] = $user->user_email;
        }

        if(get_option('eag_wordpress_settings')['eag_send_identities'] === '1') {
            $data['f'] = $user->first_name;
            $data['l'] = $user->last_name;
        }

        return $data;
    }
}