<?php

declare(strict_types=1);

namespace App\Http;

/**
 * The page served while no site is configured for the requested host.
 *
 * It is a placeholder until core resolves sites; it must stay accessible: `lang`, a unique `<title>`, one `<main>`
 * landmark, one `<h1>` and a plain message.
 */
final readonly class NoSiteConfiguredPage
{
    public const int STATUS_CODE = 404;

    private const string STYLE = <<<'CSS'
        :root { color-scheme: light dark; }
        body { margin: 0; font-family: system-ui, sans-serif; font-size: 1.125rem; line-height: 1.6;
            background: Canvas; color: CanvasText; }
        main { max-width: 40rem; margin: 0 auto; padding: 3rem 1.5rem; }
        h1 { font-size: 2rem; line-height: 1.25; margin: 0 0 1rem; }
        CSS;

    public function __construct(
        private string $environment = 'production',
    ) {
    }

    public function statusCode(): int
    {
        return self::STATUS_CODE;
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        $headers = [
            'Content-Type' => 'text/html; charset=utf-8',
            'Cache-Control' => 'no-store',
            'Content-Security-Policy' => sprintf(
                "default-src 'none'; style-src '%s'; base-uri 'none'; form-action 'none'; frame-ancestors 'none'",
                $this->styleHash(),
            ),
            'Referrer-Policy' => 'no-referrer',
            'X-Content-Type-Options' => 'nosniff',
        ];

        if ($this->environment !== 'production') {
            $headers['X-Robots-Tag'] = 'noindex, nofollow';
        }

        return $headers;
    }

    public function body(): string
    {
        return <<<HTML
            <!DOCTYPE html>
            <html lang="en-GB">
            <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>No site configured - SightPress</title>
            <style>{$this->style()}</style>
            </head>
            <body>
            <main>
            <h1>No site configured</h1>
            <p>SightPress is running, but no site is set up for this address yet.</p>
            <p>If you look after this server, create a site and link this address to it.</p>
            </main>
            </body>
            </html>

            HTML;
    }

    private function style(): string
    {
        return self::STYLE;
    }

    private function styleHash(): string
    {
        return 'sha256-' . base64_encode(hash('sha256', $this->style(), true));
    }
}
