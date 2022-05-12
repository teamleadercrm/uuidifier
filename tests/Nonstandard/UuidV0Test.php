<?php

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Codec\StringCodec;
use Ramsey\Uuid\Converter\Number\GenericNumberConverter;
use Ramsey\Uuid\Converter\Time\GenericTimeConverter;
use Ramsey\Uuid\Math\BrickMathCalculator;
use Ramsey\Uuid\Rfc4122\FieldsInterface;
use Ramsey\Uuid\Type\Hexadecimal;
use Teamleader\Uuidifier\Builder\VersionZeroUuidBuilder;
use Teamleader\Uuidifier\Nonstandard\UuidV0;

class UuidV0Test extends TestCase
{
    /**
     * @test
     */
    public function itCanCreateAVersion0Uuid()
    {
        $calculator = new BrickMathCalculator();
        $versionZeroUuidBuilder = new VersionZeroUuidBuilder(
            new GenericNumberConverter($calculator),
            new GenericTimeConverter($calculator),
        );

        $instance = new UuidV0(
            new FakeVersionZeroFields(),
            new GenericNumberConverter($calculator),
            new StringCodec($versionZeroUuidBuilder),
            new GenericTimeConverter($calculator),
        );

        $this->assertInstanceOf(
            UuidV0::class,
            $instance,
        );
    }

    /**
     * @test
     */
    public function itThrowsWhenCreatingAVersion0UuidWithAnotherVersion()
    {
        $calculator = new BrickMathCalculator();
        $versionZeroUuidBuilder = new VersionZeroUuidBuilder(
            new GenericNumberConverter($calculator),
            new GenericTimeConverter($calculator),
        );

        $this->expectException(InvalidArgumentException::class);

        new UuidV0(
            new FakeVersionFourFields(),
            new GenericNumberConverter($calculator),
            new StringCodec($versionZeroUuidBuilder),
            new GenericTimeConverter($calculator),
        );
    }
}

abstract class FakeFields implements FieldsInterface
{
    public function getClockSeq(): Hexadecimal
    {
    }

    public function getClockSeqHiAndReserved(): Hexadecimal
    {
    }

    public function getClockSeqLow(): Hexadecimal
    {
    }

    public function getNode(): Hexadecimal
    {
    }

    public function getTimeHiAndVersion(): Hexadecimal
    {
    }

    public function getTimeLow(): Hexadecimal
    {
    }

    public function getTimeMid(): Hexadecimal
    {
    }

    public function getTimestamp(): Hexadecimal
    {
    }

    public function getVariant(): int
    {
    }

    abstract public function getVersion(): ?int;

    public function isNil(): bool
    {
    }

    public function getBytes(): string
    {
    }

    public function serialize()
    {
    }

    public function unserialize(string $data)
    {
    }

    public function __serialize(): array
    {
    }

    public function __unserialize(array $data): void
    {
    }
}

class FakeVersionFourFields extends FakeFields
{
    public function getVersion(): ?int
    {
        return 4;
    }
}

class FakeVersionZeroFields extends FakeFields
{
    public function getVersion(): ?int
    {
        return 0;
    }
}
