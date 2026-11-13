<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Serves public/index.php with PHP's built-in web server and checks the real HTTP response.
 */
#[CoversNothing]
final class FrontControllerTest extends TestCase
{
    /** @var resource|null */
    private mixed $server = null;

    private int $port = 0;

    protected function tearDown(): void
    {
        if (is_resource($this->server)) {
            proc_terminate($this->server);
            proc_close($this->server);
        }
    }

    public function testEveryPathGetsTheNoSiteConfiguredPage(): void
    {
        $this->startServer(['APP_ENV' => 'production']);

        foreach (['/', '/about', '/admin?x=1'] as $path) {
            [$status, $headers, $body] = $this->get($path);

            self::assertSame(404, $status, $path);
            self::assertContains('content-type: text/html; charset=utf-8', $headers, $path);
            self::assertNotContains('x-robots-tag: noindex, nofollow', $headers, $path);
            self::assertStringContainsString('<html lang="en-GB">', $body, $path);
            self::assertStringContainsString('<h1>No site configured</h1>', $body, $path);
        }
    }

    public function testNonProductionEnvironmentsAreNotIndexed(): void
    {
        $this->startServer(['APP_ENV' => 'dev']);

        [$status, $headers] = $this->get('/');

        self::assertSame(404, $status);
        self::assertContains('x-robots-tag: noindex, nofollow', $headers);
    }

    public function testAMissingEnvironmentCountsAsProduction(): void
    {
        $this->startServer(['APP_ENV' => '']);

        [, $headers] = $this->get('/');

        self::assertNotContains('x-robots-tag: noindex, nofollow', $headers);
    }

    /**
     * @param array<string, string> $environment
     */
    private function startServer(array $environment): void
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        self::assertIsResource($socket);
        $address = (string) stream_socket_get_name($socket, false);
        fclose($socket);
        $this->port = (int) substr($address, (int) strrpos($address, ':') + 1);

        $root = dirname(__DIR__, 2);
        $process = proc_open(
            [PHP_BINARY, '-S', '127.0.0.1:' . $this->port, '-t', $root . '/public', $root . '/public/index.php'],
            [0 => ['pipe', 'r'], 1 => ['file', '/dev/null', 'w'], 2 => ['file', '/dev/null', 'w']],
            $pipes,
            $root,
            $environment + ['PATH' => (string) getenv('PATH')],
        );
        self::assertIsResource($process);
        $this->server = $process;

        for ($attempt = 0; $attempt < 100; ++$attempt) {
            $connection = @fsockopen('127.0.0.1', $this->port);
            if (is_resource($connection)) {
                fclose($connection);

                return;
            }
            usleep(50_000);
        }

        throw new RuntimeException('The built-in web server did not start.');
    }

    /**
     * @return array{int, list<string>, string}
     */
    private function get(string $path): array
    {
        $context = stream_context_create(['http' => ['ignore_errors' => true, 'timeout' => 5]]);
        $body = file_get_contents('http://127.0.0.1:' . $this->port . $path, false, $context);
        self::assertIsString($body);

        $headers = http_get_last_response_headers() ?? [];
        self::assertSame(1, preg_match('#^HTTP/\S+ (\d{3})#', $headers[0] ?? '', $status));

        return [(int) $status[1], array_map(strtolower(...), array_slice($headers, 1)), $body];
    }
}
