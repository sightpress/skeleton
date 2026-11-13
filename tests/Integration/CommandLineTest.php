<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * Runs bin/sightpress as a separate process.
 */
#[CoversNothing]
final class CommandLineTest extends TestCase
{
    public function testItListsTheCommands(): void
    {
        [$exitCode, $output] = $this->runCommand([]);

        self::assertSame(0, $exitCode);
        self::assertStringContainsString('site:create <name>', $output);
    }

    public function testCreatingASiteFailsUntilSitesExist(): void
    {
        [$exitCode, , $errorOutput] = $this->runCommand(['site:create', 'demo']);

        self::assertSame(1, $exitCode);
        self::assertStringContainsString('Can\'t create the site "demo" yet', $errorOutput);
    }

    /**
     * @param list<string> $arguments
     *
     * @return array{int, string, string}
     */
    private function runCommand(array $arguments): array
    {
        $root = dirname(__DIR__, 2);
        $process = proc_open(
            [PHP_BINARY, $root . '/bin/sightpress', ...$arguments],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $root,
        );
        self::assertIsResource($process);

        $output = (string) stream_get_contents($pipes[1]);
        $errorOutput = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return [proc_close($process), $output, $errorOutput];
    }
}
