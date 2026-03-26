<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('template_redirect', function () {
    // Só executa na rota /jwt-login
    if (!get_query_var('jwt_login')) {
        return;
    }

    if (!isset($_GET['token'])) {
        wp_die('Token não informado');
    }

    require_once ABSPATH . 'vendor/autoload.php';

    $secret = get_option('login_cert_jwt_secret', '');

    if (empty($secret)) {
        wp_die('Chave secreta não configurada');
    }

    try {
        $decoded = \Firebase\JWT\JWT::decode(
            $_GET['token'],
            new \Firebase\JWT\Key($secret, 'HS256')
        );

        // Validações extras
        $expected_iss = get_option('login_cert_iss', '');
        if (!empty($expected_iss) && $decoded->iss !== $expected_iss) {
            wp_die('Issuer inválido');
        }

        $expected_aud = get_option('login_cert_aud', '');
        if (!empty($expected_aud) && $decoded->aud !== $expected_aud) {
            wp_die('Audience inválido');
        }

        $ip_mode = get_option('login_cert_ip_mode', 'server');
        if ($ip_mode === 'server') {
            if ($decoded->ip !== getServerIP()) {
                wp_die('Endereço de IP inválido');
            }
        } elseif ($ip_mode === 'custom') {
            $custom_ip = get_option('login_cert_custom_ip', '');
            if (!empty($custom_ip) && $decoded->ip !== $custom_ip) {
                wp_die('Endereço de IP inválido');
            }
        }

        $user = get_user_by('id', $decoded->uid);

        if (!$user) {
            wp_die('Usuário não encontrado');
        }

        // LOGIN
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);
        do_action('wp_login', $user->user_login, $user);

        // Redirect seguro
        $redirect = $_GET['redirect_to'] ?? home_url();
        wp_safe_redirect($redirect);
        exit;

    } catch (Exception $e) {
        wp_die('Token inválido ou expirado');
    }
});
