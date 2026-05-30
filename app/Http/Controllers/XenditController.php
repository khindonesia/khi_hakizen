<?php

namespace App\Http\Controllers;

use App\Actions\CreateCheckoutInvoiceAction;
use App\Actions\HandleXenditWebhookAction;
use App\Services\XenditInvoiceGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class XenditController extends Controller
{
    public function __construct(
        private readonly CreateCheckoutInvoiceAction $createCheckoutInvoice,
        private readonly XenditInvoiceGateway $xenditInvoiceGateway,
        private readonly HandleXenditWebhookAction $handleXenditWebhook,
    ) {
    }

    public function createInvoice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address_id' => 'required|integer',
            'courier_code' => 'required|string|max:20',
            'service_code' => 'required|string|max:50',
        ]);

        if (! $this->hasXenditKey()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Xendit API key belum dikonfigurasi. Set XENDIT_SECRET_KEY di .env lalu jalankan php artisan config:clear.',
            ], 500);
        }

        try {
            $checkout = $this->createCheckoutInvoice->handle($request->user(), $validated);
            $order = $checkout['order'];

            $response = $this->xenditInvoiceGateway->createInvoice(
                $order,
                $request->user(),
                $checkout['invoice_items'],
            );

            $order->update([
                'xendit_invoice_id' => $response['id'],
                'payment_url' => $response['invoice_url'],
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Invoice created successfully',
                'data' => [
                    'order_id' => $order->id,
                    'invoice_url' => $response['invoice_url']
                ]
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (ModelNotFoundException $exception) {
            throw $exception;
        } catch (\Throwable $throwable) {
            Log::error('Failed to create Xendit invoice', [
                'user_id' => $request->user()?->id,
                'message' => $throwable->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create invoice.'
            ], 500);
        }
    }

    public function handleCallback(Request $request): JsonResponse
    {
        $result = $this->handleXenditWebhook->handle($request);

        return response()->json($result['body'], $result['status']);
    }

    public function createEventInvoice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_id' => 'required|integer',
            'phone_number' => 'required|string|max:20',
        ]);

        if (! $this->hasXenditKey()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Xendit API key belum dikonfigurasi. Set XENDIT_SECRET_KEY di .env lalu jalankan php artisan config:clear.',
            ], 500);
        }

        $user = $request->user();
        $user->update([
            'phone_number' => $validated['phone_number']
        ]);

        $event = \App\Models\Event::find($validated['event_id']);

        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event tidak ditemukan.',
            ], 404);
        }

        // Check if already registered and active
        $existing = \Illuminate\Support\Facades\DB::table('event_user')
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing && $existing->status === 'active') {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda sudah terdaftar untuk event ini.',
            ], 400);
        }

        try {
            if ($event->type === 'FREE' || (float)$event->price == 0) {
                // Register immediately
                if ($existing) {
                    \Illuminate\Support\Facades\DB::table('event_user')
                        ->where('id', $existing->id)
                        ->update([
                            'status' => 'active',
                            'payment_status' => 'free',
                            'amount' => 0,
                            'updated_at' => now(),
                        ]);
                } else {
                    \Illuminate\Support\Facades\DB::table('event_user')->insert([
                        'event_id' => $event->id,
                        'user_id' => $user->id,
                        'status' => 'active',
                        'payment_status' => 'free',
                        'amount' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Successfully registered for free event.',
                    'data' => [
                        'invoice_url' => route('dashboard.events') . '?payment_status=success'
                    ]
                ]);
            }

            $amount = (float) $event->price;
            
            // Generate unique external ID
            $externalId = 'EVT-' . $event->id . '-' . $user->id . '-' . time();

            // Prepare Invoice Items format
            $invoiceItems = [
                [
                    'name' => 'Registration: ' . $event->title,
                    'quantity' => 1,
                    'price' => $amount,
                ]
            ];

            // URLs for redirecting user after success/fail
            // Let's redirect back to user's dashboard events list
            $successUrl = route('dashboard.events') . '?payment_status=success';
            $failureUrl = route('dashboard.events') . '?payment_status=failed';

            $response = $this->xenditInvoiceGateway->createEventInvoice(
                $externalId,
                $amount,
                'Registration Event: ' . $event->title,
                $user,
                $invoiceItems,
                $successUrl,
                $failureUrl
            );

            // Save or update pivot registration
            if ($existing) {
                \Illuminate\Support\Facades\DB::table('event_user')
                    ->where('id', $existing->id)
                    ->update([
                        'status' => 'pending',
                        'payment_status' => 'pending',
                        'amount' => $amount,
                        'external_id' => $externalId,
                        'invoice_id' => $response['id'],
                        'payment_url' => $response['invoice_url'],
                        'updated_at' => now(),
                    ]);
            } else {
                \Illuminate\Support\Facades\DB::table('event_user')->insert([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'amount' => $amount,
                    'external_id' => $externalId,
                    'invoice_id' => $response['id'],
                    'payment_url' => $response['invoice_url'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Invoice created successfully',
                'data' => [
                    'invoice_url' => $response['invoice_url']
                ]
            ]);
        } catch (\Throwable $throwable) {
            Log::error('Failed to create Xendit invoice for event', [
                'user_id' => $user->id,
                'event_id' => $event->id,
                'message' => $throwable->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create invoice for event booking: ' . $throwable->getMessage()
            ], 500);
        }
    }

    private function hasXenditKey(): bool
    {
        return filled(config('services.xendit.secret_key'));
    }
}
