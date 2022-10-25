<?php

declare(strict_types=1);

namespace Teamleader\Uuidifier\Nonstandard;

use Ramsey\Uuid\Exception\InvalidArgumentException;
use Ramsey\Uuid\Fields\SerializableFieldsTrait;
use Ramsey\Uuid\Rfc4122\FieldsInterface;
use Ramsey\Uuid\Rfc4122\MaxTrait;
use Ramsey\Uuid\Rfc4122\NilTrait;
use Ramsey\Uuid\Rfc4122\VariantTrait;
use Ramsey\Uuid\Rfc4122\VersionTrait;
use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Uuid;

final class VersionZeroFields implements FieldsInterface
{
    use MaxTrait;
    use NilTrait;
    use SerializableFieldsTrait;
    use VariantTrait;
    use VersionTrait;

    private string $bytes;

    /**
     * @param string $bytes A 16-byte binary string representation of a UUID
     *
     * @throws InvalidArgumentException if the byte string is not exactly 16 bytes
     */
    public function __construct(
        string $bytes,
    ) {
        $this->bytes = $bytes;
        if (strlen($bytes) !== 16) {
            throw new InvalidArgumentException(
                'The byte string must be 16 bytes long; '
                . 'received ' . strlen($bytes) . ' bytes'
            );
        }

        if (!$this->isCorrectVariant()) {
            throw new InvalidArgumentException(
                'The byte string received does not conform to the RFC 4122 variant'
            );
        }

        if (!$this->isCorrectVersion()) {
            throw new InvalidArgumentException(
                'The byte string received does not contain a valid version'
            );
        }
    }

    public function getBytes(): string
    {
        return $this->bytes;
    }

    public function getClockSeq(): Hexadecimal
    {
        $clockSeq = hexdec(bin2hex(substr($this->bytes, 8, 2))) & 0x3fff;

        return new Hexadecimal(str_pad(dechex($clockSeq), 4, '0', STR_PAD_LEFT));
    }

    public function getClockSeqHiAndReserved(): Hexadecimal
    {
        return new Hexadecimal(bin2hex(substr($this->bytes, 8, 1)));
    }

    public function getClockSeqLow(): Hexadecimal
    {
        return new Hexadecimal(bin2hex(substr($this->bytes, 9, 1)));
    }

    public function getNode(): Hexadecimal
    {
        return new Hexadecimal(bin2hex(substr($this->bytes, 10)));
    }

    public function getTimeHiAndVersion(): Hexadecimal
    {
        return new Hexadecimal(bin2hex(substr($this->bytes, 6, 2)));
    }

    public function getTimeLow(): Hexadecimal
    {
        return new Hexadecimal(bin2hex(substr($this->bytes, 0, 4)));
    }

    public function getTimeMid(): Hexadecimal
    {
        return new Hexadecimal(bin2hex(substr($this->bytes, 4, 2)));
    }

    public function getTimestamp(): Hexadecimal
    {
        return new Hexadecimal(
            sprintf(
                '%03x%04s%08s',
                hexdec($this->getTimeHiAndVersion()->toString()) & 0x0fff,
                $this->getTimeMid()->toString(),
                $this->getTimeLow()->toString(),
            ),
        );
    }

    public function getVersion(): ?int
    {
        if ($this->isMax() || $this->isNil()) {
            return null;
        }

        $parts = unpack('n*', $this->bytes);

        return (int) $parts[4] >> 12;
    }

    private function isCorrectVariant(): bool
    {
        if ($this->isMax() || $this->isNil()) {
            return true;
        }

        return $this->getVariant() === Uuid::RFC_4122;
    }

    private function isCorrectVersion(): bool
    {
        if ($this->isMax() || $this->isNil()) {
            return true;
        }

        if ($this->getVersion() !== 0) {
            return false;
        }

        return true;
    }
}
