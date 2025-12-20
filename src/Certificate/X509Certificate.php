<?php

namespace Juancarlo99\PdfPadesSignatureExtractor\Certificate;

use DateTime;
use RuntimeException;

class X509Certificate
{
    /** @var array<string, mixed> */
    private array $data;

    public function __construct(string $pem)
    {
        $parsed = openssl_x509_parse($pem);

        if ($parsed === false) {
            throw new RuntimeException('Unable to parse X509 certificate');
        }

        $this->data = $parsed;
    }

    public function getSignerName(): ?string
    {
        return $this->data['subject']['CN'] ?? null;
    }

    public function getIssuer(): string
    {
        return $this->data['issuer']['CN'] ?? 'Unknown';
    }

    public function getValidFrom(): DateTime
    {
        return DateTime::createFromFormat('ymdHis\Z', $this->data['validFrom']);
    }

    public function getValidTo(): DateTime
    {
        return DateTime::createFromFormat('ymdHis\Z', $this->data['validTo']);
    }

    public function getCpf(): ?string
    {
        return isset($this->data['extensions']['subjectAltName'])
            ? OidResolver::extractCpf($this->data['extensions']['subjectAltName'])
            : null;
    }

    public function getCnpj(): ?string
    {
        return isset($this->data['extensions']['subjectAltName'])
            ? OidResolver::extractCnpj($this->data['extensions']['subjectAltName'])
            : null;
    }
}
