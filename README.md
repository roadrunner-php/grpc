<a href="https://roadrunner.dev" target="_blank">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://github.com/roadrunner-server/.github/assets/8040338/e6bde856-4ec6-4a52-bd5b-bfe78736c1ff">
    <img align="center" src="https://github.com/roadrunner-server/.github/assets/8040338/040fb694-1dd3-4865-9d29-8e0748c2c8b8">
  </picture>
</a>

# RoadRunner GRPC Plugin

[![Latest Stable Version](https://poser.pugx.org/spiral/roadrunner-grpc/version)](https://packagist.org/packages/spiral/roadrunner-grpc)
[![Codecov](https://codecov.io/gh/roadrunner-php/grpc/branch/3.x/graph/badge.svg)](https://codecov.io/gh/roadrunner-php/grpc/)

RoadRunner GRPC is an open-source (MIT) high-performance PHP [GRPC](https://grpc.io/) server build on top
of [RoadRunner](https://github.com/roadrunner-server/roadrunner). Server support both PHP and Golang services running within one
application.

## Features

- native Golang GRPC implementation compliant
- minimal configuration, plug-and-play model
- very fast, low footprint proxy
- simple TLS configuration
- debug tools included
- Prometheus metrics
- middleware and server customization support
- code generation using `protoc` plugin (Plugin can be downloaded from the
  roadrunner [releases page](https://github.com/roadrunner-server/roadrunner/releases))
- transport, message, worker error management
- response error codes over php exceptions
- works on Windows

## Documentation

You can find more information about RoadRunner GRPC plugin in the [official documentation](https://docs.roadrunner.dev/plugins/grpc).

## Example

You can find example of GRPC application in [example](./example/echo) directory.

<a href="https://spiral.dev/">
<img src="https://user-images.githubusercontent.com/773481/220979012-e67b74b5-3db1-41b7-bdb0-8a042587dedc.jpg" alt="try Spiral Framework" />
</a>

License:
--------
MIT License (MIT). Please see [`LICENSE`](./LICENSE) for more information. Maintained
by [SpiralScout](https://spiralscout.com).
