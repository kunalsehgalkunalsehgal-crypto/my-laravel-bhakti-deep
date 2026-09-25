<?php

namespace App\Services;

use App\Models\Pandit\Pandit;
use App\Models\Pandit\PanditBankDetail;
use App\Models\PaymentAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RazorpayPaymentService
{
    public function createOrder(PaymentAttempt $attempt, Model $session, User $user): array
    {
        $key = config('services.razorpay.key_id');
        $secret = config('services.razorpay.key_secret');

        if (! $key || ! $secret) {
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

        if (! $response->successful()) {
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

        if (! $secret || ! $signature) {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, (string) $secret);

        return hash_equals($expected, $signature);
    }

    public function refund(PaymentAttempt $attempt, string $reason): array
    {
        if (! $attempt->gateway_payment_id) {
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

        if (! $response->successful()) {
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

        if ($pandit->status !== 'verified' || blank(config('services.razorpay.key_id')) || blank(config('services.razorpay.key_secret')) || blank($bank->account_holder_name) || blank($bank->account_number) || blank($bank->ifsc_code) || blank($bank->pan_number) || blank($pandit->email) || strlen($phone) < 8 || strlen($phone) > 15 || blank($route['business_type'])) {
            throw ValidationException::withMessages(['razorpay' => 'Verified pandit, bank details and Razorpay Route config are required.']);
        }

        $idempotencyKey = $bank->razorpay_onboarding_idempotency_key ?: (string) Str::uuid();
        $bank->update(['razorpay_onboarding_idempotency_key' => $idempotencyKey]);

        $response = Http::withBasicAuth(config('services.razorpay.key_id'), config('services.razorpay.key_secret'))
            ->withHeader('Idempotency-Key', $idempotencyKey)
            ->asJson()
            ->post(rtrim(config('services.razorpay.base_url'), '/').'/v2/accounts', array_filter([
                'type' => 'route',
                'tnc_accepted' => true,
                'reference_id' => 'bhaktideep_pandit_'.$pandit->id,
                'legal_business_name' => $bank->account_holder_name,
                'business_type' => $route['business_type'],
                'email' => $pandit->email,
                'phone' => $phone,
                'legal_info' => $bank->pan_number ? ['pan' => $bank->pan_number] : null,
                'notes' => ['pandit_id' => (string) $pandit->id],
                'settlement_accounts' => [[
                    'method' => 'bank_account',
                    'bank_account' => [
                        'account_number' => $bank->account_number,
                        'beneficiary_name' => $bank->account_holder_name,
                        'code_type' => 'ifsc',
                        'code' => strtoupper($bank->ifsc_code),
                        'currency' => 'INR',
                        'is_default' => true,
                    ],
                ]],
            ]));

        if (! $response->successful()) {
            $bank->update(['razorpay_last_error' => $response->json('error.description') ?: 'Razorpay linked account creation failed.']);
            throw ValidationException::withMessages(['razorpay' => $bank->razorpay_last_error]);
        }

        $data = $response->json();
        if (blank($data['id'] ?? null)) {
            $bank->update(['razorpay_last_error' => 'Razorpay linked account id missing.']);
            throw ValidationException::withMessages(['razorpay' => $bank->razorpay_last_error]);
        }

        return $this->storeLinkedAccountState($bank, $data);
    }

    public function syncLinkedAccount(PanditBankDetail $bank): PanditBankDetail
    {
        if (! $bank->razorpay_linked_account_id) {
            throw ValidationException::withMessages(['razorpay' => 'Create the Razorpay linked account first.']);
        }

        $baseUrl = rtrim(config('services.razorpay.base_url'), '/');
        $client = Http::withBasicAuth(config('services.razorpay.key_id'), config('services.razorpay.key_secret'))
            ->acceptJson();
        $accountResponse = $client->get($baseUrl.'/v2/accounts/'.$bank->razorpay_linked_account_id);

        if (! $accountResponse->successful()) {
            $message = $accountResponse->json('error.description') ?: 'Could not fetch Razorpay linked account status.';
            $bank->update(['razorpay_last_error' => $message, 'razorpay_synced_at' => now()]);
            throw ValidationException::withMessages(['razorpay' => $message]);
        }

        $data = $accountResponse->json();
        $productId = $bank->razorpay_product_id ?: ($data['product_config']['id'] ?? null);

        if ($productId && empty($data['product_config'])) {
            $productResponse = $client->get($baseUrl.'/v2/accounts/'.$bank->razorpay_linked_account_id.'/products/'.$productId);

            if ($productResponse->successful()) {
                $data['product_config'] = $productResponse->json();
            } else {
                $message = $productResponse->json('error.description') ?: 'Could not fetch Razorpay Route activation status.';
                $bank->update(['razorpay_last_error' => $message, 'razorpay_synced_at' => now()]);
                throw ValidationException::withMessages(['razorpay' => $message]);
            }
        }

        return $this->storeLinkedAccountState($bank, $data);
    }

    private function storeLinkedAccountState(PanditBankDetail $bank, array $data): PanditBankDetail
    {
        $product = $data['product_config'] ?? [];
        $settlements = $product['active_configuration']['settlement_accounts']
            ?? $data['active_configuration']['settlement_accounts']
            ?? [];
        $settlement = collect($settlements)->firstWhere('is_default', true) ?: collect($settlements)->first();
        $activationStatus = $product['activation_status'] ?? $data['activation_status'] ?? $data['status'] ?? null;
        $verificationStatus = $settlement['verification_status'] ?? $data['bank_verification_status'] ?? null;
        $legacyEnabled = (bool) ($data['payout_enabled'] ?? $data['payouts_enabled'] ?? false);
        $settlementReady = $settlement
            && (bool) ($settlement['active'] ?? false)
            && $verificationStatus === 'verified';
        $enabled = $activationStatus === 'activated' && ($legacyEnabled || $settlementReady);
        $lastError = $enabled
            ? null
            : ($data['error']['description'] ?? 'Razorpay Route or bank verification is not activated yet.');

        $bank->update([
            'razorpay_linked_account_id' => $data['id'] ?? $bank->razorpay_linked_account_id,
            'razorpay_product_id' => $product['id'] ?? $bank->razorpay_product_id,
            'razorpay_settlement_account_id' => $settlement['id'] ?? $bank->razorpay_settlement_account_id,
            'razorpay_linked_account_status' => $activationStatus,
            'razorpay_bank_verification_status' => $verificationStatus,
            'razorpay_payout_enabled' => $enabled,
            'razorpay_verified_at' => $enabled ? ($bank->razorpay_verified_at ?: now()) : null,
            'razorpay_synced_at' => now(),
            'razorpay_last_error' => $lastError,
        ]);

        return $bank->fresh();
    }
}
