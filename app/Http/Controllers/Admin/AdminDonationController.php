<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Donation;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;

class AdminDonationController extends Controller
{
    public function index(Request $request)
    {
        $records = $this->query($request)->paginate(15)->withQueryString();
        $totalRevenue = (clone $this->query($request))->where('payment_status', 'paid')->sum('amount');

        return view('admin.donations.index', compact('records', 'totalRevenue'));
    }

    public function export(Request $request): StreamedResponse
    {
        $filename = 'donations-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($request) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Donor', 'Amount', 'Status', 'Receipt', 'Razorpay Order', 'Razorpay Payment', 'Created At']);

            $this->query($request)->chunk(200, function ($donations) use ($handle) {
                foreach ($donations as $donation) {
                    fputcsv($handle, [
                        $donation->id,
                        $donation->donor_name ?: $donation->user?->name,
                        $donation->amount,
                        $donation->payment_status,
                        $donation->receipt_number,
                        $donation->razorpay_order_id,
                        $donation->razorpay_payment_id,
                        $donation->created_at,
                    ]);
                }
            });

            fclose($handle);
        }, $filename);
    }

    private function query(Request $request)
    {
        return Donation::with(['user', 'service'])
            ->when($request->filled('payment_status'), fn ($query) => $query->where('payment_status', $request->payment_status))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('created_at', '>=', $request->from))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('created_at', '<=', $request->to))
            ->latest();
    }
}
