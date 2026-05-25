<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;

class XenditInvoiceGateway
{
    private InvoiceApi $invoiceApi;

    public function __construct()
    {
        Configuration::setXenditKey((string) config('services.xendit.secret_key'));
        $this->invoiceApi = new InvoiceApi();
    }

    /**
     * @param array<int, array{name: string, quantity: int, price: int|float}> $items
     * @return array{id: string, invoice_url: string}
     */
    public function createInvoice(Order $order, User $user, array $items): array
    {
        $request = new CreateInvoiceRequest([
            'external_id' => $order->external_id,
            'amount' => (float) $order->total_amount,
            'description' => 'Order #' . $order->id,
            'invoice_duration' => 86400,
            'currency' => 'IDR',
            'success_redirect_url' => route('payment.success', ['order_id' => $order->id]),
            'failure_redirect_url' => route('payment.failed', ['order_id' => $order->id]),
            'customer' => [
                'given_names' => $user->name,
                'email' => $user->email,
            ],
            'items' => $items,
        ]);
        $response = $this->invoiceApi->createInvoice($request);

        return [
            'id' => (string) $response['id'],
            'invoice_url' => (string) $response['invoice_url'],
        ];
    }

    /**
     * @param array<int, array{name: string, quantity: int, price: int|float}> $items
     * @return array{id: string, invoice_url: string}
     */
    public function createEventInvoice(string $externalId, float $amount, string $description, User $user, array $items, string $successUrl, string $failureUrl): array
    {
        $request = new CreateInvoiceRequest([
            'external_id' => $externalId,
            'amount' => $amount,
            'description' => $description,
            'invoice_duration' => 86400,
            'currency' => 'IDR',
            'success_redirect_url' => $successUrl,
            'failure_redirect_url' => $failureUrl,
            'customer' => [
                'given_names' => $user->name,
                'email' => $user->email,
            ],
            'items' => $items,
        ]);

        $response = $this->invoiceApi->createInvoice($request);

        return [
            'id' => (string) $response['id'],
            'invoice_url' => (string) $response['invoice_url'],
        ];
    }
}
