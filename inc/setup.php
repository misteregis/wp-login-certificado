<?php

if (!defined('ABSPATH')) {
    exit;
}

// Adiciona query var
add_filter('query_vars', function ($vars) {
    $vars[] = 'login_cert';
    return $vars;
});

// Registra rewrite
add_action('init', function () {
    add_rewrite_rule('^login-certificado/?$', 'index.php?login_cert=1', 'top');
});

// Flush controlado (executa só 1x por versão)
add_action('init', function () {
    if (get_option('login_cert_rewrite_version') !== '1.0.0') {
        flush_rewrite_rules();
        update_option('login_cert_rewrite_version', '1.0.0');
    }
});