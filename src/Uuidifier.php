<?php

namespace Teamleader\Uuidifier;

use InvalidArgumentException;
use Ramsey\Uuid\BinaryUtils;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidInterface;

class Uuidifier
{
    /**
     * @var int
     */
    private $version;

    /**
     * @param int $version
     */
    public function __construct($version = 0)
    {
        $this->version = $version;
    }

    public function encode(string $prefix, int $id): UuidInterface
    {
        $hash = sha1($prefix . $id);
        $hex = dechex($id);
        $length = strlen($hex);
        $hash = substr($hash, 0, 32 - $length) . $hex;

        $timeHi = BinaryUtils::applyVersion(substr($hash, 12, 4), $this->version);
        $clockSeqHi = BinaryUtils::applyVariant(hexdec(substr($hash, 16, 2)));

        $fields = [
            'time_low' => substr($hash, 0, 8),
            'time_mid' => substr($hash, 8, 4),
            'time_hi_and_version' => str_pad(dechex($timeHi), 4, '0', STR_PAD_LEFT),
            'clock_seq_hi_and_reserved' => str_pad(dechex($clockSeqHi), 2, '0', STR_PAD_LEFT),
            'clock_seq_low' => dechex($length) . substr($hash, 19, 1),
            'node' => substr($hash, 20, 12),
        ];

        return (new UuidFactory())->uuid($fields);
    }

    public function decode(UuidInterface $uuid): int
    {
        if ($uuid->getVersion() != $this->version) {
            throw new InvalidArgumentException('Can only decode version ' . $this->version . ' uuids');
        }

        $length = hexdec($uuid->getClockSeqLowHex()[0]);
        $hex = substr($uuid->getHex(), 32 - $length, $length);

        return hexdec($hex);
    }

    public function isValid(string $prefix, UuidInterface $uuid): bool
    {
        $decoded = $this->decode($uuid);
        $encoded = $this->encode($prefix, $decoded);

        return $uuid->equals($encoded);
    }
}
