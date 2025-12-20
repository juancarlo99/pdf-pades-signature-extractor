<?php

namespace Juancarlo99\PdfPadesSignatureExtractor\Tests\Certificate;

use PHPUnit\Framework\TestCase;
use Juancarlo99\PdfPadesSignatureExtractor\Certificate\X509Certificate;
use Juancarlo99\PdfPadesSignatureExtractor\Extractor\PdfSignatureLocator;
use Juancarlo99\PdfPadesSignatureExtractor\Extractor\Pkcs7Extractor;

class X509CertificateTest extends TestCase
{
    public function testParsesCertificate(): void
    {
        $pdf = file_get_contents(__DIR__ . '/../samples/pades.pdf');

        $locator = new PdfSignatureLocator();
        $pkcs7 = $locator->locate($pdf);

        $pkcs7Extractor = new Pkcs7Extractor();
        $certs = $pkcs7Extractor->extractCertificates($pkcs7);

        $cert = new X509Certificate($certs[0]);

        $this->assertNotEmpty($cert->getSignerName());
        $this->assertNotEmpty($cert->getIssuer());
    }
}
