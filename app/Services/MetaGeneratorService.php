<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

use function Illuminate\Log\log;

class MetaGeneratorService
{
    public function generate(string $title, string $content): array
    {
        $prompt = "
Buat metadata untuk sistem RAG.

WAJIB OUTPUT JSON VALID (STRICT):

{
  \"intents\": [string],
  \"keywords\": [string],
  \"synonyms\": {
    \"kata\": [\"sinonim1\", \"sinonim2\"]
  }
}

ATURAN:
- synonyms HARUS OBJECT, bukan array
- tidak boleh ada penjelasan
- tidak boleh ada markdown
- tidak boleh ada backticks

TITLE: {$title}
CONTENT: {$content}
";

        $res = Http::timeout(30)
            ->withToken(env('GROQ_API_KEY'))
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a strict JSON schema generator.

                            You MUST follow this schema exactly:

                            {
                            "intents": string[],
                            "keywords": string[],
                            "synonyms": object<string, string[]>
                            }

                            No deviation allowed. No array for synonyms. Only object. No markdown. No explanation.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ],
                ],
                'temperature' => 0.2,
            ]);

        $text = $res->json('choices.0.message.content');

        // kalau array → ambil string
        if (is_array($text)) {
            $text = $text[0] ?? '';
        }

        // clean markdown
        $text = preg_replace('/```json|```/', '', $text);
        $text = trim($text);

        // decode
        $decoded = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            log('json_error', [
                'error' => json_last_error_msg(),
                'raw' => $text
            ]);

            return [];
        }

        // return $decoded;

        log('halo', [$res]);

        return $this->formatForFilament($decoded);
    }

    public function formatForFilament(array $meta): array
    {
        return [
            'intents' => implode(', ', $meta['intents'] ?? []),
            'keywords' => implode(', ', $meta['keywords'] ?? []),

            // ✅ FIX INI (WAJIB associative array)
            'synonyms' => collect($meta['synonyms'] ?? [])
                ->mapWithKeys(function ($values, $key) {

                    return [
                        $key => is_array($values)
                            ? implode(', ', $values)
                            : $values
                    ];
                })
                ->toArray(),
        ];
    }
}
