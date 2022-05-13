<?php

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Type\Hexadecimal;
use Teamleader\Uuidifier\Nonstandard\VersionZeroFields;

class VersionZeroFieldsTest extends TestCase
{
    /**
     * @test
     */
    public function testConstructorThrowsExceptionIfNotSixteenByteString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The byte string must be 16 bytes long; received 6 bytes'
        );

        new VersionZeroFields('foobar');
    }

    /**
     * @test
     * @dataProvider nonRfc4122VariantProvider
     */
    public function testConstructorThrowsExceptionIfNotRfc4122Variant(string $uuid): void
    {
        $bytes = (string) hex2bin(str_replace('-', '', $uuid));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The byte string received does not conform to the RFC 4122 variant'
        );

        new VersionZeroFields($bytes);
    }

    public function nonRfc4122VariantProvider(): array
    {
        return [
            ['ff6f8cb0-c57d-11e1-0b21-0800200c9a66'],
            ['ff6f8cb0-c57d-11e1-1b21-0800200c9a66'],
            ['ff6f8cb0-c57d-11e1-2b21-0800200c9a66'],
            ['ff6f8cb0-c57d-11e1-3b21-0800200c9a66'],
            ['ff6f8cb0-c57d-11e1-4b21-0800200c9a66'],
            ['ff6f8cb0-c57d-11e1-5b21-0800200c9a66'],
            ['ff6f8cb0-c57d-11e1-6b21-0800200c9a66'],
            ['ff6f8cb0-c57d-11e1-7b21-0800200c9a66'],
            ['ff6f8cb0-c57d-11e1-cb21-0800200c9a66'],
            ['ff6f8cb0-c57d-11e1-db21-0800200c9a66'],
            ['ff6f8cb0-c57d-11e1-eb21-0800200c9a66'],
            ['ff6f8cb0-c57d-11e1-fb21-0800200c9a66'],
        ];
    }

    /**
     * @test
     * @dataProvider invalidVersionProvider
     */
    public function testConstructorThrowsExceptionIfInvalidVersion(string $uuid): void
    {
        $bytes = (string) hex2bin(str_replace('-', '', $uuid));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The byte string received does not contain a valid version'
        );

        new VersionZeroFields($bytes);
    }

    public function invalidVersionProvider(): array
    {
        return [
            '1' => ['ff6f8cb0-c57d-11e1-9b21-0800200c9a66'],
            '2' => ['ff6f8cb0-c57d-21ea-ab21-0800200c9a66'],
            '3' => ['ff6f8cb0-c57d-31e1-bb21-0800200c9a66'],
            '4' => ['ff6f8cb0-c57d-41e1-ab21-0800200c9a66'],
            '5' => ['ff6f8cb0-c57d-51e1-8b21-0800200c9a66'],
            '6' => ['ff6f8cb0-c57d-61e1-8b21-0800200c9a66'],
            '7' => ['ff6f8cb0-c57d-71e1-9b21-0800200c9a66'],
            '8' => ['ff6f8cb0-c57d-81e1-ab21-0800200c9a66'],
            '9' => ['ff6f8cb0-c57d-91e1-bb21-0800200c9a66'],
        ];
    }

    /**
     * @test
     * @dataProvider fieldGetterMethodProvider
     */
    public function testFieldGetterMethods(string $uuid, string $methodName, mixed $expectedValue): void
    {
        $bytes = (string) hex2bin(str_replace('-', '', $uuid));
        $fields = new VersionZeroFields($bytes);

        $result = $fields->$methodName();

        if ($result instanceof Hexadecimal) {
            $this->assertSame($expectedValue, $result->toString());
        } else {
            $this->assertSame($expectedValue, $result);
        }
    }

    public function fieldGetterMethodProvider(): array
    {
        return [
            ['ff6f8cb0-c57d-01e1-8b21-0800200c9a66', 'getClockSeq', '0b21'],
            ['ff6f8cb0-c57d-01e1-8b21-0800200c9a66', 'getClockSeqHiAndReserved', '8b'],
            ['ff6f8cb0-c57d-01e1-8b21-0800200c9a66', 'getClockSeqLow', '21'],
            ['ff6f8cb0-c57d-01e1-8b21-0800200c9a66', 'getNode', '0800200c9a66'],
            ['ff6f8cb0-c57d-01e1-8b21-0800200c9a66', 'getTimeHiAndVersion', '01e1'],
            ['ff6f8cb0-c57d-01e1-8b21-0800200c9a66', 'getTimeLow', 'ff6f8cb0'],
            ['ff6f8cb0-c57d-01e1-8b21-0800200c9a66', 'getTimeMid', 'c57d'],
            ['ff6f8cb0-c57d-01e1-8b21-0800200c9a66', 'getTimestamp', '1e1c57dff6f8cb0'],
            ['ff6f8cb0-c57d-01e1-8b21-0800200c9a66', 'getVariant', 2],
            ['ff6f8cb0-c57d-01e1-8b21-0800200c9a66', 'getVersion', 0],
            ['ff6f8cb0-c57d-01e1-8b21-0800200c9a66', 'isNil', false],
        ];
    }

    /**
     * @test
     */
    public function testSerializingFields(): void
    {
        $bytes = (string) hex2bin(str_replace('-', '', 'ff6f8cb0-c57d-01e1-8b21-0800200c9a66'));
        $fields = new VersionZeroFields($bytes);

        $serializedFields = serialize($fields);
        $unserializedFields = unserialize($serializedFields);

        $this->assertSame($fields->getBytes(), $unserializedFields->getBytes());
    }
}
