<?php

declare(strict_types=1);

namespace Juancarlo99\PdfPadesSignatureExtractor\Tests\Extractor;

use PHPUnit\Framework\TestCase;
use Juancarlo99\PdfPadesSignatureExtractor\Extractor\PadesExtractor;
use Juancarlo99\PdfPadesSignatureExtractor\Exception\SignatureExtractionException;

class PadesExtractorErrorTest extends TestCase
{
    public function testThrowsWhenFileMissing(): void
    {
        $this->expectException(SignatureExtractionException::class);
        $extractor = new PadesExtractor();
        $extractor->extract('/path/to/missing.pdf');
    }
}
