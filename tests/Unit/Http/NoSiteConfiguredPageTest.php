<?php

declare(strict_types=1);

namespace App\Tests\Unit\Http;

use App\Http\NoSiteConfiguredPage;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoSiteConfiguredPage::class)]
final class NoSiteConfiguredPageTest extends TestCase
{
    public function testItIsANotFoundResponse(): void
    {
        self::assertSame(404, new NoSiteConfiguredPage()->statusCode());
    }

    public function testItSendsHtmlThatIsNeitherCachedNorSniffed(): void
    {
        $headers = new NoSiteConfiguredPage()->headers();

        self::assertSame('text/html; charset=utf-8', $headers['Content-Type']);
        self::assertSame('no-store', $headers['Cache-Control']);
        self::assertSame('nosniff', $headers['X-Content-Type-Options']);
        self::assertSame('no-referrer', $headers['Referrer-Policy']);
    }

    public function testTheContentSecurityPolicyAllowsOnlyTheInlineStyle(): void
    {
        $page = new NoSiteConfiguredPage();
        $policy = $page->headers()['Content-Security-Policy'];

        self::assertSame(1, preg_match("/style-src 'sha256-([A-Za-z0-9+\\/=]+)'/", $policy, $matches));
        self::assertSame(1, preg_match('#<style>(.*)</style>#s', $page->body(), $style));
        self::assertSame(base64_encode(hash('sha256', $style[1], true)), $matches[1]);
        self::assertStringContainsString("default-src 'none'", $policy);
        self::assertStringContainsString("frame-ancestors 'none'", $policy);
    }

    public function testProductionPagesCanBeIndexed(): void
    {
        self::assertArrayNotHasKey('X-Robots-Tag', new NoSiteConfiguredPage('production')->headers());
    }

    #[DataProvider('nonProductionEnvironments')]
    public function testOtherEnvironmentsAskRobotsNotToIndex(string $environment): void
    {
        self::assertSame('noindex, nofollow', new NoSiteConfiguredPage($environment)->headers()['X-Robots-Tag']);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function nonProductionEnvironments(): iterable
    {
        yield 'dev' => ['dev'];
        yield 'test' => ['test'];
        yield 'staging' => ['staging'];
    }

    public function testThePageIsAccessible(): void
    {
        $body = new NoSiteConfiguredPage()->body();
        $document = new DOMDocument();
        self::assertTrue($document->loadHTML($body, LIBXML_NOERROR));
        $xpath = new DOMXPath($document);

        self::assertStringStartsWith('<!DOCTYPE html>', $body);
        self::assertSame('en-GB', $xpath->evaluate('string(/html/@lang)'));
        self::assertSame('No site configured - SightPress', $xpath->evaluate('string(/html/head/title)'));
        self::assertSame(
            'width=device-width, initial-scale=1',
            $xpath->evaluate('string(//meta[@name="viewport"]/@content)'),
        );
        self::assertSame(1.0, $xpath->evaluate('count(//main)'));
        self::assertSame(1.0, $xpath->evaluate('count(//h1)'));
        self::assertSame(1.0, $xpath->evaluate('count(//main/h1)'));
        self::assertSame('No site configured', $xpath->evaluate('string(//h1)'));
        self::assertSame(2.0, $xpath->evaluate('count(//main/p)'));
    }
}
