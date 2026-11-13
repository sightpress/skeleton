<?php

declare(strict_types=1);

namespace App\Console;

/**
 * The `bin/sightpress` command line tool.
 *
 * A placeholder until core ships its console: it lists the planned commands and validates their input, but
 * `site:create` can't create sites yet.
 */
final readonly class Application
{
    public const int SUCCESS = 0;
    public const int FAILURE = 1;
    public const int INVALID = 2;

    private const string SITE_NAME_PATTERN = '/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/';

    /**
     * @param resource $output
     * @param resource $errorOutput
     */
    public function __construct(
        private mixed $output,
        private mixed $errorOutput,
    ) {
    }

    /**
     * @param list<string> $arguments the command line arguments without the script name
     */
    public function run(array $arguments): int
    {
        $command = $arguments[0] ?? 'list';

        return match ($command) {
            'list', 'help', '--help', '-h' => $this->list(),
            'site:create' => $this->createSite(array_slice($arguments, 1)),
            default => $this->unknown($command),
        };
    }

    private function list(): int
    {
        $this->write($this->output, <<<'TEXT'
            SightPress command line

            Usage:
              bin/sightpress <command> [arguments]

            Commands:
              list                 Show this list.
              site:create <name>   Create a site (not available yet).

            TEXT);

        return self::SUCCESS;
    }

    /**
     * @param list<string> $arguments
     */
    private function createSite(array $arguments): int
    {
        $name = $arguments[0] ?? null;

        if ($name === null || count($arguments) !== 1) {
            $this->write($this->errorOutput, "Usage: bin/sightpress site:create <name>\n");

            return self::INVALID;
        }

        if (preg_match(self::SITE_NAME_PATTERN, $name) !== 1) {
            $this->write(
                $this->errorOutput,
                "The site name must use lower-case letters, digits and hyphens, and start and end with a letter"
                . " or digit (at most 63 characters).\n",
            );

            return self::INVALID;
        }

        $this->write(
            $this->errorOutput,
            sprintf("Can't create the site \"%s\" yet: sites are not available in this version of SightPress.\n", $name),
        );

        return self::FAILURE;
    }

    private function unknown(string $command): int
    {
        $this->write(
            $this->errorOutput,
            sprintf("Unknown command \"%s\". Run bin/sightpress list to see the commands.\n", $command),
        );

        return self::INVALID;
    }

    /**
     * @param resource $stream
     */
    private function write(mixed $stream, string $text): void
    {
        fwrite($stream, $text);
    }
}
