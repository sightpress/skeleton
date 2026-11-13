<?php

declare(strict_types=1);

use App\Http\NoSiteConfiguredPage;

require dirname(__DIR__) . '/vendor/autoload.php';

date_default_timezone_set('UTC');

// No site can be resolved yet, so every request gets the "no site configured" page.
$environment = getenv('APP_ENV');
$page = new NoSiteConfiguredPage(is_string($environment) && $environment !== '' ? $environment : 'production');

http_response_code($page->statusCode());

foreach ($page->headers() as $name => $value) {
    header($name . ': ' . $value);
}

echo $page->body();
