<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $stripePriceId = 'price_1SXcoKBAb41cQoUY1UlpZsqa';
        $quantity = 1;

        // Fake user for testing
        $user = \App\Models\User::first();

        return $user->checkout([$stripePriceId => $quantity], [
            'success_url' => route('checkout.success'),
            'cancel_url' => route('checkout.cancel'),
        ]);
    }

    public function subscriptionCheckout(Request $request)
    {
        $stripePriceId = 'price_1SXcqGBAb41cQoUYofCwGUtd';

        // Fake user for testing
        $user = \App\Models\User::first();

        return $user->newSubscription('default', $stripePriceId)->checkout([
            'success_url' => route('checkout.success'),
            'cancel_url' => route('checkout.cancel'),
        ]);
    }

    public function success(Request $request)
    {
        return response()->json(['message' => 'Checkout successful.']);
    }

    public function cancel(Request $request)
    {
        return response()->json(['message' => 'Checkout cancelled.']);
    }

    public function latestInvoice(Request $request)
    {
        // Fake user for testing
        $user = \App\Models\User::first();

        $invoiceId = $user->invoices()->first()?->id;

        if ($invoiceId) {
            return $user->downloadInvoice($invoiceId);
        }

        return response()->json(['message' => 'No invoice found.']);
    }
}
