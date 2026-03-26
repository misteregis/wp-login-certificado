<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Extrai CNPJ do subject do certificado
 */
function extractCNPJ($subject)
{
    if (!$subject) {
        return null;
    }

    // Exemplo: /C=BR/O=Empresa/CN=Nome:12345678000199
    if (preg_match('/\d{14}/', $subject, $matches)) {
        return $matches[0];
    }

    return null;
}

function getServerIP(): string
{
    // Tenta pelo SERVER_ADDR
    if (!empty($_SERVER['SERVER_ADDR'])) {
        return $_SERVER['SERVER_ADDR'];
    }

    // Tenta pelo hostname
    if ($hostname = gethostname()) {
        $ip = gethostbyname($hostname);
        if ($ip !== $hostname) {
            return $ip;
        }
    }

    // Fallback final
    return 'IP não encontrado';
}
