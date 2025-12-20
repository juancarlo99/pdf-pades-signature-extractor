<?php

namespace Juancarlo99\PdfPadesSignatureExtractor\Utils;

class PemFormatter
{
    public static function formatCertificate(string $binary): string
    {
        return "-----BEGIN CERTIFICATE-----\n"
            . chunk_split(base64_encode($binary), 64, "\n")
            . "-----END CERTIFICATE-----\n";
    }
}
