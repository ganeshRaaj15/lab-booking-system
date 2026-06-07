<?php

declare(strict_types=1);

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;
use Smalot\PdfParser\Parser as PdfParser;
use ZipArchive;

class UploadedDocumentTextExtractor
{
    public function extract(UploadedFile $file): array
    {
        if (! $file->isValid()) {
            throw new RuntimeException('Uploaded file is not valid.');
        }

        $extension = strtolower((string) ($file->getClientExtension() ?: $file->getExtension()));
        $path = $file->getTempName();
        $text = match ($extension) {
            'docx' => $this->extractFromDocx($path),
            'pdf' => $this->extractFromPdf($path),
            'html', 'htm', 'txt', 'md', 'doc', 'rtf' => $this->extractFromTextLike($path, $extension),
            default => throw new RuntimeException('Unsupported file type: ' . $extension),
        };

        $normalized = $this->normalizeText($text);
        if ($normalized === '') {
            throw new RuntimeException('No readable text could be extracted from the uploaded file.');
        }

        return [
            'text' => $normalized,
            'extension' => $extension,
            'original_name' => (string) $file->getClientName(),
            'char_count' => mb_strlen($normalized),
            'line_count' => substr_count($normalized, "\n") + 1,
        ];
    }

    protected function extractFromDocx(string $path): string
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Unable to read the uploaded DOCX file.');
        }

        $parts = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string) $zip->getNameIndex($i);
            if (! preg_match('#^word/(document|header\d+|footer\d+|footnotes|endnotes)\.xml$#', $name)) {
                continue;
            }

            $xml = $zip->getFromName($name);
            if (! is_string($xml) || $xml === '') {
                continue;
            }

            $xml = str_replace(
                ['</w:p>', '</w:tr>', '</w:tc>', '<w:tab/>', '<w:tab />'],
                ["\n", "\n", "\t", "\t", "\t"],
                $xml
            );

            $parts[] = $xml;
        }

        $zip->close();

        return html_entity_decode(strip_tags(implode("\n", $parts)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    protected function extractFromPdf(string $path): string
    {
        try {
            $parser = new PdfParser();
            return $parser->parseFile($path)->getText();
        } catch (\Throwable $e) {
            throw new RuntimeException('Unable to read the uploaded PDF file.', 0, $e);
        }
    }

    protected function extractFromTextLike(string $path, string $extension): string
    {
        $contents = (string) file_get_contents($path);

        if ($contents === '') {
            return '';
        }

        if ($extension === 'rtf') {
            return $this->extractFromRtfString($contents);
        }

        if ($extension === 'doc' && $this->looksBinary($contents)) {
            $printable = [];
            preg_match_all('/[A-Za-z0-9,.():;\/\\\\\-\s]{4,}/', $contents, $printable);
            return implode("\n", $printable[0] ?? []);
        }

        if ($this->looksHtml($contents)) {
            return html_entity_decode(strip_tags($contents), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return $contents;
    }

    protected function extractFromRtfString(string $contents): string
    {
        $contents = preg_replace("/\\\\'[0-9a-fA-F]{2}/", ' ', $contents) ?? $contents;
        $contents = preg_replace('/\\\\par[d]?/', "\n", $contents) ?? $contents;
        $contents = preg_replace('/\\\\tab/', "\t", $contents) ?? $contents;
        $contents = preg_replace('/\\\\[a-z]+\d* ?/i', ' ', $contents) ?? $contents;
        $contents = str_replace(['{', '}'], ' ', $contents);

        return html_entity_decode(strip_tags($contents), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    protected function normalizeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r", "\xC2\xA0"], ["\n", "\n", ' '], $text);
        $text = preg_replace("/[ \t]+/u", ' ', $text) ?? $text;
        $text = preg_replace("/\n{3,}/u", "\n\n", $text) ?? $text;

        return trim($text);
    }

    protected function looksHtml(string $contents): bool
    {
        return preg_match('/<(html|body|div|p|table|span|meta|style)\b/i', $contents) === 1;
    }

    protected function looksBinary(string $contents): bool
    {
        return preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $contents) === 1;
    }
}
