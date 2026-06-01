<?php

namespace App\Http\Controllers;

use App\Actions\CreateCheckoutInvoiceAction;
use App\Actions\HandleXenditWebhookAction;
use App\Services\XenditInvoiceGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
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

        try {
            // Step 1: Atomic transaction to check/lock status and validate capacity
            $registrationResult = DB::transaction(function () use ($validated, $user) {
                // Lock the User record to block concurrent registration requests from the same user
                $lockedUser = \App\Models\User::query()
                    ->whereKey($user->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedUser->update([
                    'phone_number' => $validated['phone_number']
                ]);

                // Pessimistic lock the Event row to securely enforce event ticketing quota checks
                $event = \App\Models\Event::query()
                    ->whereKey($validated['event_id'])
                    ->lockForUpdate()
                    ->first();

                if (! $event) {
                    throw new \Exception('Event tidak ditemukan.', 404);
                }

                // Check if already registered and active
                $existing = DB::table('event_user')
                    ->where('event_id', $event->id)
                    ->where('user_id', $lockedUser->id)
                    ->lockForUpdate()
                    ->first();

                if ($existing && $existing->status === 'active') {
                    throw new \Exception('Anda sudah terdaftar untuk event ini.', 400);
                }

                // SECURE CAPACITY/QUOTA CHECK
                if ($event->capacity !== null) {
                    $activeCount = DB::table('event_user')
                        ->where('event_id', $event->id)
                        ->whereIn('status', ['active', 'pending'])
                        ->count();

                    if ($activeCount >= $event->capacity) {
                        throw new \Exception('Maaf, kuota pendaftaran tiket untuk event ini sudah penuh.', 422);
                    }
                }

                // Free Event registration
                if ($event->type === 'FREE' || (float)$event->price == 0) {
                    if ($existing) {
                        DB::table('event_user')
                            ->where('id', $existing->id)
                            ->update([
                                'status' => 'active',
                                'payment_status' => 'free',
                                'amount' => 0,
                                'updated_at' => now(),
                            ]);
                    } else {
                        DB::table('event_user')->insert([
                            'event_id' => $event->id,
                            'user_id' => $lockedUser->id,
                            'status' => 'active',
                            'payment_status' => 'free',
                            'amount' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    return [
                        'status' => 'free_success',
                        'invoice_url' => route('dashboard.events') . '?payment_status=success'
                    ];
                }

                // Paid Event: Prevent duplicate invoice overwrite!
                // If a pending registration already exists with a payment URL, reuse it!
                if ($existing && $existing->status === 'pending' && filled($existing->payment_url)) {
                    return [
                        'status' => 'reuse_pending',
                        'invoice_url' => $existing->payment_url
                    ];
                }

                // New Paid Registration Setup
                $amount = (float) $event->price;
                $externalId = 'EVT-' . $event->id . '-' . $lockedUser->id . '-' . time();

                return [
                    'status' => 'create_new',
                    'event' => $event,
                    'user' => $lockedUser,
                    'amount' => $amount,
                    'external_id' => $externalId,
                    'existing' => $existing
                ];
            });

            // Return immediate response if Free or Reused Pending
            if ($registrationResult['status'] === 'free_success' || $registrationResult['status'] === 'reuse_pending') {
                return response()->json([
                    'status' => 'success',
                    'message' => $registrationResult['status'] === 'free_success' 
                        ? 'Successfully registered for free event.' 
                        : 'Menampilkan tagihan pembayaran yang sudah ada.',
                    'data' => [
                        'invoice_url' => $registrationResult['invoice_url']
                    ]
                ]);
            }

            // Create new invoice with Xendit API outside the DB transaction (to avoid holding DB locks during network calls)
            $amount = $registrationResult['amount'];
            $externalId = $registrationResult['external_id'];
            $event = $registrationResult['event'];
            $lockedUser = $registrationResult['user'];
            $existing = $registrationResult['existing'];

            $invoiceItems = [
                [
                    'name' => 'Registration: ' . $event->title,
                    'quantity' => 1,
                    'price' => $amount,
                ]
            ];

            $successUrl = route('dashboard.events') . '?payment_status=success';
            $failureUrl = route('dashboard.events') . '?payment_status=failed';

            $response = $this->xenditInvoiceGateway->createEventInvoice(
                $externalId,
                $amount,
                'Registration Event: ' . $event->title,
                $lockedUser,
                $invoiceItems,
                $successUrl,
                $failureUrl
            );

            // Record invoice details to event_user pivot under transaction
            DB::transaction(function () use ($event, $lockedUser, $existing, $amount, $externalId, $response) {
                if ($existing) {
                    DB::table('event_user')
                        ->where('event_id', $event->id)
                        ->where('user_id', $lockedUser->id)
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
                    DB::table('event_user')->insert([
                        'event_id' => $event->id,
                        'user_id' => $lockedUser->id,
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
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Invoice created successfully',
                'data' => [
                    'invoice_url' => $response['invoice_url']
                ]
            ]);

        } catch (\Throwable $exception) {
            $code = $exception->getCode();
            $status = in_array($code, [400, 404, 422]) ? $code : 500;

            Log::error('Failed to handle event booking invoice creation', [
                'user_id' => $user->id,
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
            ], $status);
        }
    }

    private function hasXenditKey(): bool
    {
        return filled(config('services.xendit.secret_key'));
    }
}
