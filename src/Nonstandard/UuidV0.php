<?php

declare(strict_types=1);

namespace Teamleader\Uuidifier\Nonstandard;

use Ramsey\Uuid\Codec\CodecInterface;
use Ramsey\Uuid\Codec\StringCodec;
use Ramsey\Uuid\Converter\Number\GenericNumberConverter;
use Ramsey\Uuid\Converter\NumberConverterInterface;
use Ramsey\Uuid\Converter\Time\GenericTimeConverter;
use Ramsey\Uuid\Converter\TimeConverterInterface;
use Ramsey\Uuid\Exception\InvalidArgumentException;
use Ramsey\Uuid\Math\BrickMathCalculator;
use Ramsey\Uuid\Rfc4122\FieldsInterface as Rfc4122FieldsInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidFactoryInterface;
use Ramsey\Uuid\UuidInterface;
use Teamleader\Uuidifier\Builder\VersionZeroUuidBuilder;

final class UuidV0 extends Uuid
{
    public function __construct(
        Rfc4122FieldsInterface $fields,
        NumberConverterInterface $numberConverter,
        CodecInterface $codec,
        TimeConverterInterface $timeConverter
    ) {
        if ($fields->getVersion() !== 0) {
            throw new InvalidArgumentException(
                'Fields used to create a UuidV0 must represent a version 0 (incremental) UUID',
            );
        }

        parent::__construct($fields, $numberConverter, $codec, $timeConverter);
    }

    /**
     * Override this method to avoid returning a LazyUuidFromString
     */
    public static function fromString(string $uuid): UuidInterface
    {
        $uuid = strtolower($uuid);

        return self::getFactory()->fromString($uuid);
    }

    /**
     * Override this method to avoid returning a Ramsey\Uuid\UuidFactory
     */
    public static function getFactory(): UuidFactoryInterface
    {
        $uuidFactory = new UuidFactory();

        $calculator = new BrickMathCalculator();
        $versionZeroUuidBuilder = new VersionZeroUuidBuilder(
            new GenericNumberConverter($calculator),
            new GenericTimeConverter($calculator),
        );

        $uuidFactory->setCodec(new StringCodec($versionZeroUuidBuilder));
        $uuidFactory->setUuidBuilder($versionZeroUuidBuilder);

        return $uuidFactory;
    }
}
