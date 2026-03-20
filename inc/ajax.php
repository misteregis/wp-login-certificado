<?php

if (!defined('ABSPATH')) exit;

add_action('wp_ajax_check_cnpj', function () {

    if (!current_user_can('edit_users')) {
        wp_send_json_error('Sem permissão');
    }

    $cnpj = isset($_POST['cnpj']) ? preg_replace('/\D/', '', $_POST['cnpj']) : '';

    if (empty($cnpj)) {
        wp_send_json_success(['exists' => false]);
    }

    $users = get_users([
        'meta_query' => [
            [
                'key' => 'cnpj',
                'value' => $cnpj,
                'compare' => '='
            ]
        ],
        'fields' => 'ids'
    ]);

    $user_id = $users[0] ?? null;

    wp_send_json_success([
        'exists' => !empty($users),
        'user' => [
            'id' => $user_id,
            'login' => $user_id ? get_userdata($user_id)->user_login : null,
            'link' => $user_id ? get_edit_user_link($user_id) : null,
        ]
    ]);
});