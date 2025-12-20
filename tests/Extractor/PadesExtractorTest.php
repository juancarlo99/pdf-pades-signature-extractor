<?php

namespace Juancarlo99\PdfPadesSignatureExtractor\Tests\Extractor;

use PHPUnit\Framework\TestCase;
use Juancarlo99\PdfPadesSignatureExtractor\Extractor\PadesExtractor;
use Juancarlo99\PdfPadesSignatureExtractor\DTO\SignatureData;

class PadesExtractorTest extends TestCase
{
    public function testItExtractsSignatureData(): void
    {
        $extractor = new PadesExtractor();

        $data = $extractor->extract(__DIR__ . '/../samples/pades.pdf');

        $this->assertInstanceOf(SignatureData::class, $data);
        $this->assertNotEmpty($data->signerName);
        $this->assertNotEmpty($data->issuer);
        $this->assertNotNull($data->validFrom);
        $this->assertNotNull($data->validTo);
    }
}
