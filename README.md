# Login por Certificado (CNPJ)

Plugin WordPress para autenticação automática de usuários via certificado digital, utilizando o CNPJ presente no certificado como identificador.

## Índice

- [Visão Geral](#visão-geral)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Configuração do Servidor](#configuração-do-servidor)
- [Como Funciona](#como-funciona)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Uso](#uso)
- [Referência Técnica](#referência-técnica)
- [Autor](#autor)

## Visão Geral

O plugin registra a rota `/login-certificado` no WordPress. Quando um usuário acessa essa rota com um certificado digital válido (contendo um CNPJ de 14 dígitos), o plugin:

1. Lê o **subject** do certificado a partir do header `X-Client-Subject` (enviado pelo reverse proxy).
2. Extrai o **CNPJ** (14 dígitos) do subject.
3. Busca um usuário WordPress que possua aquele CNPJ vinculado via `user_meta`.
4. Autentica o usuário automaticamente e redireciona para a página inicial.

Além disso, o plugin adiciona um campo **CNPJ** ao perfil de cada usuário no painel administrativo, com validação de duplicidade em tempo real via AJAX e formatação automática.

## Requisitos

- **WordPress** 5.0+
- **PHP** 7.4+
- **Servidor web** com suporte a certificados de cliente (ex.: Nginx ou Apache como reverse proxy)
- O reverse proxy deve repassar o subject do certificado no header HTTP `X-Client-Subject`

## Instalação

1. Copie a pasta `login-certificado` para `wp-content/plugins/`.
2. Ative o plugin no painel **Plugins** do WordPress.
3. O plugin faz flush das rewrite rules automaticamente na primeira ativação (controlado por versão).

## Configuração do Servidor

O plugin depende do header `X-Client-Subject` para receber as informações do certificado digital.

### IIS (web.config)

Adicione o bloco abaixo ao `web.config` do site para que o IIS negocie o certificado do cliente na rota `/login-certificado` e repasse o subject via server variable:

```xml
<location path="login-certificado">
    <system.webServer>
        <security>
            <access sslFlags="Ssl, SslNegotiateCert" />
        </security>
        <rewrite>
            <rules>
                <rule name="CertHeader">
                    <match url=".*" />
                    <serverVariables>
                        <set name="HTTP_X_CLIENT_SUBJECT" value="{CERT_SUBJECT}" />
                    </serverVariables>
                    <action type="None" />
                </rule>
            </rules>
        </rewrite>
    </system.webServer>
</location>
```

> **Importante:** Para que a server variable `HTTP_X_CLIENT_SUBJECT` funcione, é necessário adicioná-la na lista de **Allowed Server Variables** no IIS (URL Rewrite → View Server Variables → Add).

### Nginx

```nginx
server {
    listen 443 ssl;

    ssl_client_certificate /etc/nginx/certs/ca.crt;
    ssl_verify_client optional;

    location /login-certificado {
        proxy_set_header X-Client-Subject $ssl_client_s_dn;
        proxy_pass http://wordpress_backend;
    }
}
```

> **Nota:** O formato esperado do subject é algo como `/C=BR/O=Empresa/CN=Nome:12345678000199`, onde `12345678000199` é o CNPJ de 14 dígitos.

## Como Funciona

### Fluxo de Autenticação

```
Usuário com certificado
        │
        ▼
  Acessa /login-certificado
        │
        ▼
  Nginx valida o certificado
  e envia header X-Client-Subject
        │
        ▼
  Plugin extrai CNPJ (14 dígitos)
        │
        ▼
  Busca usuário WP com meta 'cnpj'
        │
        ├── Encontrou → Login automático + redirect
        │
        └── Não encontrou → Mensagem de erro
```

### Campo CNPJ no Perfil

- O campo CNPJ aparece na tela de edição de perfil (próprio e de outros usuários).
- Ao digitar, o CNPJ é **formatado automaticamente** no padrão `XX.XXX.XXX/XXXX-XX`.
- A verificação de **duplicidade** é feita via AJAX enquanto o usuário digita (com debounce de 400ms), exibindo um aviso caso o CNPJ já esteja vinculado a outro usuário.
- No backend, a duplicidade também é validada antes de salvar.

## Estrutura do Projeto

```
login-certificado/
├── login-certificado.php      # Arquivo principal — carrega todos os módulos
├── assets/
│   └── js/
│       └── cnpj-check.js      # Formatação e verificação de duplicidade (frontend)
└── inc/
    ├── setup.php              # Registra rewrite rule e query var
    ├── auth.php               # Lógica de autenticação via certificado
    ├── helpers.php            # Função utilitária para extrair CNPJ
    ├── user-meta.php          # Campo CNPJ no perfil + salvamento com validação
    ├── ajax.php               # Endpoint AJAX para verificar duplicidade de CNPJ
    └── assets.php             # Enfileiramento de scripts no admin
```

## Uso

### Vincular um CNPJ a um usuário

1. Acesse **Usuários → Editar** no painel do WordPress.
2. Localize a seção **CNPJ**.
3. Informe o CNPJ desejado (com ou sem formatação).
4. Salve o perfil.

### Autenticar via certificado

Acesse `https://seusite.com/login-certificado` com um navegador que possua o certificado digital instalado. Se o CNPJ do certificado estiver vinculado a um usuário, o login será feito automaticamente.

## Referência Técnica

### Hooks do WordPress utilizados

| Hook                              | Tipo     | Descrição                                      |
|-----------------------------------|----------|-------------------------------------------------|
| `query_vars`                      | Filter   | Registra a query var `login_cert`               |
| `init`                            | Action   | Registra a rewrite rule e controla flush        |
| `template_redirect`               | Action   | Executa a lógica de autenticação                |
| `show_user_profile`               | Action   | Exibe campo CNPJ no perfil próprio              |
| `edit_user_profile`               | Action   | Exibe campo CNPJ no perfil de outros            |
| `personal_options_update`         | Action   | Salva CNPJ ao atualizar perfil próprio          |
| `edit_user_profile_update`        | Action   | Salva CNPJ ao atualizar perfil de outro usuário |
| `user_profile_update_errors`      | Action   | Adiciona erro de duplicidade de CNPJ            |
| `admin_enqueue_scripts`           | Action   | Enfileira JS nas telas de perfil                |
| `wp_ajax_check_cnpj`             | Action   | Endpoint AJAX para verificar CNPJ duplicado     |

### Meta do Usuário

| Meta Key | Tipo   | Descrição                           |
|----------|--------|-------------------------------------|
| `cnpj`   | string | CNPJ (somente dígitos, 14 caracteres) |

### Opções

| Option Key                   | Descrição                                     |
|------------------------------|-----------------------------------------------|
| `login_cert_rewrite_version` | Controle de flush de rewrite rules por versão |

## Autor

**Misteregis** — [github.com/misteregis](https://github.com/misteregis/)
