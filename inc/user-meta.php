<?php

if (!defined('ABSPATH')) exit;

// Adiciona campo no perfil
add_action('show_user_profile', 'lc_add_cnpj_field');
add_action('edit_user_profile', 'lc_add_cnpj_field');

function lc_add_cnpj_field($user) {
?>
    <h3>CNPJ</h3>
    <table class="form-table">
        <tr>
            <th><label for="cnpj">CNPJ</label></th>
            <td>
                <input type="text" name="cnpj" id="cnpj"
                    value="<?php echo esc_attr(get_user_meta($user->ID, 'cnpj', true)); ?>"
                    autocomplete="off"
                    maxlength="18"
                    class="regular-text" />
            </td>
        </tr>
    </table>
<?php
}

// Salvar campo
add_action('personal_options_update', 'lc_save_cnpj_field');
add_action('edit_user_profile_update', 'lc_save_cnpj_field');

function lc_save_cnpj_field($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return;
    }

    if (!isset($_POST['cnpj'])) {
        return;
    }

    $cnpj = preg_replace('/\D/', '', $_POST['cnpj']);

    if (empty($cnpj)) {
        delete_user_meta($user_id, 'cnpj');
        return;
    }

    // Verifica duplicidade
    $users = get_users([
        'meta_query' => [
            [
                'key' => 'cnpj',
                'value' => $cnpj,
                'compare' => '='
            ]
        ],
        'exclude' => [$user_id],
        'fields' => ['display_name', 'user_login']
    ]);

    if (!empty($users)) {
        add_action('user_profile_update_errors', function($errors) {
            $errors->add('cnpj_duplicado', 'Este CNPJ já está vinculado a outro usuário.');
        });
        return;
    }

    update_user_meta($user_id, 'cnpj', $cnpj);
}