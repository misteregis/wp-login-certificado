<?php

if (!defined('ABSPATH')) {
    exit;
}

// Registra as opções
add_action('admin_init', function () {
    register_setting('login_cert_settings', 'login_cert_jwt_secret', [
        'sanitize_callback' => function ($new_value) {
            $placeholder = '••••••••••••••••';
            if ($new_value === '' || $new_value === $placeholder) {
                return get_option('login_cert_jwt_secret', '');
            }
            return $new_value;
        },
    ]);
    register_setting('login_cert_settings', 'login_cert_iss');
    register_setting('login_cert_settings', 'login_cert_aud');
    register_setting('login_cert_settings', 'login_cert_ip_mode');
    register_setting('login_cert_settings', 'login_cert_custom_ip');

    add_settings_section(
        'login_cert_jwt_section',
        'Configurações do JWT',
        function () {
            echo '<p>Configure os parâmetros de validação do token JWT.</p>';
        },
        'login-certificado'
    );

    add_settings_field(
        'login_cert_jwt_secret',
        'Chave Secreta (Secret)',
        function () {
            $saved = get_option('login_cert_jwt_secret', '');
            $display = $saved !== '' ? '••••••••••••••••' : '';
            echo '<input type="text" name="login_cert_jwt_secret" value="' . esc_attr($display) . '" class="regular-text login-cert-secret" autocomplete="off" />';
            echo '<p class="description">Chave secreta usada para assinar e validar os tokens JWT. Preencha apenas se deseja alterar.</p>';
        },
        'login-certificado',
        'login_cert_jwt_section'
    );

    add_settings_field(
        'login_cert_iss',
        'Issuer (iss)',
        function () {
            $value = get_option('login_cert_iss', '');
            echo '<input type="text" name="login_cert_iss" value="' . esc_attr($value) . '" class="regular-text" />';
            echo '<p class="description">Valor esperado do campo <code>iss</code> no token JWT.</p>';
        },
        'login-certificado',
        'login_cert_jwt_section'
    );

    add_settings_field(
        'login_cert_aud',
        'Audience (aud)',
        function () {
            $value = get_option('login_cert_aud', '');
            echo '<input type="text" name="login_cert_aud" value="' . esc_attr($value) . '" class="regular-text" />';
            echo '<p class="description">Valor esperado do campo <code>aud</code> no token JWT.</p>';
        },
        'login-certificado',
        'login_cert_jwt_section'
    );

    add_settings_field(
        'login_cert_ip_mode',
        'Validação de IP',
        function () {
            $mode = get_option('login_cert_ip_mode', 'server');
            $custom_ip = get_option('login_cert_custom_ip', '');
            ?>
            <fieldset>
                <label>
                    <input type="radio" name="login_cert_ip_mode" value="none" <?php checked($mode, 'none'); ?> />
                    Não validar IP
                </label>
                <br />
                <label>
                    <input type="radio" name="login_cert_ip_mode" value="server" <?php checked($mode, 'server'); ?> />
                    Comparar com o IP do servidor
                </label>
                <br />
                <label>
                    <input type="radio" name="login_cert_ip_mode" value="custom" <?php checked($mode, 'custom'); ?> />
                    Comparar com um IP específico:
                </label>
                <input type="text" name="login_cert_custom_ip" value="<?php echo esc_attr($custom_ip); ?>"
                    class="regular-text" placeholder="Ex: 192.168.1.100" />
            </fieldset>
            <p class="description">Escolha como validar o campo <code>ip</code> presente no token JWT.</p>
            <?php
        },
        'login-certificado',
        'login_cert_jwt_section'
    );
});

// Adiciona a página de menu
add_action('admin_menu', function () {
    add_options_page(
        'Login por Certificado',
        'Login Certificado',
        'manage_options',
        'login-certificado',
        function () {
            if (!current_user_can('manage_options')) {
                return;
            }
            ?>
            <div class="wrap">
                <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                <form method="post" action="options.php" autocomplete="off">
                    <?php
                    settings_fields('login_cert_settings');
                    do_settings_sections('login-certificado');
                    submit_button('Salvar Configurações');
                    ?>
                </form>
            </div>
            <?php
        }
    );
});
