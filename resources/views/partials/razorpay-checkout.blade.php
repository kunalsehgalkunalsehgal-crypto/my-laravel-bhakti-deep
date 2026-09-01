@once
    @push('scripts')
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script>
            window.startBhaktiDeepPayment = function (payment, button, resetButton) {
                if (!window.Razorpay) {
                    alert('Payment checkout could not load. Please retry.');
                    if (resetButton) resetButton();
                    return;
                }

                const post = (url, data) => fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(data),
                }).then(async response => {
                    const result = await response.json();
                    if (!response.ok || !result.success) throw new Error(result.message || 'Payment failed.');
                    return result;
                });

                const checkout = new Razorpay({
                    key: payment.key,
                    amount: payment.amount,
                    currency: payment.currency,
                    name: payment.name,
                    description: payment.description,
                    order_id: payment.order_id,
                    prefill: payment.prefill || {},
                    handler: async function (response) {
                        try {
                            const result = await post(payment.verify_url, {
                                payment_attempt_id: payment.attempt_id,
                                razorpay_order_id: response.razorpay_order_id,
                                razorpay_payment_id: response.razorpay_payment_id,
                                razorpay_signature: response.razorpay_signature,
                            });
                            window.location.href = result.redirect_url || payment.profile_url;
                        } catch (error) {
                            alert(error.message || 'Payment verification failed. Please retry.');
                            if (resetButton) resetButton();
                        }
                    },
                    modal: {
                        ondismiss: function () {
                            if (resetButton) resetButton();
                        },
                    },
                });

                checkout.on('payment.failed', function (response) {
                    post(payment.failure_url, {
                        payment_attempt_id: payment.attempt_id,
                        razorpay_order_id: payment.order_id,
                        error: response.error || {},
                    }).catch(function () {}).finally(function () {
                        alert('Payment failed. Please retry from My Profile.');
                        if (resetButton) resetButton();
                    });
                });

                checkout.open();
            };
        </script>
    @endpush
@endonce
