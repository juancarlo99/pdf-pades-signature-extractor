<?php

namespace Juancarlo99\PdfPadesSignatureExtractor\DTO;

use DateTime;

class SignatureData
{
    public string $signerName;
    public ?string $cpf = null;
    public ?string $cnpj = null;
    public string $issuer;
    public DateTime $signedAt;
    public DateTime $validFrom;
    public DateTime $validTo;
}
