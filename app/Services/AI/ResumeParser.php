<?php

namespace App\Services\AI;

use App\Models\Candidate;
use App\Services\AI\Schemas\ResumeSchema;
use Smalot\PdfParser\Parser;

class ResumeParser
{
    public function __construct(
        private OpenAIClient $client,
    ) {}

    /**
     * Parse a resume PDF file and return structured data.
     *
     * @param  string  $filePath  Absolute path to the PDF resume file.
     * @return array Structured resume data matching ResumeSchema.
     *
     * @throws \RuntimeException
     */
    public function parse(string $filePath): array
    {
        $parser = new Parser;
        $pdf = $parser->parseFile($filePath);
        $text = $pdf->getText();

        if (empty(trim($text))) {
            throw new \RuntimeException('Could not extract text from the resume PDF.');
        }

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an expert resume parser. Extract structured data from the following resume text. Be thorough and accurate.',
            ],
            [
                'role' => 'user',
                'content' => $text,
            ],
        ];

        return $this->client->chat(
            messages: $messages,
            responseFormat: ResumeSchema::schema(),
            options: [
                '_action' => 'parse_resume',
                '_loggable_type' => Candidate::class,
            ],
        );
    }
}
