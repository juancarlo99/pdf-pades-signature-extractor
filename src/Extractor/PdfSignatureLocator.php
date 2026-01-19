<?php

namespace Juancarlo99\PdfPadesSignatureExtractor\Extractor;

use Juancarlo99\PdfPadesSignatureExtractor\Exception\PdfNotSignedException;

class PdfSignatureLocator
{
    public function locate(string $pdfContent): string
    {
        if (!preg_match('/\/ByteRange\s*\[\s*(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s*\]/', $pdfContent)) {
            throw new PdfNotSignedException('PDF does not contain a PAdES signature');
        }

        if (!preg_match('/\/Contents\s*<([0-9A-Fa-f\s]+)>/', $pdfContent, $matches)) {
            throw new PdfNotSignedException('Signature contents not found');
        }

        return hex2bin(preg_replace('/\s+/', '', $matches[1]));
    }

    /**
     * Locate all PAdES signatures in a PDF content.
     *
     * @return array<int, string> List of PKCS#7/CMS binaries
     * @throws PdfNotSignedException
     */
    public function locateAll(string $pdfContent): array
    {
        if (!preg_match('/\/ByteRange\s*\[\s*(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s*\]/', $pdfContent)) {
            throw new PdfNotSignedException('PDF does not contain a PAdES signature');
        }

        if (!preg_match_all('/\/Contents\s*<([0-9A-Fa-f\s]+)>/', $pdfContent, $matches)) {
            throw new PdfNotSignedException('Signature contents not found');
        }

        $list = [];
        foreach ($matches[1] as $hex) {
            $list[] = hex2bin(preg_replace('/\s+/', '', $hex));
        }

        return $list;
    }
}
