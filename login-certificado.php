<?php
/**
 * Plugin Name: Login por Certificado (CNPJ)
 * Description: Autenticação automática via certificado digital (CNPJ).
 * Version: 1.1.1
 * Author: Misteregis
 * Author URI:  https://github.com/misteregis/
 */

if (!defined('ABSPATH')) {
    exit;
}

// Includes
require_once plugin_dir_path(__FILE__) . 'inc/setup.php';
require_once plugin_dir_path(__FILE__) . 'inc/settings.php';
require_once plugin_dir_path(__FILE__) . 'inc/auth.php';
require_once plugin_dir_path(__FILE__) . 'inc/helpers.php';
require_once plugin_dir_path(__FILE__) . 'inc/user-meta.php';
require_once plugin_dir_path(__FILE__) . 'inc/ajax.php';
require_once plugin_dir_path(__FILE__) . 'inc/assets.php';
