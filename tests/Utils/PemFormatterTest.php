<?php

declare(strict_types=1);

namespace Juancarlo99\PdfPadesSignatureExtractor\Tests\Utils;

use PHPUnit\Framework\TestCase;
use Juancarlo99\PdfPadesSignatureExtractor\Utils\PemFormatter;

class PemFormatterTest extends TestCase
{
    public function testFormatsCertificateToPem(): void
    {
        $binary = random_bytes(64);
        $pem = PemFormatter::formatCertificate($binary);
        $this->assertStringContainsString('-----BEGIN CERTIFICATE-----', $pem);
        $this->assertStringContainsString('-----END CERTIFICATE-----', $pem);
    }
}
