<?php

namespace App\Http\Controllers;

use App\Models\Admin\Deity;
use App\Models\Admin\Donation;
use App\Models\Admin\PaymentLog;
use App\Models\PaymentAttempt;
use App\Services\RazorpayPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AartiDonationController extends Controller
{
    public function create(
        Request $request,
        Deity $deity
    ) {
        $data = $request->validate([
            'amount' => [
                'required',
                'integer',
                'min:1',
                'max:100000',
            ],
        ]);


        $user = $request->user();


        $result = DB::transaction(
            function () use (
                $data,
                $user,
                $deity
            ) {

                /*
                |--------------------------------------------------------------------------
                | CREATE DONATION
                |--------------------------------------------------------------------------
                */

                $donation = Donation::create([

                    'user_id' =>
                        $user->id,

                    'payment_purpose' =>
                        'aarti_dakshina',

                    /*
                    | Ye sirf batayega ki
                    | donation kis Deity ke liye hai.
                    */
                    'session_type' =>
                        Deity::class,

                    'session_id' =>
                        $deity->id,

                    'amount' =>
                        $data['amount'],

                    'currency' =>
                        'INR',

                    'donor_name' =>
                        $user->name,

                    'donor_email' =>
                        $user->email,

                    'donor_mobile' =>
                        $user->mobile,

                    'payment_status' =>
                        'pending',
                ]);


                /*
                |--------------------------------------------------------------------------
                | CREATE PAYMENT ATTEMPT
                |--------------------------------------------------------------------------
                */

                $attempt = PaymentAttempt::create([

                    'user_id' =>
                        $user->id,

                    'donation_id' =>
                        $donation->id,

                    /*
                    | IMPORTANT:
                    |
                    | Payable Deity nahi hogi.
                    | Actual payable Donation hogi.
                    */
                    'payable_type' =>
                        Donation::class,

                    'payable_id' =>
                        $donation->id,

                    'purpose' =>
                        PaymentAttempt::PURPOSE_QUICK_DAKSHINA,

                    'gateway' =>
                        'none',

                    'amount' =>
                        $data['amount'],

                    'currency' =>
                        'INR',

                    'status' =>
                        PaymentAttempt::STATUS_PENDING,

                    'metadata' => [

                        'type' =>
                            'aarti_dakshina',

                        'deity_id' =>
                            $deity->id,

                        'deity_name' =>
                            $deity->name,
                    ],
                ]);


                /*
                |--------------------------------------------------------------------------
                | LINK ATTEMPT TO DONATION
                |--------------------------------------------------------------------------
                */

                $donation->update([
                    'latest_payment_attempt_id' =>
                        $attempt->id,
                ]);


                /*
                |--------------------------------------------------------------------------
                | PAYMENT LOG
                |--------------------------------------------------------------------------
                */

                PaymentLog::create([

                    'donation_id' =>
                        $donation->id,

                    'payment_attempt_id' =>
                        $attempt->id,

                    'loggable_type' =>
                        Deity::class,

                    'loggable_id' =>
                        $deity->id,

                    'user_id' =>
                        $user->id,

                    'gateway' =>
                        'none',

                    'event_type' =>
                        'aarti_dakshina_created',

                    'status' =>
                        'pending',

                    'occurred_at' =>
                        now(),

                    'amount' =>
                        $data['amount'],

                    'payload' => [

                        'deity_id' =>
                            $deity->id,

                        'deity_name' =>
                            $deity->name,
                    ],
                ]);


                return [
                    'attempt' => $attempt,
                    'donation' => $donation,
                ];
            }
        );


        /*
        |--------------------------------------------------------------------------
        | CREATE RAZORPAY ORDER
        |--------------------------------------------------------------------------
        */

        $payment = app(
            RazorpayPaymentService::class
        )->createOrder(

            $result['attempt'],

            $result['donation'],

            $user
        );


        /*
        | Better checkout description.
        */
        $payment['description'] =
            'Aarti Dakshina - '.$deity->name;


        return response()->json([
            'success' => true,
            'payment' => $payment,
        ]);
    }
}