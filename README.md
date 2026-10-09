<p align="center">
    <a href="https://roadrunner.dev"><picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://github.com/roadrunner-server/.github/assets/8040338/e6bde856-4ec6-4a52-bd5b-bfe78736c1ff">
        <img alt="RoadRunner" src="https://github.com/roadrunner-server/.github/assets/8040338/040fb694-1dd3-4865-9d29-8e0748c2c8b8" style="width: 6in; display: block">
    </picture></a>
</p>

<p align="center">High-performance gRPC server for PHP applications</p>

<div align="center">

[![Documentation](https://img.shields.io/badge/Documentation-blue?style=for-the-badge&logo=gitbook&logoColor=white)](https://docs.roadrunner.dev/docs/grpc/grpc)
[![Sponsor](https://img.shields.io/static/v1?style=for-the-badge&label=&message=Sponsor&logo=githubsponsors&logoColor=white&color=%23EA4AAA)](https://github.com/sponsors/roadrunner-server)

[![Psalm Level](https://shepherd.dev/github/roadrunner-php/grpc/level.svg)](https://shepherd.dev/github/roadrunner-php/grpc)
[![Type Coverage](https://shepherd.dev/github/roadrunner-php/grpc/coverage.svg)](https://shepherd.dev/github/roadrunner-php/grpc)
[![Codecov](https://codecov.io/gh/roadrunner-php/grpc/branch/3.x/graph/badge.svg)](https://codecov.io/gh/roadrunner-php/grpc/)
[![Mutation testing badge](https://img.shields.io/endpoint?url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Froadrunner-php%2Fgrpc%2F3.x)](https://dashboard.stryker-mutator.io/reports/github.com/roadrunner-php/grpc/3.x)

</div>

<br />

RoadRunner GRPC is the PHP side of the [RoadRunner](https://github.com/roadrunner-server/roadrunner) [gRPC](https://grpc.io/) plugin.
It runs your PHP gRPC services inside RoadRunner workers, so PHP and Golang services can live within one application.

## Get Started

### Installation

```bash
composer require spiral/roadrunner-grpc
```

[![PHP](https://img.shields.io/packagist/php-v/spiral/roadrunner-grpc.svg?style=flat-square&logo=php)](https://packagist.org/packages/spiral/roadrunner-grpc)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/spiral/roadrunner-grpc.svg?style=flat-square&logo=packagist)](https://packagist.org/packages/spiral/roadrunner-grpc)
[![License](https://img.shields.io/packagist/l/spiral/roadrunner-grpc.svg?style=flat-square)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/spiral/roadrunner-grpc.svg?style=flat-square)](https://packagist.org/packages/spiral/roadrunner-grpc/stats)

### Configuration

Enable the `grpc` plugin in `.rr.yaml` and point it at your `.proto` files:

```yaml
version: "3"

server:
  command: "php worker.php"

grpc:
  listen: "tcp://127.0.0.1:9001"
  proto: ["service.proto"]
```

All available options are described in the [plugin documentation](https://docs.roadrunner.dev/docs/grpc/grpc).

### Generating Service Code

Service interfaces and messages are generated from `.proto` files with `protoc` and the `protoc-gen-php-grpc` plugin,
which can be downloaded from the RoadRunner [releases page](https://github.com/roadrunner-server/roadrunner/releases):

```bash
protoc --php_out=src --php-grpc_out=src service.proto
```

### Writing a Service

Implement the generated interface:

```php
use Service\EchoInterface;
use Service\Message;
use Spiral\RoadRunner\GRPC\ContextInterface;

final class EchoService implements EchoInterface
{
    public function Ping(ContextInterface $ctx, Message $in): Message
    {
        return (new Message())->setMsg(\date('Y-m-d H:i:s') . ': PONG');
    }
}
```

### Running the Worker

Register the service in `worker.php` and start serving requests:

```php
use Service\EchoInterface;
use Spiral\RoadRunner\GRPC\Invoker;
use Spiral\RoadRunner\GRPC\Server;
use Spiral\RoadRunner\Worker;

require __DIR__ . '/vendor/autoload.php';

$server = new Server(new Invoker(), [
    'debug' => false, // optional (default: false)
]);

$server->registerService(EchoInterface::class, new EchoService());

$server->serve(Worker::create());
```

Then run `rr serve`. A complete echo service with a client is in the [example](./example/echo) directory.

## Features

- native Golang GRPC implementation compliant
- minimal configuration, plug-and-play model
- very fast, low footprint proxy
- simple TLS configuration
- debug tools included
- Prometheus metrics
- middleware and server customization support
- code generation using `protoc` plugin
- transport, message, worker error management
- response error codes over php exceptions
- works on Windows

<a href="https://spiral.dev/">
<img src="https://user-images.githubusercontent.com/773481/220979012-e67b74b5-3db1-41b7-bdb0-8a042587dedc.jpg" alt="try Spiral Framework" />
</a>
