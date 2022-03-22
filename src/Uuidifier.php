<?php

namespace Teamleader\Uuidifier;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
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

    /**
     * @param string $prefix
     * @param int $id
     * @return UuidInterface
     */
    public function encode($prefix, $id)
    {
        if (!is_int($id)) {
            throw new InvalidArgumentException('Can only encode integers');
        }

        $hash = sha1($prefix . $id);
        $hex = dechex($id);
        $length = strlen($hex);
        $hash = substr($hash, 0, 32 - $length) . $hex;

        $timeHi = $this->applyVersion(substr($hash, 12, 4), $this->version);
        $clockSeqHi = $this->applyVariant(hexdec(substr($hash, 16, 2)));

        $fields = [
            'time_low' => substr($hash, 0, 8),
            'time_mid' => substr($hash, 8, 4),
            'time_hi_and_version' => str_pad(dechex($timeHi), 4, '0', STR_PAD_LEFT),
            'clock_seq_hi_and_reserved' => str_pad(dechex($clockSeqHi), 2, '0', STR_PAD_LEFT),
            'clock_seq_low' => dechex($length) . substr($hash, 19, 1),
            'node' => substr($hash, 20, 12),
        ];

        $hex = vsprintf(
            '%08s-%04s-%04s-%02s%02s-%012s',
            $fields
        );

        return Uuid::fromString($hex);
    }

    /**
     * @param UuidInterface $uuid
     * @return int
     */
    public function decode(UuidInterface $uuid)
    {
        if ($uuid->getVersion() != $this->version) {
            throw new InvalidArgumentException('Can only decode version ' . $this->version . ' uuids');
        }

        $length = hexdec($uuid->getClockSeqLowHex()[0]);
        $hex = substr($uuid->getHex(), 32 - $length, $length);

        return hexdec($hex);
    }

    /**
     * @param string $prefix
     * @param UuidInterface $uuid
     *
     * @return bool
     */
    public function isValid($prefix, UuidInterface $uuid)
    {
        $decoded = $this->decode($uuid);
        $encoded = $this->encode($prefix, $decoded);

        return $uuid->equals($encoded);
    }

    /**
     * Applies the RFC 4122 variant field to the `clock_seq_hi_and_reserved` field
     *
     * @param $clockSeqHi
     * @return int The high field of the clock sequence multiplexed with the variant
     * @link http://tools.ietf.org/html/rfc4122#section-4.1.1
     */
    public static function applyVariant($clockSeqHi)
    {
        // Set the variant to RFC 4122
        $clockSeqHi = $clockSeqHi & 0x3f;
        $clockSeqHi |= 0x80;

        return $clockSeqHi;
    }

    /**
     * Applies the RFC 4122 version number to the `time_hi_and_version` field
     *
     * @param string $timeHi
     * @param integer $version
     * @return int The high field of the timestamp multiplexed with the version number
     * @link http://tools.ietf.org/html/rfc4122#section-4.1.3
     */
    public static function applyVersion($timeHi, $version)
    {
        $timeHi = hexdec($timeHi) & 0x0fff;
        $timeHi |= $version << 12;

        return $timeHi;
    }
}
