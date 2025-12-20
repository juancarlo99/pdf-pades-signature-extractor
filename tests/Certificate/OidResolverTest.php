<?php

declare(strict_types=1);

namespace Juancarlo99\PdfPadesSignatureExtractor\Tests\Certificate;

use PHPUnit\Framework\TestCase;
use Juancarlo99\PdfPadesSignatureExtractor\Certificate\OidResolver;

class OidResolverTest extends TestCase
{
    public function testExtractCpf(): void
    {
        $san = OidResolver::CPF_OID . ': 12345678901';
        $cpf = OidResolver::extractCpf($san);
        $this->assertSame('12345678901', $cpf);
    }

    public function testExtractCnpj(): void
    {
        $san = OidResolver::CNPJ_OID . ': 12345678901234';
        $cnpj = OidResolver::extractCnpj($san);
        $this->assertSame('12345678901234', $cnpj);
    }

    public function testReturnsNullWhenMissing(): void
    {
        $san = 'other: value';
        $this->assertNull(OidResolver::extractCpf($san));
        $this->assertNull(OidResolver::extractCnpj($san));
    }
}
