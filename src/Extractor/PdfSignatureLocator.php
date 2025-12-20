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
}
