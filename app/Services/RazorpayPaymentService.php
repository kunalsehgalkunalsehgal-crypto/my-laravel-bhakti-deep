<?php

namespace App\Services;

use App\Models\PaymentAttempt;
use App\Models\Pandit\Pandit;
use App\Models\Pandit\PanditBankDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class RazorpayPaymentService
{
    public function createOrder(PaymentAttempt $attempt, Model $session, User $user): array
    {
        $key = config('services.razorpay.key_id');
        $secret = config('services.razorpay.key_secret');

        if (!$key || !$secret) {
            throw ValidationException::withMessages(['payment' => 'Razorpay test keys are not configured.']);
        }

        $amount = (int) round(((float) $attempt->amount) * 100);
        $receipt = 'BD'.$attempt->id;
        $response = Http::withBasicAuth($key, $secret)
            ->asJson()
            ->post(rtrim(config('services.razorpay.base_url'), '/').'/v1/orders', [
                'amount' => $amount,
                'currency' => $attempt->currency,
                'receipt' => $receipt,
                'notes' => [
                    'attempt_id' => (string) $attempt->id,
                    'booking_id' => (string) $session->getKey(),
                    'booking_type' => class_basename($session),
                ],
            ]);

        if (!$response->successful()) {
            throw ValidationException::withMessages(['payment' => 'Could not start Razorpay payment. Please retry.']);
        }

        $order = $response->json();
        $attempt->update([
            'gateway' => 'razorpay_test',
            'gateway_order_id' => $order['id'],
            'status' => PaymentAttempt::STATUS_PROCESSING,
        ]);
        $attempt->donation?->update(['razorpay_order_id' => $order['id']]);

        return $this->checkoutPayload($attempt->fresh('donation'), $session, $user);
    }

    public function checkoutPayload(PaymentAttempt $attempt, Model $session, User $user): array
    {
        return [
            'key' => config('services.razorpay.key_id'),
            'order_id' => $attempt->gateway_order_id,
            'amount' => (int) round(((float) $attempt->amount) * 100),
            'currency' => $attempt->currency,
            'name' => 'BhaktiDeep',
            'description' => 'Booking #'.$session->getKey(),
            'attempt_id' => $attempt->id,
            'prefill' => [
                'name' => $user->name,
                'email' => $user->email,
                'contact' => $user->mobile,
            ],
            'verify_url' => route('payments.razorpay.verify'),
            'failure_url' => route('payments.razorpay.failure'),
            'profile_url' => route('user.profile'),
            'failure_message' => class_basename($session) === 'DiyaSession'
                ? 'Payment failed. Please start the Diya payment again.'
                : 'Payment failed. Please retry from My Profile.',
        ];
    }

    public function validSignature(string $orderId, string $paymentId, string $signature): bool
    {
        $expected = hash_hmac('sha256', $orderId.'|'.$paymentId, (string) config('services.razorpay.key_secret'));

        return hash_equals($expected, $signature);
    }

    public function validWebhookSignature(string $payload, string $signature): bool
    {
        $secret = config('services.razorpay.webhook_secret') ?: config('services.razorpay.key_secret');

        if (!$secret || !$signature) {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, (string) $secret);

        return hash_equals($expected, $signature);
    }

    public function refund(PaymentAttempt $attempt, string $reason): array
    {
        if (!$attempt->gateway_payment_id) {
            throw ValidationException::withMessages(['payment' => 'Paid Razorpay payment id is missing.']);
        }

        $response = Http::withBasicAuth(config('services.razorpay.key_id'), config('services.razorpay.key_secret'))
            ->asJson()
            ->post(rtrim(config('services.razorpay.base_url'), '/').'/v1/payments/'.$attempt->gateway_payment_id.'/refund', [
                'amount' => (int) round(((float) $attempt->amount) * 100),
                'speed' => 'optimum',
                'receipt' => 'BDR'.$attempt->id,
                'notes' => ['reason' => $reason],
            ]);

        if (!$response->successful()) {
            throw ValidationException::withMessages(['payment' => 'Refund could not be started. Please contact admin.']);
        }

        return $response->json();
    }

    public function createLinkedAccount(Pandit $pandit, PanditBankDetail $bank): PanditBankDetail
    {
        if ($bank->razorpay_linked_account_id) {
            return $bank;
        }

        $phone = preg_replace('/\D+/', '', (string) $pandit->mobile);
        $route = config('services.razorpay.route');

        if ($pandit->status !== 'verified' || blank(config('services.razorpay.key_id')) || blank(config('services.razorpay.key_secret')) || blank($bank->account_holder_name) || blank($bank->account_number) || blank($bank->ifsc_code) || blank($pandit->email) || strlen($phone) < 8 || strlen($phone) > 15 || blank($route['business_type']) || blank($route['category']) || blank($route['subcategory'])) {
            throw ValidationException::withMessages(['razorpay' => 'Verified pandit, bank details and Razorpay Route config are required.']);
        }

        $response = Http::withBasicAuth(config('services.razorpay.key_id'), config('services.razorpay.key_secret'))
            ->asJson()
            ->post(rtrim(config('services.razorpay.base_url'), '/').'/v2/accounts', array_filter([
                'email' => $pandit->email,
                'phone' => $phone,
                'type' => 'route',
                'legal_business_name' => $bank->account_holder_name,
                'business_type' => $route['business_type'],
                'contact_name' => $bank->account_holder_name,
                'profile' => ['category' => $route['category'], 'subcategory' => $route['subcategory']],
                'legal_info' => $bank->pan_number ? ['pan' => $bank->pan_number] : null,
            ]));

        if (!$response->successful()) {
            $bank->update(['razorpay_last_error' => $response->json('error.description') ?: 'Razorpay linked account creation failed.']);
            throw ValidationException::withMessages(['razorpay' => $bank->razorpay_last_error]);
        }

        $data = $response->json();
        if (blank($data['id'] ?? null)) {
            $bank->update(['razorpay_last_error' => 'Razorpay linked account id missing.']);
            throw ValidationException::withMessages(['razorpay' => $bank->razorpay_last_error]);
        }

        $status = $data['status'] ?? null;
        $enabled = (bool) ($data['payout_enabled'] ?? $data['payouts_enabled'] ?? false);

        $bank->update([
            'razorpay_linked_account_id' => $data['id'] ?? null,
            'razorpay_linked_account_status' => $status,
            'razorpay_payout_enabled' => $enabled,
            'razorpay_verified_at' => $enabled || in_array($status, ['active', 'activated', 'verified'], true) ? now() : null,
            'razorpay_last_error' => null,
        ]);

        return $bank->fresh();
    }
}
