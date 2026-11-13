<?php

declare(strict_types=1);

namespace App\Tests\Unit\Console;

use App\Console\Application;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Application::class)]
final class ApplicationTest extends TestCase
{
    /** @var resource */
    private mixed $output;

    /** @var resource */
    private mixed $errorOutput;

    protected function setUp(): void
    {
        $this->output = $this->memoryStream();
        $this->errorOutput = $this->memoryStream();
    }

    /**
     * @param list<string> $arguments
     */
    #[DataProvider('listArguments')]
    public function testItListsTheCommands(array $arguments): void
    {
        self::assertSame(Application::SUCCESS, $this->runCommand($arguments));
        self::assertStringContainsString('site:create <name>', $this->read($this->output));
        self::assertSame('', $this->read($this->errorOutput));
    }

    /**
     * @return iterable<string, array{list<string>}>
     */
    public static function listArguments(): iterable
    {
        yield 'no command' => [[]];
        yield 'list' => [['list']];
        yield 'help' => [['help']];
        yield '--help' => [['--help']];
        yield '-h' => [['-h']];
    }

    public function testCreatingASiteIsNotAvailableYet(): void
    {
        self::assertSame(Application::FAILURE, $this->runCommand(['site:create', 'demo']));
        self::assertStringContainsString('"demo"', $this->read($this->errorOutput));
        self::assertStringContainsString('not available', $this->read($this->errorOutput));
        self::assertSame('', $this->read($this->output));
    }

    /**
     * @param list<string> $arguments
     */
    #[DataProvider('missingOrExtraNames')]
    public function testCreatingASiteNeedsExactlyOneName(array $arguments): void
    {
        self::assertSame(Application::INVALID, $this->runCommand($arguments));
        self::assertStringContainsString('Usage: bin/sightpress site:create <name>', $this->read($this->errorOutput));
    }

    /**
     * @return iterable<string, array{list<string>}>
     */
    public static function missingOrExtraNames(): iterable
    {
        yield 'missing' => [['site:create']];
        yield 'two names' => [['site:create', 'demo', 'other']];
    }

    #[DataProvider('invalidNames')]
    public function testCreatingASiteRejectsInvalidNames(string $name): void
    {
        self::assertSame(Application::INVALID, $this->runCommand(['site:create', $name]));
        self::assertStringContainsString('lower-case letters', $this->read($this->errorOutput));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidNames(): iterable
    {
        yield 'empty' => [''];
        yield 'upper case' => ['Demo'];
        yield 'leading hyphen' => ['-demo'];
        yield 'trailing hyphen' => ['demo-'];
        yield 'path' => ['../demo'];
        yield 'markup' => ['<b>demo</b>'];
        yield 'too long' => [str_repeat('a', 64)];
    }

    #[DataProvider('validNames')]
    public function testCreatingASiteAcceptsValidNames(string $name): void
    {
        self::assertSame(Application::FAILURE, $this->runCommand(['site:create', $name]));
        self::assertStringNotContainsString('lower-case letters', $this->read($this->errorOutput));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function validNames(): iterable
    {
        yield 'one character' => ['a'];
        yield 'with hyphen and digits' => ['my-site-2'];
        yield 'longest' => [str_repeat('a', 63)];
    }

    public function testItRejectsUnknownCommands(): void
    {
        self::assertSame(Application::INVALID, $this->runCommand(['nope']));
        self::assertStringContainsString('Unknown command "nope"', $this->read($this->errorOutput));
        self::assertSame('', $this->read($this->output));
    }

    /**
     * @param list<string> $arguments
     */
    private function runCommand(array $arguments): int
    {
        return new Application($this->output, $this->errorOutput)->run($arguments);
    }

    /**
     * @return resource
     */
    private function memoryStream(): mixed
    {
        $stream = fopen('php://memory', 'w+b');
        self::assertIsResource($stream);

        return $stream;
    }

    /**
     * @param resource $stream
     */
    private function read(mixed $stream): string
    {
        rewind($stream);

        return (string) stream_get_contents($stream);
    }
}
