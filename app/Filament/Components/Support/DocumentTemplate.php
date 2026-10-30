<?php

namespace App\Filament\Components\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdf;
use InvalidArgumentException;

/**
 * Fills an HTML document template from `public/template/{company}/{document}.html` and turns it into a PDF.
 *
 * Templates are plain HTML; `${name}` marks where a value goes. Images are referenced relative to the
 * template file (`images/…`), so the same file also opens directly in a browser while it is being edited.
 */
class DocumentTemplate
{
    public const COMPANIES = ['rasyidu', 'edunesia'];

    public const DOCUMENTS = ['spk', 'kuitansi', 'invoice'];

    /**
     * Absolute path of a template file.
     */
    public static function path(string $company, string $document): string
    {
        if (! in_array($company, self::COMPANIES, true) || ! in_array($document, self::DOCUMENTS, true)) {
            throw new InvalidArgumentException("Template {$company}/{$document} is not defined.");
        }

        return public_path("template/{$company}/{$document}.html");
    }

    /**
     * The template with every `${name}` replaced by its escaped value. Names without a value are left
     * untouched on purpose, so a typo in a template shows up instead of silently printing nothing.
     *
     * @param  array<string, scalar|null>  $values
     */
    public static function html(string $company, string $document, array $values): string
    {
        $html = (string) file_get_contents(self::path($company, $document));

        return (string) preg_replace_callback(
            '/\$\{([A-Za-z0-9_]+)\}/',
            fn (array $match): string => array_key_exists($match[1], $values)
                ? nl2br(e((string) $values[$match[1]]), false)
                : $match[0],
            $html,
        );
    }

    /**
     * Every distinct `${name}` a template uses.
     *
     * @return list<string>
     */
    public static function placeholders(string $company, string $document): array
    {
        preg_match_all('/\$\{([A-Za-z0-9_]+)\}/', (string) file_get_contents(self::path($company, $document)), $matches);

        return array_values(array_unique($matches[1]));
    }

    /**
     * @param  array<string, scalar|null>  $values
     */
    public static function pdf(string $company, string $document, array $values): DomPdf
    {
        return Pdf::setBasePath(dirname(self::path($company, $document)))
            ->setPaper('a4')
            ->loadHTML(self::html($company, $document, $values));
    }
}
