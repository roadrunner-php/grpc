<?php

/**
 * This file is part of RoadRunner package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

spl_autoload_register(static function (string $class) {
    if (strpos($class, 'Spiral\\GRPC') === 0) {
        $original = 'Spiral\\RoadRunner\\GRPC' . substr($class, 11);

        @trigger_error(
            "$class has been deprecated since spiral/roadrunner-grpc 2.0.0 " .
            "and will be removed in further release. Please use class $original instead.",
            \E_USER_DEPRECATED
        );

        class_alias($original, $class);
    }
});
