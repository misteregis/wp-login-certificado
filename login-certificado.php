<?php
/**
 * Plugin Name: Login por Certificado (CNPJ)
 * Description: Autenticação automática via certificado digital (CNPJ).
 * Version: 1.2.1
 * Author: Misteregis
 * Author URI:  https://github.com/misteregis/
 */

if (!defined('ABSPATH')) {
    exit;
}

// Flush rewrite rules on activation/deactivation
register_activation_hook(__FILE__, function () {
    add_rewrite_rule('^jwt-login/?$', 'index.php?jwt_login=1', 'top');
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules();
});

// Includes
require_once plugin_dir_path(__FILE__) . 'inc/setup.php';
require_once plugin_dir_path(__FILE__) . 'inc/settings.php';
require_once plugin_dir_path(__FILE__) . 'inc/auth.php';
require_once plugin_dir_path(__FILE__) . 'inc/helpers.php';
require_once plugin_dir_path(__FILE__) . 'inc/user-meta.php';
require_once plugin_dir_path(__FILE__) . 'inc/ajax.php';
require_once plugin_dir_path(__FILE__) . 'inc/assets.php';
