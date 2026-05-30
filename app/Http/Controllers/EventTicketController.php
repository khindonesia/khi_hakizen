<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EventTicketController extends Controller
{
    /**
     * Show the printable event ticket.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\View\View
     */
    public function showTicket(Event $event): View
    {
        $user = auth()->user();

        // Verify if user is registered and active
        $registration = DB::table('event_user')
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (! $registration) {
            abort(403, 'Anda tidak terdaftar untuk event ini.');
        }

        // Calculate sequence number (nomor urut)
        $sequenceNumber = DB::table('event_user')
            ->where('event_id', $event->id)
            ->where('id', '<=', $registration->id)
            ->count();

        // Parse purchase date (created_at)
        $purchaseDate = $registration->created_at 
            ? \Carbon\Carbon::parse($registration->created_at)->format('dmY') 
            : now()->format('dmY');

        // Compile ticket number
        $ticketNumber = 'EVENT-' . $event->id . '-' . $purchaseDate . '-' . sprintf('%03d', $sequenceNumber);

        return view('tickets.print', [
            'event' => $event,
            'user' => $user,
            'registration' => $registration,
            'ticketNumber' => $ticketNumber,
        ]);
    }
}
