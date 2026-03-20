<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('template_redirect', function () {

    // Só executa na rota /login-certificado
    if (!get_query_var('login_cert')) {
        return;
    }

    if (empty($_SERVER['HTTP_X_CLIENT_SUBJECT'])) {
        wp_die('Certificado não encontrado');
    }

    $subject = $_SERVER['HTTP_X_CLIENT_SUBJECT'];
    $cnpj = extractCNPJ($subject);

    if (!$cnpj) {
        wp_die('CNPJ não encontrado no certificado.');
    }

    $users = get_users([
        'meta_key'   => 'cnpj',
        'meta_value' => $cnpj,
        'number'     => 1
    ]);

    $user = $users[0] ?? null;

    if ($user) {
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);

        /**
         * Dispara evento padrão de login
         */
        do_action('wp_login', $user->user_login, $user);

        wp_safe_redirect(home_url());
        exit;
    }

    wp_die("Nenhum usuário encontrado para o CNPJ: {$cnpj}");
});