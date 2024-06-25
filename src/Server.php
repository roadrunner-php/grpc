<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC;

use Google\Protobuf\Any;
use Google\Rpc\Status;
use Spiral\RoadRunner\GRPC\Exception\GRPCException;
use Spiral\RoadRunner\GRPC\Exception\GRPCExceptionInterface;
use Spiral\RoadRunner\GRPC\Exception\NotFoundException;
use Spiral\RoadRunner\GRPC\Exception\ServiceException;
use Spiral\RoadRunner\GRPC\Internal\Json;
use Spiral\RoadRunner\Payload;
use Spiral\RoadRunner\Worker;
use Spiral\RoadRunner\WorkerInterface;

/**
 * Manages group of services and communication with RoadRunner server.
 *
 * @psalm-type ServerOptions = array{
 *  debug?: bool
 * }
 *
 * @psalm-type ContextResponse = array{
 *  service: class-string<ServiceInterface>,
 *  method:  non-empty-string,
 *  context: array<string, array<string>>
 * }
 */
final class Server
{
    /** @var ServiceWrapper[] */
    private array $services = [];

    /**
     * @param ServerOptions $options
     */
    public function __construct(
        private readonly InvokerInterface $invoker = new Invoker(),
        private readonly array $options = [],
    ) {
    }

    /**
     * Register new GRPC service.
     *
     * For example:
     * <code>
     *  $server->registerService(EchoServiceInterface::class, new EchoService());
     * </code>
     *
     * @template T of ServiceInterface
     *
     * @param class-string<T> $interface Generated service interface.
     * @param T $service Must implement interface.
     * @throws ServiceException
     */
    public function registerService(string $interface, ServiceInterface $service): void
    {
        $service = new ServiceWrapper($this->invoker, $interface, $service);

        $this->services[$service->getName()] = $service;
    }

    /**
     * @param ContextResponse $data
     * @return array{0: string, 1: string}
     * @throws \JsonException
     * @throws \Throwable
     */
    private function tick(string $body, array $data): array
    {
        $context = (new Context($data['context']))
            ->withValue(ResponseHeaders::class, new ResponseHeaders());

        $response = $this->invoke($data['service'], $data['method'], $context, $body);

        /** @var ResponseHeaders|null $responseHeaders */
        $responseHeaders = $context->getValue(ResponseHeaders::class);
        $responseHeadersString = $responseHeaders ? $responseHeaders->packHeaders() : '{}';

        return [$response, $responseHeadersString];
    }

    /**
     * @psalm-suppress InaccessibleMethod
     */
    private function workerSend(WorkerInterface $worker, string $body, string $headers): void
    {
        $worker->respond(new Payload($body, $headers));
    }

    private function workerError(WorkerInterface $worker, string $message): void
    {
        $worker->error($message);
    }

    /**
     * Serve GRPC over given RoadRunner worker.
     */
    public function serve(WorkerInterface $worker = null, callable $finalize = null): void
    {
        $worker ??= Worker::create();

        while (true) {
            $e = null;
            $request = $worker->waitPayload();

            if ($request === null) {
                return;
            }

            try {
                /** @var ContextResponse $context */
                $context = Json::decode($request->header);

                [$answerBody, $answerHeaders] = $this->tick($request->body, $context);

                $this->workerSend($worker, $answerBody, $answerHeaders);
            } catch (GRPCExceptionInterface $e) {
                $this->workerGrpcError($worker, $e);
            } catch (\Throwable $e) {
                $this->workerError($worker, $this->isDebugMode() ? (string)$e : $e->getMessage());
            } finally {
                if ($finalize !== null) {
                    isset($e) ? $finalize($e) : $finalize();
                }
            }
        }
    }

    /**
     * Invoke service method with binary payload and return the response.
     *
     * @param class-string<ServiceInterface> $service
     * @param non-empty-string $method
     * @throws GRPCException
     */
    protected function invoke(string $service, string $method, ContextInterface $context, string $body): string
    {
        if (!isset($this->services[$service])) {
            throw NotFoundException::create("Service `{$service}` not found.", StatusCode::NOT_FOUND);
        }

        return $this->services[$service]->invoke($method, $context, $body);
    }

    private function workerGrpcError(WorkerInterface $worker, GRPCExceptionInterface $e): void
    {
        $status = new Status([
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'details' => \array_map(
                static function ($detail) {
                    $message = new Any();
                    $message->pack($detail);

                    return $message;
                },
                $e->getDetails(),
            ),
        ]);

        $this->workerSend(
            $worker,
            '',
            Json::encode([
                'error' => \base64_encode($status->serializeToString()),
            ]),
        );
    }

    /**
     * Checks if debug mode is enabled.
     */
    private function isDebugMode(): bool
    {
        $debug = false;

        if (isset($this->options['debug'])) {
            $debug = \filter_var($this->options['debug'], \FILTER_VALIDATE_BOOLEAN);
        }

        return $debug;
    }
}
