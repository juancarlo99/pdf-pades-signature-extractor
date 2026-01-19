<?php

declare(strict_types=1);

namespace Juancarlo99\PdfPadesSignatureExtractor\Tests\Extractor;

use Juancarlo99\PdfPadesSignatureExtractor\Extractor\Pkcs7SignerInfoExtractor;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class Pkcs7SignerInfoExtractorTest extends TestCase
{
    public function testExtractsSigningTimeFromMinimalDer(): void
    {
        // 2024-01-01 12:00:00Z (UTCTime)
        $der = $this->buildSigningTimeAttributeDer('240101120000Z');

        $ext = new Pkcs7SignerInfoExtractor();
        $dt = $ext->extractSigningTime($der);

        $this->assertInstanceOf(DateTimeImmutable::class, $dt);
        $this->assertSame('2024-01-01T12:00:00+00:00', $dt->format('c'));
    }

    public function testReturnsNullWhenNoSigningTime(): void
    {
        $ext = new Pkcs7SignerInfoExtractor();
        $dt = $ext->extractSigningTime("random-bytes-without-oid");
        $this->assertNull($dt);
    }

    /**
     * Build minimal DER bytes that contain the signingTime OID followed by a time primitive.
     * Not a full CMS structure; sufficient for linear scan fallback.
     */
    private function buildSigningTimeAttributeDer(string $timeStr): string
    {
        // OID for signingTime: 06 09 2A 86 48 86 F7 0D 01 09 05
        $oid = "\x06\x09\x2A\x86\x48\x86\xF7\x0D\x01\x09\x05";

        // Time tag: UTCTime (0x17) or GeneralizedTime (0x18) depending on length
        $isGeneralized = preg_match('/^\d{14}/', $timeStr) === 1;
        $tag = $isGeneralized ? "\x18" : "\x17";
        $timeLen = strlen($timeStr);
        $time = $tag . $this->derLen($timeLen) . $timeStr;

        // Minimal bytes: OID immediately followed by the time primitive
        return $oid . $time;
    }

    private function derLen(int $len): string
    {
        if ($len < 0x80) {
            return chr($len);
        }
        $bytes = '';
        $n = $len;
        $stack = [];
        while ($n > 0) {
            $stack[] = chr($n & 0xFF);
            $n >>= 8;
        }
        $stack = array_reverse($stack);
        return chr(0x80 | count($stack)) . implode('', $stack);
    }
}
