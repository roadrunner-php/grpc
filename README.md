# RoadRunner GRPC Plugin

[![Latest Stable Version](https://poser.pugx.org/spiral/roadrunner-grpc/version)](https://packagist.org/packages/spiral/roadrunner-grpc)
[![build](https://github.com/spiral/roadrunner-grpc/actions/workflows/ci-build.yml/badge.svg)](https://github.com/spiral/roadrunner-grpc/actions/workflows/ci-build.yml)
[![Codecov](https://codecov.io/gh/spiral/roadrunner-grpc/branch/master/graph/badge.svg)](https://codecov.io/gh/spiral/roadrunner-grpc/)

RoadRunner GRPC is an open-source (MIT) high-performance PHP [GRPC](https://grpc.io/) server build at top
of [RoadRunner](https://github.com/spiral/roadrunner). Server support both PHP and Golang services running within one
application.

Documentation:
--------

* [Installation and Configuration](https://spiral.dev/docs/grpc-configuration)
* [Service Code](https://spiral.dev/docs/grpc-service)
* [Client SDK](https://spiral.dev/docs/grpc-client)
* [Golang Services](https://spiral.dev/docs/grpc-golang)
* [Data Streaming](https://spiral.dev/docs/grpc-streaming)

Features:
--------

- native Golang GRPC implementation compliant
- minimal configuration, plug-and-play model
- very fast, low footprint proxy
- simple TLS configuration
- debug tools included
- Prometheus metrics
- middleware and server customization support
- code generation using `protoc` plugin (Plugin can be downloaded from the
  roadrunner-binary [releases page](https://github.com/spiral/roadrunner-binary/releases))
- transport, message, worker error management
- response error codes over php exceptions
- works on Windows

Usage:
--------
Install `rr` and `protoc-gen-php-grpc` by building it or
use [pre-build binaries](https://github.com/spiral/roadrunner-binary/releases).

Define your service schema using proto file. You can scaffold protobuf classes and
GRPC [service interfaces](https://github.com/spiral/roadrunner-grpc/blob/master/example/echo/src/Service/EchoInterface.php)
using:

```
$ protoc --php_out=target-dir/ --php-grpc_out=target-dir/ sample.proto
```

> Make sure to install [protoc compiler](https://github.com/protocolbuffers/protobuf) and
> run `composer require spiral/roadrunner-grpc` first

[Implement](https://github.com/spiral/roadrunner-grpc/blob/master/example/echo/src/EchoService.php) needed classes and
create [worker.php](https://github.com/spiral/roadrunner-grpc/blob/master/example/echo/worker.php) to invoke your
services.

Install [rr](https://roadrunner.dev/docs/intro-install) and
place [.rr.yaml](https://github.com/spiral/roadrunner-grpc/blob/master/example/echo/.rr.yaml) (or any other format
supported by viper configurator) into the root of your project. You can run your application now:

```
$ rr serve
```

> See [example](https://github.com/spiral/roadrunner-grpc/blob/master/example).

You can find more details regarding server configuration at [RoadRunner Wiki](https://roadrunner.dev/docs).

License:
--------
MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information. Maintained
by [SpiralScout](https://spiralscout.com).
