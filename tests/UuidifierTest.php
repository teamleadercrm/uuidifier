<?php

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use Teamleader\Uuidifier\Nonstandard\UuidV0;
use Teamleader\Uuidifier\Uuidifier;
use Ramsey\Uuid\Uuid;

class UuidifierTest extends TestCase
{
    /**
     * @test
     */
    public function itEncodesIds()
    {
        $generator = new Uuidifier();
        $uuid = $generator->encode('foo', 1);
        $this->assertInstanceOf(UuidInterface::class, $uuid);
    }

    /**
     * @test
     */
    public function itDecodesUuids()
    {
        $generator = new Uuidifier();

        for ($i = 0; $i < 100; $i++) {
            $id = rand(1, 10000000);
            $decoded = $generator->decode($generator->encode('foo', $id));
            $this->assertEquals($id, $decoded);
        }
    }

    /**
     * @test
     */
    public function itEmbedsTheVersion()
    {
        $generator = new Uuidifier();
        $uuid = $generator->encode('foo', 1);
        $this->assertEquals(0, $uuid->getVersion());
    }

    /**
     * @test
     */
    public function itGeneratesDifferentUuidsForDifferentPrefixes()
    {
        $generator = new Uuidifier();
        $uuid1 = $generator->encode('foo', 1);
        $uuid2 = $generator->encode('bar', 1);
        $this->assertNotEquals($uuid1->toString(), $uuid2->toString());
    }

    /**
     * @test
     */
    public function uuidWithCorrectPrefixIsValid()
    {
        $generator = new Uuidifier();
        $uuid = $generator->encode('foo', 1);
        $this->assertTrue($generator->isValid('foo', $uuid));
    }

    /**
     * @test
     */
    public function uuidWithDifferentPrefixIsInvalid()
    {
        $generator = new Uuidifier();
        $uuid = $generator->encode('foo', 1);
        $this->assertFalse($generator->isValid('bar', $uuid));
    }

    /**
     * @test
     */
    public function uuidWithInvalidVersionIsInvalid(): void
    {
        $generator = new Uuidifier();

        $uuid = Uuid::fromString('018dc552-ce8c-77ad-8c27-289ac479d815'); // V7 UUID

        Assert::assertFalse($generator->isValid('foo', $uuid));
    }

    /**
     * @test
     */
    public function uuidWithDifferentNumberIsInvalid()
    {
        $generator = new Uuidifier();
        $uuid1 = $generator->encode('foo', 1);
        $uuid2 = Uuid::fromString(rtrim($uuid1, '1') . '2');

        $this->assertNotEquals($uuid1, $uuid2);
        $this->assertTrue($generator->isValid('foo', $uuid1));
        $this->assertFalse($generator->isValid('foo', $uuid2));
    }

    private function providesPreviouslyGeneratedUuids(): array
    {
        return [
            ['foo', 1, '18a16d45-3076-0ef4-b311-d306c9f6c591'],
            ['foo', 2, 'aaadd949-77b8-0bf3-b61b-09fc3bbbc9e2'],
            ['foo', 3, 'fa129605-92c4-00f7-811c-2d4a31ee6523'],
            ['bar', 3, '89c76b7f-d66c-04b1-a511-c31d2482ef23'],
            ['bar', 4, 'c542f2cd-cfd5-0279-941b-2be43ee31c64'],
            ['foo', 42, 'af616ff7-e491-06bc-b62e-b9a8ca12fd2a'],
            ['foo', 999999999, '28bb05fc-9542-0c26-a384-86ae3b9ac9ff'],
        ];
    }

    /**
     * @test
     * @dataProvider providesPreviouslyGeneratedUuids
     */
    public function encodedUuidIsPermanentlyTheSame(string $prefix, int $id, string $expectedUuid)
    {
        $generator = new Uuidifier();
        $uuid = $generator->encode($prefix, $id);

        $this->assertEquals($expectedUuid, $uuid->toString());
    }

    private function providesPreviouslyGeneratedIds(): array
    {
        return [
            ['18a16d45-3076-0ef4-b311-d306c9f6c591', 1],
            ['aaadd949-77b8-0bf3-b61b-09fc3bbbc9e2', 2],
            ['fa129605-92c4-00f7-811c-2d4a31ee6523', 3],
            ['89c76b7f-d66c-04b1-a511-c31d2482ef23', 3],
            ['c542f2cd-cfd5-0279-941b-2be43ee31c64', 4],
            ['af616ff7-e491-06bc-b62e-b9a8ca12fd2a', 42],
            ['28bb05fc-9542-0c26-a384-86ae3b9ac9ff', 999999999],
        ];
    }

    /**
     * @test
     * @dataProvider providesPreviouslyGeneratedIds
     */
    public function decodedIdIsPermanentlyTheSame(string $uuid, int $expectedId)
    {
        $generator = new Uuidifier();
        $uuid = Uuid::fromString($uuid);
        $id = $generator->decode($uuid);

        $this->assertEquals($expectedId, $id);
    }

    /**
     * @test
     */
    public function testDecodingANonStandardUuidV0Works()
    {
        $generator = new Uuidifier();
        $uuid = UuidV0::fromString('af616ff7-e491-06bc-b62e-b9a8ca12fd2a');
        $id = $generator->decode($uuid);

        $this->assertEquals(42, $id);
    }

    /**
     * @test
     */
    public function testDecodingAStandardUuidWorks()
    {
        $generator = new Uuidifier();
        $uuid = Uuid::fromString('af616ff7-e491-06bc-b62e-b9a8ca12fd2a');
        $id = $generator->decode($uuid);

        $this->assertEquals(42, $id);
    }
}
