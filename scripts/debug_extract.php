<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use Juancarlo99\PdfPadesSignatureExtractor\Extractor\PdfSignatureLocator;

$input = __DIR__ . '/../tests/samples/pades.pdf';
$out   = __DIR__ . '/../tmp_pkcs7.der';

$pdf = file_get_contents($input);
$locator = new PdfSignatureLocator();
$pkcs7 = $locator->locate($pdf);
file_put_contents($out, $pkcs7);

fwrite(STDOUT, "Extracted PKCS7 length: " . strlen($pkcs7) . "\n");
