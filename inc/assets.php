<?php

if (!defined('ABSPATH')) exit;

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook === 'settings_page_login-certificado') {
        wp_enqueue_style(
            'lc-settings',
            plugin_dir_url(__FILE__) . '../assets/css/settings.css',
            [],
            '1.2.1'
        );
    }

    if ($hook !== 'user-edit.php' && $hook !== 'profile.php') {
        return;
    }

    wp_enqueue_script(
        'lc-cnpj-check',
        plugin_dir_url(__FILE__) . '../assets/js/cnpj-check.js',
        ['jquery'],
        '1.2.1',
        true
    );

    wp_localize_script('lc-cnpj-check', 'cnpjAjax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'user_id' => isset($_GET['user_id']) ? $_GET['user_id'] : get_current_user_id()
    ]);
});