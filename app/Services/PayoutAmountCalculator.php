<?php

namespace App\Services;

use App\Models\Admin\PlatformSetting;
use App\Models\PaymentAttempt;

class PayoutAmountCalculator
{
    public const SETTING_KEY = 'payout_platform_commission_percent';

    public function freeze(float $grossAmount, array $metadata): array
    {
        if (($metadata['financial_snapshot_frozen'] ?? false) === true) {
            return $metadata;
        }

        $grossAmount = max(round($grossAmount, 2), 0);
        $dakshinaAmount = max(round((float) ($metadata['dakshina_amount'] ?? $metadata['dakshina'] ?? $metadata['donation_amount'] ?? 0), 2), 0);
        $dakshinaAmount = min($dakshinaAmount, $grossAmount);

        $serviceAmount = (float) ($metadata['service_amount'] ?? $metadata['package_amount'] ?? $metadata['hawan_type_price'] ?? ($grossAmount - $dakshinaAmount));
        $serviceAmount = max(round($serviceAmount, 2), 0);

        if (abs(($serviceAmount + $dakshinaAmount) - $grossAmount) > 0.01) {
            $serviceAmount = max(round($grossAmount - $dakshinaAmount, 2), 0);
        }

        $commissionPercent = $this->commissionPercent();
        $platformAmount = round($serviceAmount * ($commissionPercent / 100), 2);
        $panditServiceAmount = round($serviceAmount - $platformAmount, 2);
        $panditAmount = round($panditServiceAmount + $dakshinaAmount, 2);

        return array_merge($metadata, [
            'service_amount' => $serviceAmount,
            'package_amount' => $serviceAmount,
            'gross_amount' => $grossAmount,
            'platform_commission_percent' => $commissionPercent,
            'platform_commission_amount' => $platformAmount,
            'platform_amount' => $platformAmount,
            'pandit_service_amount' => $panditServiceAmount,
            'dakshina_amount' => $dakshinaAmount,
            'pandit_amount' => $panditAmount,
            'financial_snapshot_frozen' => true,
            'financial_snapshot_frozen_at' => now()->toIso8601String(),
        ]);
    }

    public function freezeAttempt(PaymentAttempt $attempt): PaymentAttempt
    {
        $metadata = $this->freeze((float) $attempt->amount, $attempt->metadata ?: []);

        if ($metadata !== ($attempt->metadata ?: [])) {
            $attempt->update(['metadata' => $metadata]);
        }

        return $attempt->fresh();
    }

    private function commissionPercent(): float
    {
        $configured = PlatformSetting::query()->where('key', self::SETTING_KEY)->value('value');
        $configured ??= PlatformSetting::query()->where('key', 'platform_commission_percent')->value('value');
        $percent = is_numeric($configured)
            ? (float) $configured
            : (float) config('services.payouts.platform_commission_percent', 0);

        return round(min(max($percent, 0), 100), 4);
    }
}
