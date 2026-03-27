<?php

if (!defined('ABSPATH')) {
    exit;
}

// Adiciona query var
add_filter('query_vars', function ($vars) {
    $vars[] = 'jwt_login';
    return $vars;
});

// Registra rewrite + flush se a regra não existir no banco
add_action('init', function () {
    add_rewrite_rule('^jwt-login/?$', 'index.php?jwt_login=1', 'top');

    $rules = get_option('rewrite_rules');
    if (!isset($rules['^jwt-login/?$'])) {
        flush_rewrite_rules();
    }
});
