<?php

declare(strict_types=1);

namespace Teamleader\Uuidifier\Builder;

use Ramsey\Uuid\Builder\UuidBuilderInterface;
use Ramsey\Uuid\Codec\CodecInterface;
use Ramsey\Uuid\Converter\NumberConverterInterface;
use Ramsey\Uuid\Converter\TimeConverterInterface;
use Ramsey\Uuid\Exception\UnableToBuildUuidException;
use Ramsey\Uuid\Exception\UnsupportedOperationException;
use Ramsey\Uuid\UuidInterface;
use Teamleader\Uuidifier\Nonstandard\UuidV0;
use Teamleader\Uuidifier\Nonstandard\VersionZeroFields;
use Throwable;

final class VersionZeroUuidBuilder implements UuidBuilderInterface
{
    private NumberConverterInterface $numberConverter;
    private TimeConverterInterface $timeConverter;

    public function __construct(
        NumberConverterInterface $numberConverter,
        TimeConverterInterface $timeConverter,
    ) {
        $this->timeConverter = $timeConverter;
        $this->numberConverter = $numberConverter;
    }

    public function build(CodecInterface $codec, string $bytes): UuidInterface
    {
        try {
            $fields = $this->buildFields($bytes);

            if ($fields->getVersion() !== 0) {
                throw new UnsupportedOperationException(
                    'The UUID version in the given fields is not supported by this UUID builder'
                );
            }

            return new UuidV0(
                $fields,
                $this->numberConverter,
                $codec,
                $this->timeConverter,
            );
        } catch (Throwable $exception) {
            throw new UnableToBuildUuidException(
                $exception->getMessage(),
                (int) $exception->getCode(),
                $exception,
            );
        }
    }

    /**
     * Proxy method to allow injecting a mock, for testing
     */
    protected function buildFields(string $bytes): VersionZeroFields
    {
        return new VersionZeroFields($bytes);
    }
}
