<?php

if (!defined('ABSPATH')) {
    exit;
}

// Adiciona query var
add_filter('query_vars', function ($vars) {
    $vars[] = 'jwt_login';
    return $vars;
});

// Registra rewrite
add_action('init', function () {
    add_rewrite_rule('^jwt-login/?$', 'index.php?jwt_login=1', 'top');
});

// Flush controlado
add_action('init', function () {
    if (get_option('login_cert_rewrite_version') !== '1.1.0') {
        flush_rewrite_rules();
        update_option('login_cert_rewrite_version', '1.1.0');
    }
});
