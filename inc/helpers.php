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
