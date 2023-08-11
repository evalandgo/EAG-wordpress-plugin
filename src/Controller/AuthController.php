<?php

namespace src\Controller;

use src\Service\Auth\AuthService;

class AuthController
{
    public function eag_add_query_vars($vars) {
        $vars[] = 'eag_auth';

        return $vars;
    }

    public function eag_check_route(): void
    {
        global $wp_query;
        error_log(isset($wp_query->query_vars['eag_auth']) ? 'eag_auth' : 'not eag_auth');

        if (isset($wp_query->query_vars['eag_auth'])) {
            AuthService::eag_auth_function();
            exit;
        }
    }

    function generate_token() {
        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            $token = AuthService::create_signature($user);
            return 'eag-wp-t='.$token;
        }
        return '';
    }
}