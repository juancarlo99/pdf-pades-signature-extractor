<?php

declare(strict_types=1);

namespace Juancarlo99\PdfPadesSignatureExtractor\Extractor;

use DateTimeImmutable;

/**
 * Extracts SignerInfo attributes (SigningTime) from PKCS#7/CMS signatures.
 * Uses phpseclib's ASN1 when available; otherwise falls back to a robust DER scan.
 */
class Pkcs7SignerInfoExtractor
{
    private const SIGNING_TIME_OID_BYTES = "\x06\x09\x2A\x86\x48\x86\xF7\x0D\x01\x09\x05"; // 1.2.840.113549.1.9.5

    /**
     * Extract SigningTime (OID 1.2.840.113549.1.9.5) from a PKCS#7/CMS binary.
     * Supports UTCTime and GeneralizedTime.
     */
    public function extractSigningTime(string $pkcs7Binary): ?DateTimeImmutable
    {
        // Try phpseclib3\File\ASN1 if available (preferred implementation)
        if (class_exists('phpseclib3\\File\\ASN1')) {
            $dt = $this->extractWithPhpseclib($pkcs7Binary);
            if ($dt instanceof DateTimeImmutable) {
                return $dt;
            }
        }

        // Fallback: direct DER scan for SigningTime attribute
        return $this->extractByDerScan($pkcs7Binary);
    }

    private function extractWithPhpseclib(string $der): ?DateTimeImmutable
    {
        // Minimal decoding approach: search for SigningTime attribute via decoded BER tree
        // to avoid full CMS mapping complexity. If decoding fails, return null.
        try {
            $asn1 = new \phpseclib3\File\ASN1();
            $decoded = $asn1->decodeBER($der);
            if (!is_array($decoded)) {
                return null;
            }
            foreach ($decoded as $element) {
                $dt = $this->findSigningTimeInNode($element);
                if ($dt) {
                    return $dt;
                }
            }
        } catch (\Throwable $e) {
            return null;
        }
        return null;
    }

    /**
     * Recursively search decoded BER nodes to find SigningTime values.
     */
    private function findSigningTimeInNode(array $node): ?DateTimeImmutable
    {
        if (isset($node['value']) && is_array($node['value'])) {
            // Look for OID then time value nearby in sibling nodes
            foreach ($node['value'] as $idx => $child) {
                $oid = $this->getOidFromNode($child);
                if ($oid === '1.2.840.113549.1.9.5') {
                    // Next nodes should contain a SET with the time
                    $next = $node['value'][$idx + 1] ?? null;
                    if (is_array($next) && isset($next['value']) && is_array($next['value'])) {
                        foreach ($next['value'] as $valNode) {
                            $dt = $this->parseTimeNode($valNode);
                            if ($dt) {
                                return $dt;
                            }
                        }
                    }
                }
                // Recurse
                $dt = $this->findSigningTimeInNode($child);
                if ($dt) {
                    return $dt;
                }
            }
        }
        return null;
    }

    private function getOidFromNode(array $node): ?string
    {
        if (($node['type'] ?? null) === \phpseclib3\File\ASN1::TYPE_OBJECT_IDENTIFIER && isset($node['content'])) {
            // phpseclib returns OID as dotted string in 'content'
            return is_string($node['content']) ? $node['content'] : null;
        }
        return null;
    }

    private function parseTimeNode(array $node): ?DateTimeImmutable
    {
        if (!isset($node['type'])) {
            return null;
        }
        $content = $node['content'] ?? null;
        if (!is_string($content)) {
            return null;
        }
        if ($node['type'] === \phpseclib3\File\ASN1::TYPE_UTCTime) {
            return $this->parseUtcTime($content);
        }
        if ($node['type'] === \phpseclib3\File\ASN1::TYPE_GeneralizedTime) {
            return $this->parseGeneralizedTime($content);
        }
        return null;
    }

    private function extractByDerScan(string $der): ?DateTimeImmutable
    {
        $pos = 0;
        while (true) {
            $idx = strpos($der, self::SIGNING_TIME_OID_BYTES, $pos);
            if ($idx === false) {
                return null;
            }
            // After OID, expect a SET of values then a time primitive (UTCTime or GeneralizedTime)
            $scanStart = $idx + strlen(self::SIGNING_TIME_OID_BYTES);
            $dt = $this->scanForTimeAfter($der, $scanStart);
            if ($dt instanceof DateTimeImmutable) {
                return $dt;
            }
            $pos = $scanStart;
        }
    }

    private function scanForTimeAfter(string $der, int $offset): ?DateTimeImmutable
    {
        $len = strlen($der);
        for ($i = $offset; $i < $len; $i++) {
            $tag = ord($der[$i]);
            if ($tag === 0x17 || $tag === 0x18) { // UTCTime or GeneralizedTime
                $res = $this->readDerLength($der, $i + 1);
                if ($res === null) {
                    return null;
                }
                [$length, $lenLen] = $res;
                $start = $i + 1 + $lenLen;
                if ($start + $length > $len) {
                    return null;
                }
                $timeStr = substr($der, $start, $length);
                return $tag === 0x17
                    ? $this->parseUtcTime($timeStr)
                    : $this->parseGeneralizedTime($timeStr);
            }
        }
        return null;
    }

    /**
     * Read DER length at given offset, returning [length, bytesConsumed].
     */
    private function readDerLength(string $der, int $offset): ?array
    {
        if ($offset >= strlen($der)) {
            return null;
        }
        $first = ord($der[$offset]);
        if ($first < 0x80) {
            return [$first, 1];
        }
        $numBytes = $first & 0x7F;
        if ($numBytes === 0 || $offset + 1 + $numBytes > strlen($der)) {
            return null;
        }
        $length = 0;
        for ($j = 0; $j < $numBytes; $j++) {
            $length = ($length << 8) | ord($der[$offset + 1 + $j]);
        }
        return [$length, 1 + $numBytes];
    }

    private function parseUtcTime(string $str): ?DateTimeImmutable
    {
        // UTCTime: YYMMDDHHMM[SS]Z or with timezone offset (+/-HHMM)
        $len = strlen($str);
        if ($len < 11) {
            return null;
        }
        if ($str[$len - 1] === 'Z') {
            $digits = substr($str, 0, $len - 1);
            if ($digits === false) {
                return null;
            }
            $fmt = strlen($digits) === 12 ? 'ymdHiS' : (strlen($digits) === 10 ? 'ymdHi' : null);
            if ($fmt === null) {
                return null;
            }
            $dt = DateTimeImmutable::createFromFormat($fmt . '\\Z', $digits . 'Z');
            if ($dt instanceof DateTimeImmutable) {
                return $dt;
            }
            // Fallback manual parse
            $hasSeconds = strlen($digits) === 12;
            $yy   = (int) substr($digits, 0, 2);
            $year = ($yy >= 50 ? 1900 + $yy : 2000 + $yy);
            $mon  = (int) substr($digits, 2, 2);
            $day  = (int) substr($digits, 4, 2);
            $hour = (int) substr($digits, 6, 2);
            $min  = (int) substr($digits, 8, 2);
            $sec  = $hasSeconds ? (int) substr($digits, 10, 2) : 0;
            $date = sprintf('%04d-%02d-%02d %02d:%02d:%02d', $year, $mon, $day, $hour, $min, $sec);
            $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $date, new \DateTimeZone('UTC'));
            return $dt ?: null;
        }
        // With offset
        if ($len >= 14) {
            $offset = substr($str, -5);
            if ($offset !== false && ($offset[0] === '+' || $offset[0] === '-')) {
                $digits = substr($str, 0, $len - 5);
                if ($digits === false) {
                    return null;
                }
                $fmt = strlen($digits) === 12 ? 'ymdHiS' : (strlen($digits) === 10 ? 'ymdHi' : null);
                if ($fmt === null) {
                    return null;
                }
                $dt = DateTimeImmutable::createFromFormat($fmt . 'O', $digits . $offset);
                return $dt ?: null;
            }
        }
        return null;
    }

    private function parseGeneralizedTime(string $str): ?DateTimeImmutable
    {
        // GeneralizedTime: YYYYMMDDHHMMSS[.fff]Z or with timezone offset (+/-HHMM)
        $len = strlen($str);
        if ($len < 15) {
            return null;
        }
        // Strip fractional seconds if present
        $main = $str;
        $fracPos = strpos($str, '.');
        if ($fracPos !== false) {
            // preserve timezone suffix
            $suffix = substr($str, $fracPos);
            $zPos = strpos($suffix, 'Z');
            if ($zPos !== false) {
                $main = substr($str, 0, $fracPos) . 'Z';
            } else {
                // assume offset present
                $main = substr($str, 0, $fracPos) . substr($str, -5);
            }
        }
        $len = strlen($main);
        if ($main[$len - 1] === 'Z') {
            $digits = substr($main, 0, $len - 1);
            if ($digits === false || strlen($digits) !== 14) {
                return null;
            }
            $dt = DateTimeImmutable::createFromFormat('YmdHis\\Z', $digits . 'Z');
            return $dt ?: null;
        }
        // With offset
        $offset = substr($main, -5);
        if ($offset !== false && ($offset[0] === '+' || $offset[0] === '-')) {
            $digits = substr($main, 0, strlen($main) - 5);
            if ($digits === false || strlen($digits) !== 14) {
                return null;
            }
            $dt = DateTimeImmutable::createFromFormat('YmdHisO', $digits . $offset);
            return $dt ?: null;
        }
        return null;
    }
}
