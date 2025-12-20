<?php

declare(strict_types=1);

namespace Juancarlo99\PdfPadesSignatureExtractor\Tests\Extractor;

use PHPUnit\Framework\TestCase;
use Juancarlo99\PdfPadesSignatureExtractor\Extractor\PdfSignatureLocator;
use Juancarlo99\PdfPadesSignatureExtractor\Exception\PdfNotSignedException;

class PdfSignatureLocatorNegativeTest extends TestCase
{
    public function testThrowsWhenPdfNotSigned(): void
    {
        $this->expectException(PdfNotSignedException::class);
        $locator = new PdfSignatureLocator();
        $locator->locate('not a signed pdf');
    }
}
