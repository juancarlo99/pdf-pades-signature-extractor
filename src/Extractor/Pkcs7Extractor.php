<?php

declare(strict_types=1);

namespace Juancarlo99\PdfPadesSignatureExtractor\Extractor;

use Juancarlo99\PdfPadesSignatureExtractor\Exception\SignatureExtractionException;
use Juancarlo99\PdfPadesSignatureExtractor\Utils\PemFormatter;

/**
 * @internal Extracts certificates from PKCS#7/CMS signatures
 */
class Pkcs7Extractor
{
    /**
     * @return array<int, string> PEM-encoded certificates
     */
    public function extractCertificates(string $pkcs7Binary): array
    {
        $tempIn = tempnam(sys_get_temp_dir(), 'pkcs7_');
        file_put_contents($tempIn, $pkcs7Binary);

        $certs = [];
        $extracted = false;

        // Try CMS verify to extract certificates from DER-encoded signature
        if (defined('OPENSSL_ENCODING_DER')) {
            $tempCerts = tempnam(sys_get_temp_dir(), 'certs_');

            $verified = @openssl_cms_verify(
                $tempIn,
                0,
                $tempCerts,
                [],
                null,
                null,
                null,
                null,
                OPENSSL_ENCODING_DER
            );

            if ($verified === true && file_exists($tempCerts)) {
                $pem = file_get_contents($tempCerts);
                @unlink($tempCerts);

                if ($pem !== false) {
                    if (preg_match_all('/-----BEGIN CERTIFICATE-----.*?-----END CERTIFICATE-----/s', $pem, $m)) {
                        $certs = $m[0];
                        $extracted = !empty($certs);
                    }
                }
            }
        }

        // Fallbacks
        if (!$extracted) {
            if (function_exists('openssl_cms_read')) {
                $extracted = openssl_cms_read($tempIn, $certs) === true;
            } else {
                $pemPkcs7 = "-----BEGIN PKCS7-----\n" . chunk_split(base64_encode($pkcs7Binary), 64, "\n") . "-----END PKCS7-----\n";
                $extracted = openssl_pkcs7_read($pemPkcs7, $certs) === true;
            }
        }

        // CLI fallback using openssl (prints PEM certificates)
        if (!$extracted) {
            $cmd = 'openssl pkcs7 -inform DER -in ' . escapeshellarg($tempIn) . ' -print_certs';
            $output = @shell_exec($cmd);
            if (is_string($output) && preg_match_all('/-----BEGIN CERTIFICATE-----.*?-----END CERTIFICATE-----/s', $output, $m)) {
                $certs = $m[0];
                $extracted = !empty($certs);
            }
        }

        @unlink($tempIn);

        if (!$extracted || empty($certs)) {
            throw new SignatureExtractionException('Unable to extract certificates from PKCS7');
        }

        return $certs;
    }
}
