<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpWord\IOFactory as PhpWordIOFactory;
use Smalot\PdfParser\Parser as PdfParser;

class DocumentTextExtractor
{
    /**
     * @return array{text: string, file_type: string, file_name: string}
     */
    public function extractTextFromUploadedFile(?UploadedFile $file, ?string $ocrText = null): array
    {
        if (! $file && ! $ocrText) {
            throw new Exception('File atau teks hasil OCR tidak ditemukan.');
        }

        if (! $file && $ocrText) {
            return [
                'text' => $this->normalizeExtractedText($ocrText),
                'file_type' => 'image/manual',
                'file_name' => 'manual_input.txt',
            ];
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $fileName = $file->getClientOriginalName();
        $text = '';

        try {
            if ($extension === 'txt') {
                $text = file_get_contents($file->getRealPath());
            } elseif ($extension === 'pdf') {
                $parser = new PdfParser;
                $pdf = $parser->parseFile($file->getRealPath());
                $text = $pdf->getText();
            } elseif ($extension === 'docx') {
                $phpWord = PhpWordIOFactory::load($file->getRealPath(), 'Word2007');
                foreach ($phpWord->getSections() as $section) {
                    foreach ($section->getElements() as $element) {
                        if (method_exists($element, 'getText')) {
                            $got = $element->getText();
                            $text .= (is_array($got) ? implode(' ', array_filter($got)) : (string) $got)."\n";
                        } elseif (method_exists($element, 'getElements')) {
                            foreach ($element->getElements() as $childElement) {
                                if (method_exists($childElement, 'getText')) {
                                    $got = $childElement->getText();
                                    $text .= (is_array($got) ? implode(' ', array_filter($got)) : (string) $got).' ';
                                }
                            }
                            $text .= "\n";
                        }
                    }
                }
            } elseif (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                if ($ocrText) {
                    $text = $ocrText;
                } else {
                    throw new Exception("File gambar memerlukan hasil teks manual atau OCR di field 'ocr_text'.");
                }
            } else {
                throw new Exception("Format file tidak didukung: {$extension}. Gunakan txt, pdf, docx, atau gambar.");
            }
        } catch (Exception $e) {
            throw new Exception('Gagal mengekstrak teks dari file: '.$e->getMessage());
        }

        return [
            'text' => $this->normalizeExtractedText($text),
            'file_type' => $extension,
            'file_name' => $fileName,
        ];
    }

    private function normalizeExtractedText(string $text): string
    {
        $text = preg_replace("/^\xEF\xBB\xBF/u", '', $text) ?? $text;
        $text = preg_replace('/^\x{FEFF}/u', '', $text) ?? $text;
        $text = str_replace("\r\n", "\n", $text);
        $text = str_replace("\r", "\n", $text);

        return trim($text);
    }

    public function detectChapters(string $text): array
    {
        $chapters = [];

        // Regex to match "BAB 1", "Chapter 1", "BAB I", etc.
        $pattern = '/(?i)(?:bab|chapter)\s+[a-z0-9ivxlc]+(?:[\s\:\-]*[^\n]+)?/i';

        $lines = explode("\n", $text);

        $currentChapterIndex = -1;
        $currentContent = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Check if line looks like a chapter header (usually short, matches pattern)
            if (preg_match($pattern, $line) && strlen($line) < 100) {
                if ($currentChapterIndex >= 0) {
                    $chapters[$currentChapterIndex]['content'] = implode("\n", $currentContent);
                }

                $currentChapterIndex++;

                // Extract chapter number/identifier and title
                // Try to split "BAB 1 Judul" or "BAB 1: Judul"
                $parts = preg_split('/[\:\-]/', $line, 2);
                if (count($parts) > 1) {
                    $chapterName = trim($parts[0]);
                    $title = trim($parts[1]);
                } else {
                    $chapterName = $line;
                    $title = '';
                }

                $chapters[] = [
                    'chapter' => $chapterName,
                    'title' => $title,
                    'content' => '',
                ];
                $currentContent = [];
            } else {
                $currentContent[] = $line;
            }
        }

        if ($currentChapterIndex >= 0) {
            $chapters[$currentChapterIndex]['content'] = implode("\n", $currentContent);
        }

        if (empty($chapters)) {
            $chapters[] = [
                'chapter' => 'Bab Umum',
                'title' => 'Keseluruhan Materi',
                'content' => $text,
            ];
        }

        return $chapters;
    }
}
