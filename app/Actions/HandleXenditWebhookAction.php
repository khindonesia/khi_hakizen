<?php

namespace App\Actions;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HandleXenditWebhookAction
{
    /**
     * @return array{body: array<string, string>, status: int}
     */
    public function handle(Request $request): array
    {
        if (! $this->isVerified($request)) {
            return [
                'body' => ['status' => 'error', 'message' => 'Unauthorized webhook.'],
                'status' => 401,
            ];
        }

        $payload = $this->payload($request);
        $externalId = (string) ($payload['external_id'] ?? '');
        $status = (string) ($payload['status'] ?? '');

        if ($externalId === '') {
            return [
                'body' => ['status' => 'error', 'message' => 'Missing external_id.'],
                'status' => 422,
            ];
        }

        if (str_starts_with($externalId, 'EVT-')) {
            return $this->handleEventRegistration($externalId, $status);
        }

        return $this->handleOrder($externalId, $status);
    }

    private function isVerified(Request $request): bool
    {
        $secret = (string) config('services.xendit.webhook_secret', '');

        if ($secret === '') {
            Log::error('Xendit webhook secret is not configured.');

            return false;
        }

        $callbackToken = (string) $request->header('x-callback-token', '');

        if ($callbackToken !== '') {
            return hash_equals($secret, $callbackToken);
        }

        $signature = (string) $request->header('X-Callback-Signature', '');
        $timestamp = (string) $request->header('X-Callback-Timestamp', '');

        if ($signature === '' || $timestamp === '') {
            return false;
        }

        $signature = str_starts_with($signature, 'sha256=')
            ? substr($signature, 7)
            : $signature;

        $expected = hash_hmac('sha256', "{$timestamp}.{$request->getContent()}", $secret);

        return hash_equals($expected, $signature);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request): array
    {
        $payload = $request->json()->all();

        if ($payload !== []) {
            return $payload;
        }

        return $request->all();
    }

    /**
     * @return array{body: array<string, string>, status: int}
     */
    private function handleEventRegistration(string $externalId, string $status): array
    {
        $registration = DB::table('event_user')
            ->where('external_id', $externalId)
            ->first();

        if (! $registration) {
            return [
                'body' => ['status' => 'error', 'message' => 'Registration not found'],
                'status' => 404,
            ];
        }

        if ($registration->payment_status === 'paid' || $registration->status === 'active') {
            return [
                'body' => ['status' => 'success'],
                'status' => 200,
            ];
        }

        if ($status === 'PAID') {
            DB::table('event_user')
                ->where('external_id', $externalId)
                ->update([
                    'payment_status' => 'paid',
                    'status' => 'active',
                    'updated_at' => now(),
                ]);
        } elseif ($status === 'EXPIRED') {
            DB::table('event_user')
                ->where('external_id', $externalId)
                ->update([
                    'payment_status' => 'expired',
                    'status' => 'cancelled',
                    'updated_at' => now(),
                ]);
        }

        return [
            'body' => ['status' => 'success'],
            'status' => 200,
        ];
    }

    /**
     * @return array{body: array<string, string>, status: int}
     */
    private function handleOrder(string $externalId, string $status): array
    {
        $order = Order::query()
            ->where('external_id', $externalId)
            ->first();

        if (! $order) {
            return [
                'body' => ['status' => 'error', 'message' => 'Order not found'],
                'status' => 404,
            ];
        }

        if ($order->payment_status === 'paid') {
            return [
                'body' => ['status' => 'success'],
                'status' => 200,
            ];
        }

        if ($status === 'PAID') {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
            ]);
        } elseif ($status === 'EXPIRED') {
            $order->update([
                'payment_status' => 'expired',
                'status' => 'cancelled',
            ]);
        }

        return [
            'body' => ['status' => 'success'],
            'status' => 200,
        ];
    }
}
