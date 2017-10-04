<?php

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use Teamleader\Uuidifier\Uuidifier;
use Ramsey\Uuid\Uuid;

class UudifierTest extends TestCase
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
        $generator = new Uuidifier(9);
        $uuid = $generator->encode('foo', 1);
        $this->assertEquals(9, $uuid->getVersion());
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
    public function uuidWithDifferentNumberIsInvalid()
    {
        $generator = new Uuidifier();
        $uuid1 = $generator->encode('foo', 1);
        $uuid2 = Uuid::fromString(rtrim($uuid1, '1') . '2');

        $this->assertNotEquals($uuid1, $uuid2);
        $this->assertTrue($generator->isValid('foo', $uuid1));
        $this->assertFalse($generator->isValid('foo', $uuid2));
    }
}
