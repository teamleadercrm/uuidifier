<?php

namespace Teamleader\Uuidifier;

use Ramsey\Uuid\Codec\StringCodec;
use Ramsey\Uuid\Converter\Number\GenericNumberConverter;
use Ramsey\Uuid\Converter\Time\GenericTimeConverter;
use Ramsey\Uuid\Exception\UuidExceptionInterface;
use Ramsey\Uuid\Math\BrickMathCalculator;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidInterface;
use Teamleader\Uuidifier\Builder\VersionZeroUuidBuilder;
use Teamleader\Uuidifier\Nonstandard\UuidV0;

class Uuidifier
{
    private const VERSION = 0;

    public function encode(string $prefix, int $id): UuidInterface
    {
        $hash = sha1($prefix . $id);
        $hex = dechex($id);
        $length = strlen($hex);
        $hash = substr($hash, 0, 32 - $length) . $hex;

        $timeHi = $this->applyVersion(substr($hash, 12, 4), self::VERSION);
        $clockSeqHi = $this->applyVariant(hexdec(substr($hash, 16, 2)));

        $fields = [
            'time_low' => substr($hash, 0, 8),
            'time_mid' => substr($hash, 8, 4),
            'time_hi_and_version' => str_pad(dechex($timeHi), 4, '0', STR_PAD_LEFT),
            'clock_seq_hi_and_reserved' => str_pad(dechex($clockSeqHi), 2, '0', STR_PAD_LEFT),
            'clock_seq_low' => dechex($length) . substr($hash, 19, 1),
            'node' => substr($hash, 20, 12),
        ];

        $bytes = (string) hex2bin(
            implode('', $fields),
        );

        $uuidFactory = new UuidFactory();

        $calculator = new BrickMathCalculator();
        $versionZeroUuidBuilder = new VersionZeroUuidBuilder(
            new GenericNumberConverter($calculator),
            new GenericTimeConverter($calculator),
        );

        $uuidFactory->setCodec(new StringCodec($versionZeroUuidBuilder));
        $uuidFactory->setUuidBuilder($versionZeroUuidBuilder);

        return $uuidFactory->uuid($bytes);
    }

    public function decode(UuidInterface $uuid): int
    {
        if ($uuid->getVersion() !== self::VERSION) {
            $uuid = $this->transformUnknownUuidIntoUuidVersionZero($uuid);
        }

        $length = hexdec($uuid->getClockSeqLowHex()[0]);
        $hex = substr($uuid->getHex(), 32 - $length, $length);

        return hexdec($hex);
    }

    public function isValid(string $prefix, UuidInterface $uuid): bool
    {
        if ($uuid->getVersion() !== self::VERSION) {
            $uuid = $this->transformUnknownUuidIntoUuidVersionZero($uuid);
        }

        $decoded = $this->decode($uuid);
        $encoded = $this->encode($prefix, $decoded);

        return $uuid->equals($encoded);
    }

    /**
     * @throws UuidExceptionInterface
     */
    private function transformUnknownUuidIntoUuidVersionZero(UuidInterface $uuid): UuidInterface
    {
        return UuidV0::fromString($uuid->toString());
    }

    /**
     * Applies the RFC 4122 variant field to the `clock_seq_hi_and_reserved` field
     *
     * @link http://tools.ietf.org/html/rfc4122#section-4.1.1
     */
    private function applyVariant(float|int $clockSeqHi): int
    {
        // Set the variant to RFC 4122
        $clockSeqHi = $clockSeqHi & 0x3f;
        $clockSeqHi |= 0x80;

        return $clockSeqHi;
    }

    /**
     * Applies the RFC 4122 version number to the `time_hi_and_version` field
     *
     * @link http://tools.ietf.org/html/rfc4122#section-4.1.3
     */
    private function applyVersion(string $timeHi, int $version): int
    {
        $timeHi = hexdec($timeHi) & 0x0fff;
        $timeHi |= $version << 12;

        return $timeHi;
    }
}
