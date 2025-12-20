<?php

namespace Juancarlo99\PdfPadesSignatureExtractor\Certificate;

class OidResolver
{
    public const CPF_OID  = '2.16.76.1.3.1';
    public const CNPJ_OID = '2.16.76.1.3.3';

    public static function extractCpf(string $subjectAltName): ?string
    {
        if (preg_match('/' . self::CPF_OID . ':\s*([0-9]{11})/', $subjectAltName, $m)) {
            return $m[1];
        }

        return null;
    }

    public static function extractCnpj(string $subjectAltName): ?string
    {
        if (preg_match('/' . self::CNPJ_OID . ':\s*([0-9]{14})/', $subjectAltName, $m)) {
            return $m[1];
        }

        return null;
    }
}
