<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\KnowledgeBase;

class ChatController extends Controller
{
    public function chat(Request $request)
    {
        $message = trim($request->message ?? '');

        if ($message === '') {
            return response()->json([
                'reply' => 'Pesan tidak boleh kosong.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | BASIC PROMPT INJECTION GUARD
        |--------------------------------------------------------------------------
        */
        $blocked = [
            'ignore previous instructions',
            'system prompt',
            'jailbreak',
            'developer mode',
            'act as',
            'you are now',
        ];

        foreach ($blocked as $word) {
            if (stripos($message, $word) !== false) {
                return response()->json([
                    'reply' => 'Permintaan tidak valid.'
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 1. NORMALIZE QUERY
        |--------------------------------------------------------------------------
        */
        $baseQuery = strtolower($message);

        $keywords = collect(explode(' ', $baseQuery))
            ->map(fn($w) => trim($w))
            ->filter(fn($w) => strlen($w) > 2)
            ->values()
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | 2. FETCH KNOWLEDGE BASE (initial context for synonyms)
        |--------------------------------------------------------------------------
        */
        $query = KnowledgeBase::query()
            ->where('title', 'LIKE', "%{$baseQuery}%")
            ->orWhere('content', 'LIKE', "%{$baseQuery}%");

        foreach ($keywords as $word) {
            $query->orWhere('content', 'LIKE', "%{$word}%")
                ->orWhereJsonContains('meta->keywords', $word)
                ->orWhereJsonContains('meta->intents', $word);
        }

        $contextItems = $query->limit(5)->get();

        /*
        |--------------------------------------------------------------------------
        | 3. SYNONYM EXPANSION
        |--------------------------------------------------------------------------
        */
        $synonyms = [];

        foreach ($contextItems as $item) {

            $meta = $item->meta ?? [];

            $itemSynonyms = $meta['synonyms'] ?? [];

            if (!is_array($itemSynonyms)) {
                continue;
            }

            foreach ($itemSynonyms as $key => $values) {

                if (is_string($values)) {
                    $values = array_map('trim', explode(',', $values));
                }

                if (!is_array($values)) {
                    continue;
                }

                $synonyms[$key] = array_merge(
                    $synonyms[$key] ?? [],
                    $values
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 4. EXPAND KEYWORDS (FIXED: NOW USED)
        |--------------------------------------------------------------------------
        */
        $expandedKeywords = array_values(array_unique(array_merge(
            $keywords,
            collect($synonyms)->flatten()->toArray()
        )));

        /*
        |--------------------------------------------------------------------------
        | 5. RE-QUERY USING EXPANDED KEYWORDS (IMPORTANT FIX)
        |--------------------------------------------------------------------------
        */
        $query = KnowledgeBase::query()
            ->where(function ($q) use ($baseQuery) {
                $q->where('title', 'LIKE', "%{$baseQuery}%")
                    ->orWhere('content', 'LIKE', "%{$baseQuery}%");
            })
            ->orWhere(function ($q) use ($expandedKeywords) {
                foreach ($expandedKeywords as $word) {
                    $q->orWhere('content', 'LIKE', "%{$word}%")
                        ->orWhereJsonContains('meta->keywords', $word)
                        ->orWhereJsonContains('meta->intents', $word);
                }
            });

        $contextItems = $query->limit(5)->get();

        /*
        |--------------------------------------------------------------------------
        | 6. FORMAT CONTEXT
        |--------------------------------------------------------------------------
        */
        $context = $contextItems->map(function ($item) {
            return
                "TITLE: {$item->title}\n" .
                "META: " . json_encode($item->meta) . "\n" .
                "CONTENT: {$item->content}";
        })->implode("\n\n---\n\n");

        /*
        |--------------------------------------------------------------------------
        | 7. SYSTEM PROMPT (STRICT RAG LOCK)
        |--------------------------------------------------------------------------
        */
        $system = <<<PROMPT
Kamu adalah AI internal Komunitas Historia Indonesia (KHI).

ATURAN WAJIB:
- Jawab HANYA berdasarkan DATABASE CONTEXT
- Jangan gunakan pengetahuan luar
- Jangan mengarang
- Jika tidak ada di context jawab: "Data tidak tersedia di sistem KHI. Silakan ajukan pertanyaan yang lebih spesifik!"
- Gunakan sinonim hanya untuk memahami query

DATABASE CONTEXT:
{$context}
PROMPT;

        /*
        |--------------------------------------------------------------------------
        | 8. CALL LLM
        |--------------------------------------------------------------------------
        */
        try {
            $res = Http::timeout(30)
                ->withToken(env('GROQ_API_KEY'))
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.3-70b-versatile',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $system
                        ],
                        [
                            'role' => 'user',
                            'content' => $message
                        ],
                    ],
                    'temperature' => 0.1,
                ]);

            $content = $res->json('choices.0.message.content');

            return response()->json([
                'reply' => $content ?: 'AI tidak merespon.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'reply' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
