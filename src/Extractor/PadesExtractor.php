<?php

declare(strict_types=1);

namespace Juancarlo99\PdfPadesSignatureExtractor\Extractor;

use Juancarlo99\PdfPadesSignatureExtractor\Certificate\X509Certificate;
use Juancarlo99\PdfPadesSignatureExtractor\DTO\SignatureData;
use Juancarlo99\PdfPadesSignatureExtractor\Exception\SignatureExtractionException;

class PadesExtractor
{
    public function extract(string $pdfPath): SignatureData
    {
        if (!is_file($pdfPath)) {
            throw new SignatureExtractionException('PDF file not found: ' . $pdfPath);
        }

        $content = file_get_contents($pdfPath);

        $locator = new PdfSignatureLocator();
        $pkcs7   = $locator->locate($content);

        $pkcs7Extractor = new Pkcs7Extractor();
        $certificates   = $pkcs7Extractor->extractCertificates($pkcs7);

        // Primeiro certificado normalmente é o do assinante
        $x509 = new X509Certificate($certificates[0]);

        $dto = new SignatureData();
        $dto->signerName = $x509->getSignerName();
        $dto->cpf        = $x509->getCpf();
        $dto->cnpj       = $x509->getCnpj();
        $dto->issuer     = $x509->getIssuer();
        $dto->validFrom  = $x509->getValidFrom();
        $dto->validTo    = $x509->getValidTo();
        $dto->signedAt   = $dto->validFrom; // fallback técnico

        return $dto;
    }
}
