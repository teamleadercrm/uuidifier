<?php

namespace Builder;

use Ramsey\Uuid\Codec\StringCodec;
use Ramsey\Uuid\Converter\Number\GenericNumberConverter;
use Ramsey\Uuid\Converter\Time\GenericTimeConverter;
use Ramsey\Uuid\Exception\UnableToBuildUuidException;
use Ramsey\Uuid\Math\BrickMathCalculator;
use Ramsey\Uuid\UuidInterface;
use Teamleader\Uuidifier\Builder\VersionZeroUuidBuilder;
use PHPUnit\Framework\TestCase;

final class VersionZeroUuidBuilderTest extends TestCase
{
    /**
     * @test
     */
    public function testBuildCreatesUuidV0(): void
    {
        $builder = $this->createBuilder();
        $codec = new StringCodec($builder);

        $fields = [
            'time_low' => 'ff6f8cb0',
            'time_mid' => 'c57d',
            'time_hi_and_version' => '01e1',
            'clock_seq_hi_and_reserved' => '8b',
            'clock_seq_low' => '21',
            'node' => '0800200c9a66',
        ];

        $bytes = (string) hex2bin(implode('', $fields));

        $result = $builder->build($codec, $bytes);
        $this->assertInstanceOf(UuidInterface::class, $result);
    }

    /**
     * @test
     */
    public function testBuildFailsToCreateFromInvalidUuidVersion(): void
    {
        $builder = $this->createBuilder();
        $codec = new StringCodec($builder);

        $fields = [
            'time_low' => '754cd475',
            'time_mid' => '7e58',
            'time_hi_and_version' => '4411',
            'clock_seq_hi_and_reserved' => '93',
            'clock_seq_low' => '22',
            'node' => 'be0725c8ce01',
        ];

        $bytes = (string) hex2bin(implode('', $fields));

        $this->expectException(UnableToBuildUuidException::class);

        $builder->build($codec, $bytes);
    }

    private function createBuilder(): VersionZeroUuidBuilder
    {
        $calculator = new BrickMathCalculator();

        return new VersionZeroUuidBuilder(
            new GenericNumberConverter($calculator),
            new GenericTimeConverter($calculator),
        );
    }
}
