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
    public function testItListsCoresCommands(): void
    {
        [$exitCode, $output] = $this->runCommand(['list']);

        self::assertSame(0, $exitCode);
        foreach (['site:create', 'site:list', 'site:enable', 'site:disable', 'site:archive', 'site:delete', 'site:purge', 'site:migrate'] as $command) {
            self::assertStringContainsString($command, $output);
        }
    }

    public function testItListsTheSites(): void
    {
        [$exitCode, $output] = $this->runCommand(['site:list']);

        self::assertSame(0, $exitCode);
        self::assertSame("There are no sites yet. Create one with site:create.\n", $output);
    }

    public function testCreatingASiteNeedsItsOptionsWhenNobodyCanBeAsked(): void
    {
        [$exitCode, , $errorOutput] = $this->runCommand(['site:create', 'demo', '--no-interaction']);

        self::assertSame(2, $exitCode);
        self::assertSame("The --domain option is required.\n", $errorOutput);
    }

    public function testABrokenConfigurationIsExplained(): void
    {
        [$exitCode, , $errorOutput] = $this->runCommand(['list'], ['APP_DEBUG' => 'maybe']);

        self::assertSame(1, $exitCode);
        self::assertSame("SightPress could not start: APP_DEBUG is not valid (\"maybe\"): use 1 or 0.\n", $errorOutput);
    }

    /**
     * @param list<string>          $arguments
     * @param array<string, string> $environment added to this process's environment
     *
     * @return array{int, string, string}
     */
    private function runCommand(array $arguments, array $environment = []): array
    {
        $root = dirname(__DIR__, 2);
        $process = proc_open(
            [PHP_BINARY, $root . '/bin/sightpress', ...$arguments],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $root,
            [...getenv(), 'APP_ENV' => 'test', ...$environment],
        );
        self::assertIsResource($process);

        $output = (string) stream_get_contents($pipes[1]);
        $errorOutput = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return [proc_close($process), $output, $errorOutput];
    }
}
