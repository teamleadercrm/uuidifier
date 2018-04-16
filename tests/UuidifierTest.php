<?php

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use Teamleader\Uuidifier\Uuidifier;
use Ramsey\Uuid\Uuid;

class UuidifierTest extends TestCase
{
    public function testItEncodesIds()
    {
        $generator = new Uuidifier();
        $uuid = $generator->encode('foo', 1);
        $this->assertInstanceOf(UuidInterface::class, $uuid);
    }

    public function testEncodeOnInvalidId()
    {
        $this->expectException('InvalidArgumentException');
        $generator = new Uuidifier();
        $uuid = $generator->encode('foo', 'invalid_id');
    }

    public function testItDecodesUuids()
    {
        $generator = new Uuidifier();

        for ($i = 0; $i < 100; $i++) {
            $id = rand(1, 10000000);
            $decoded = $generator->decode($generator->encode('foo', $id));
            $this->assertEquals($id, $decoded);
        }
    }

    public function testItDecodeOnInvalidVersion()
    {
        $this->expectException('InvalidArgumentException');
        $generator = new Uuidifier(10000);
        $generator->decode($generator->encode('foo', 1));
    }

    public function testItEmbedsTheVersion()
    {
        $generator = new Uuidifier(9);
        $uuid = $generator->encode('foo', 1);
        $this->assertEquals(9, $uuid->getVersion());
    }

    public function testItGeneratesDifferentUuidsForDifferentPrefixes()
    {
        $generator = new Uuidifier();
        $uuid1 = $generator->encode('foo', 1);
        $uuid2 = $generator->encode('bar', 1);
        $this->assertNotEquals($uuid1->toString(), $uuid2->toString());
    }

    public function testUuidWithCorrectPrefixIsValid()
    {
        $generator = new Uuidifier();
        $uuid = $generator->encode('foo', 1);
        $this->assertTrue($generator->isValid('foo', $uuid));
    }

    public function testUuidWithDifferentPrefixIsInvalid()
    {
        $generator = new Uuidifier();
        $uuid = $generator->encode('foo', 1);
        $this->assertFalse($generator->isValid('bar', $uuid));
    }

    public function testUuidWithDifferentNumberIsInvalid()
    {
        $generator = new Uuidifier();
        $uuid1 = $generator->encode('foo', 1);
        $uuid2 = Uuid::fromString(rtrim($uuid1, '1') . '2');

        $this->assertNotEquals($uuid1, $uuid2);
        $this->assertTrue($generator->isValid('foo', $uuid1));
        $this->assertFalse($generator->isValid('foo', $uuid2));
    }
}
