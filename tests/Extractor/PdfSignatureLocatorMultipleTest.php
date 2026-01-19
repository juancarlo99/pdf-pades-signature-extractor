<?php

declare(strict_types=1);

namespace Juancarlo99\PdfPadesSignatureExtractor\Tests\Extractor;

use Juancarlo99\PdfPadesSignatureExtractor\Extractor\PdfSignatureLocator;
use PHPUnit\Framework\TestCase;

class PdfSignatureLocatorMultipleTest extends TestCase
{
    public function testLocateAllFindsMultipleContents(): void
    {
        $hex1 = 'A1B2C3';
        $hex2 = 'D4E5F6';
        $pdf = "%PDF-1.7\n/ByteRange [0 100 200 300]\n/Contents <{$hex1}>\n... more ...\n/Contents <{$hex2}>\n";

        $locator = new PdfSignatureLocator();
        $all = $locator->locateAll($pdf);

        $this->assertCount(2, $all);
        $this->assertIsString($all[0]);
        $this->assertIsString($all[1]);
    }
}
