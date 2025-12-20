<?php

namespace Juancarlo99\PdfPadesSignatureExtractor\Tests\Extractor;

use PHPUnit\Framework\TestCase;
use Juancarlo99\PdfPadesSignatureExtractor\Extractor\PdfSignatureLocator;

/**
 * @group sensitive
 */
class PdfSignatureLocatorTest extends TestCase
{
    public function testItExtractsPkcs7FromSignedPdf(): void
    {
        $pdf = file_get_contents(__DIR__ . '/../samples/pades.pdf');

        $locator = new PdfSignatureLocator();
        $pkcs7 = $locator->locate($pdf);

        $this->assertNotEmpty($pkcs7);
        $this->assertIsString($pkcs7);
    }
}
