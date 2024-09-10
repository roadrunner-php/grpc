<?php

declare(strict_types=1);

namespace Spiral\RoadRunner\GRPC;

use Google\Protobuf\Any;
use Google\Rpc\Status;
use Spiral\RoadRunner\GRPC\Exception\GRPCException;
use Spiral\RoadRunner\GRPC\Exception\GRPCExceptionInterface;
use Spiral\RoadRunner\GRPC\Exception\NotFoundException;
use Spiral\RoadRunner\GRPC\Exception\ServiceException;
use Spiral\RoadRunner\GRPC\Internal\CallContext;
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
    ) {}

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
     * Serve GRPC over given RoadRunner worker.
     */
    public function serve(?WorkerInterface $worker = null, ?callable $finalize = null): void
    {
        $worker ??= Worker::create();

        while (true) {
            $e = null;
            $request = $worker->waitPayload();

            if ($request === null) {
                return;
            }

            $responseHeaders = new ResponseHeaders();
            $responseTrailers = new ResponseTrailers();

            try {
                $call = CallContext::decode($request->header);

                $context = new Context(array_merge(
                    $call->context,
                    [
                        ResponseHeaders::class => $responseHeaders,
                        ResponseTrailers::class => $responseTrailers
                    ]
                ));

                $response = $this->invoke($call->service, $call->method, $context, $request->body);

                $this->workerSend(
                    worker: $worker,
                    body: $response,
                    headers: Json::encode([
                        'headers' => $responseHeaders->packHeaders(),
                        'trailers' => $responseTrailers->packTrailers(),
                    ]),
                );
            } catch (GRPCExceptionInterface $e) {
                $this->workerSend(
                    worker: $worker,
                    body: '',
                    headers: Json::encode([
                        'error' => $this->createGrpcError($e),
                        'headers' => $responseHeaders->packHeaders(),
                        'trailers' => $responseTrailers->packTrailers(),
                    ]),
                );
            } catch (\Throwable $e) {
                $this->workerError($worker, $this->isDebugMode() ? (string) $e : $e->getMessage());
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

    private function workerError(WorkerInterface $worker, string $message): void
    {
        $worker->error($message);
    }

    /**
     * @psalm-suppress InaccessibleMethod
     */
    private function workerSend(WorkerInterface $worker, string $body, string $headers): void
    {
        $worker->respond(new Payload($body, $headers));
    }

    private function createGrpcError(GRPCExceptionInterface $e): string
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

        return \base64_encode($status->serializeToString());
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
