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

        return view('tickets.print', [
            'event' => $event,
            'user' => $user,
            'registration' => $registration,
        ]);
    }
}
