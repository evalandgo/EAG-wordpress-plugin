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
            return wp_redirect(home_url());
        }
//    if(!wp_validate_auth_cookie()) {
//        return wp_redirect(wp_login_url($redirect_uri).'?eag-wp-t=');
//    }

        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            $token = self::create_signature($user);
        }
        else {
            $token = '';
        }

        return wp_redirect($redirect_uri . '?eag-wp-t=' . $token);
    }

    private static function plugin_configured(): bool
    {
        $settings = get_option('eag_wordpress_settings', []);
        return !empty($settings['eag_api_key']) && !empty($settings['eag_host_private_key']);
    }

    private static function create_signature(WP_User $user): string
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


//function eag_nonce_function(WP_REST_Request $request) {
//    return wp_create_nonce('eag_auth_function');
//}
}