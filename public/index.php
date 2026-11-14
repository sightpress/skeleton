<?php

declare(strict_types=1);

use SightPress\Core\Kernel;

require dirname(__DIR__) . '/vendor/autoload.php';

Kernel::serve(dirname(__DIR__));
