<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC\Exception;

use Google\Protobuf\Internal\Message;
use JetBrains\PhpStorm\ExpectedValues;
use Spiral\RoadRunner\GRPC\StatusCode;

/**
 * @psalm-import-type StatusCodeType from StatusCode
 */
class GRPCException extends \RuntimeException implements MutableGRPCExceptionInterface
{
    /**
     * Can be overridden by child classes.
     *
     * @psalm-var StatusCodeType
     * @var int
     */
    protected const CODE = StatusCode::UNKNOWN;

    /**
     * @param StatusCodeType $code
     * @param Message[] $details Collection of protobuf messages for describing error which will be converted to
     * google.protobuf. Any during sending as response. {@see https://cloud.google.com/apis/design/errors}
     */
    final public function __construct(
        string $message = '',
        ?int $code = null,
        private array $details = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code ?? static::CODE, $previous);
    }

    /**
     * @param StatusCodeType $code
     * @param Message[] $details
     * @return static
     */
    public static function create(
        string $message,
        #[ExpectedValues(valuesFromClass: StatusCode::class)]
        int $code = self::CODE,
        ?\Throwable $previous = null,
        array $details = [],
    ): self {
        return new static($message, $code, $details, $previous);
    }

    /**
     * @return Message[]
     */
    #[\Override]
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * @param Message[] $details
     */
    #[\Override]
    public function setDetails(array $details): void
    {
        $this->details = $details;
    }

    #[\Override]
    public function addDetails(Message $message): void
    {
        $this->details[] = $message;
    }
}
