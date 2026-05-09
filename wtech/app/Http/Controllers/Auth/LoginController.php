<?php

namespace App\Http\Controllers\Auth;

use App\Support\CartSessionMerger;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function store(Request $request, CartSessionMerger $cartSessionMerger): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $remember = (bool) ($credentials['remember'] ?? false);

        if (! Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $remember)) {
            return back()->withErrors([
                'email' => 'The provided credentials are incorrect.',
            ])->onlyInput('email');
        }

        if (Auth::user()?->role === 'admin') {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Admin account detected. Please use the admin login page.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $isCheckoutInProgress = $request->session()->has('checkout.resume');

        $cartSessionMerger->mergeGuestSessionCartIntoUserCart(
            $request,
            (int) Auth::id(),
            $isCheckoutInProgress
        );

        $checkoutResume = $request->session()->get('checkout.resume');

        if (is_array($checkoutResume)) {
            $request->session()->put('checkout.shipping_method', $checkoutResume['shipping_method'] ?? session('checkout.shipping_method'));
            $request->session()->put('checkout.shipping_price', (float) ($checkoutResume['shipping_price'] ?? session('checkout.shipping_price', 15)));
            $request->session()->put('checkout.payment_method', $checkoutResume['payment_method'] ?? session('checkout.payment_method'));

            $deliveryDraft = $checkoutResume['delivery_draft'] ?? [];
            if (is_array($deliveryDraft)) {
                $request->session()->put('checkout.delivery_draft', $deliveryDraft);
            }

            $request->session()->forget('checkout.resume');
        }

        return redirect()->intended(route('home'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}