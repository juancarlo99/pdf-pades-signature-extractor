<?php

namespace Juancarlo99\PdfPadesSignatureExtractor\DTO;

use DateTime;
use DateTimeImmutable;

class SignatureData
{
    public string $signerName;
    public ?string $cpf = null;
    public ?string $cnpj = null;
    public string $issuer;
    public DateTime $signedAt;
    public DateTime $validFrom;
    public DateTime $validTo;
    /** @var array<int, string> PEM-encoded certificates */
    public array $certificates = [];
    /** SigningTime from CMS (optional) */
    public ?DateTimeImmutable $signingDateTime = null;
}
