document.addEventListener(
    "DOMContentLoaded",
    function () {

        const app =
            document.getElementById(
                "aartiSessionApp"
            );

        if (!app) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | VIDEO
        |--------------------------------------------------------------------------
        */

        const video =
            document.getElementById(
                "aartiVideo"
            );

        // if (video) {

        //     video.muted = true;

        //     video.play().catch(function () {
        //         // Browser autoplay block kare
        //         // to koi page error nahi dikhana.
        //     });
        // }
        if (video) {

    video.muted = true;

    video.addEventListener("playing", function () {
        video.classList.add("video-playing");
    });

    video.play().catch(function () {
        // Browser autoplay block kare
        // to poster image contain mode mein hi rahe.
    });
}


        /*
        |--------------------------------------------------------------------------
        | AUDIO
        |--------------------------------------------------------------------------
        */

        const audio =
            document.getElementById(
                "aartiAudio"
            );

        const startButton =
            document.getElementById(
                "startAartiButton"
            );

        const restartButton =
            document.getElementById(
                "restartAartiButton"
            );

        const volumeControl =
            document.getElementById(
                "aartiVolume"
            );


        if (audio && startButton) {

            startButton.addEventListener(
                "click",
                async function () {

                    try {

                        if (audio.paused) {

                            await audio.play();

                            startButton.innerHTML =
                                '⏸ Pause Aarti';

                        } else {

                            audio.pause();

                            startButton.innerHTML =
                                '🪔 Aarti Shuru Kare';
                        }

                    } catch (error) {

                        alert(
                            "Aarti audio start nahi ho paya. Please dobara try karein."
                        );
                    }
                }
            );


            audio.addEventListener(
                "ended",
                function () {

                    audio.currentTime = 0;

                    startButton.innerHTML =
                        "🪔 Aarti Shuru Kare";
                }
            );
        }


        if (audio && restartButton) {

            restartButton.addEventListener(
                "click",
                async function () {

                    audio.currentTime = 0;

                    try {

                        await audio.play();

                        if (startButton) {

                            startButton.innerHTML =
                                "⏸ Pause Aarti";
                        }

                    } catch (error) {

                        alert(
                            "Aarti restart nahi ho payi."
                        );
                    }
                }
            );
        }


        if (audio && volumeControl) {

            volumeControl.addEventListener(
                "input",
                function () {

                    audio.volume =
                        Number(
                            volumeControl.value
                        );
                }
            );
        }


        /*
        |--------------------------------------------------------------------------
        | QUICK DONATION
        |--------------------------------------------------------------------------
        */

        const donationButtons =
            document.querySelectorAll(
                ".aarti-donation-value"
            );

        const customBox =
            document.getElementById(
                "customAmountBox"
            );

        const customInput =
            document.getElementById(
                "customAmount"
            );

        const donateButton =
            document.getElementById(
                "donateButton"
            );

        const messageBox =
            document.getElementById(
                "donationMessage"
            );


        let selectedAmount = 101;


        donationButtons.forEach(
            function (button) {

                button.addEventListener(
                    "click",
                    function () {

                        donationButtons.forEach(
                            function (item) {

                                item.classList.remove(
                                    "active"
                                );
                            }
                        );


                        button.classList.add(
                            "active"
                        );


                        const value =
                            button.dataset.amount;


                        if (value === "custom") {

                            selectedAmount = null;

                            if (customBox) {

                                customBox.classList.add(
                                    "show"
                                );
                            }

                        } else {

                            selectedAmount =
                                parseInt(value);

                            if (customBox) {

                                customBox.classList.remove(
                                    "show"
                                );
                            }
                        }
                    }
                );
            }
        );


        if (!donateButton) {
            return;
        }


        donateButton.addEventListener(
            "click",
            async function () {

                let amount =
                    selectedAmount;


                if (!amount) {

                    amount =
                        parseInt(
                            customInput
                                ? customInput.value
                                : ""
                        );
                }


                if (
                    !amount ||
                    amount < 1 ||
                    amount > 100000
                ) {

                    showMessage(
                        "Please valid donation amount enter karein.",
                        "error"
                    );

                    return;
                }


                const donationUrl =
                    app.dataset.donationUrl;


                if (!donationUrl) {

                    showMessage(
                        "Donation URL missing hai.",
                        "error"
                    );

                    return;
                }


                donateButton.disabled =
                    true;


                showMessage(
                    "Payment start ho raha hai...",
                    "normal"
                );


                try {

                    const response =
                        await fetch(
                            donationUrl,
                            {
                                method: "POST",

                                headers: {
                                    "Content-Type":
                                        "application/json",

                                    "Accept":
                                        "application/json",

                                    "X-CSRF-TOKEN":
                                        document
                                            .querySelector(
                                                'meta[name="csrf-token"]'
                                            )
                                            .content
                                },

                                body:
                                    JSON.stringify({
                                        amount: amount
                                    })
                            }
                        );


                    const result =
                        await response.json();


                    if (
                        !response.ok ||
                        !result.success
                    ) {

                        throw new Error(
                            firstError(
                                result
                            )
                        );
                    }


                    if (
                        typeof Razorpay
                        ===
                        "undefined"
                    ) {

                        throw new Error(
                            "Razorpay checkout load nahi hua."
                        );
                    }


                    openRazorpay(
                        result.payment
                    );


                } catch (error) {

                    donateButton.disabled =
                        false;


                    showMessage(
                        error.message,
                        "error"
                    );
                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | RAZORPAY
        |--------------------------------------------------------------------------
        */

        function openRazorpay(payment) {

            const options = {

                key:
                    payment.key,

                amount:
                    payment.amount,

                currency:
                    payment.currency,

                name:
                    "BhaktiDeep",

                description:
                    payment.description ||
                    "Aarti Dakshina",

                order_id:
                    payment.order_id,

                prefill:
                    payment.prefill || {},


                handler:
                    async function (
                        razorpayResponse
                    ) {

                        try {

                            showMessage(
                                "Payment verify ho raha hai...",
                                "normal"
                            );


                            const verifyResponse =
                                await fetch(
                                    payment.verify_url,
                                    {
                                        method:
                                            "POST",

                                        headers: {

                                            "Content-Type":
                                                "application/json",

                                            "Accept":
                                                "application/json",

                                            "X-CSRF-TOKEN":
                                                document
                                                    .querySelector(
                                                        'meta[name="csrf-token"]'
                                                    )
                                                    .content
                                        },

                                        body:
                                            JSON.stringify({

                                                payment_attempt_id:
                                                    payment.attempt_id,

                                                razorpay_order_id:
                                                    razorpayResponse
                                                        .razorpay_order_id,

                                                razorpay_payment_id:
                                                    razorpayResponse
                                                        .razorpay_payment_id,

                                                razorpay_signature:
                                                    razorpayResponse
                                                        .razorpay_signature
                                            })
                                    }
                                );


                            const verifyResult =
                                await verifyResponse.json();


                            if (
                                !verifyResponse.ok ||
                                !verifyResult.success
                            ) {

                                throw new Error(
                                    firstError(
                                        verifyResult
                                    )
                                );
                            }


                            showMessage(
                                "🙏 Dakshina successfully arpit hui.",
                                "success"
                            );


                        } catch (error) {

                            showMessage(
                                error.message,
                                "error"
                            );
                        }


                        donateButton.disabled =
                            false;
                    },


                modal: {

                    ondismiss:
                        function () {

                            donateButton.disabled =
                                false;

                            showMessage(
                                "",
                                "normal"
                            );
                        }
                }
            };


            const razorpay =
                new Razorpay(
                    options
                );


            razorpay.on(
                "payment.failed",
                async function (response) {

                    try {

                        await fetch(
                            payment.failure_url,
                            {
                                method:
                                    "POST",

                                headers: {

                                    "Content-Type":
                                        "application/json",

                                    "Accept":
                                        "application/json",

                                    "X-CSRF-TOKEN":
                                        document
                                            .querySelector(
                                                'meta[name="csrf-token"]'
                                            )
                                            .content
                                },

                                body:
                                    JSON.stringify({

                                        payment_attempt_id:
                                            payment.attempt_id,

                                        razorpay_order_id:
                                            payment.order_id,

                                        error:
                                            response.error || {}
                                    })
                            }
                        );

                    } catch (error) {

                        console.error(
                            error
                        );
                    }


                    donateButton.disabled =
                        false;


                    showMessage(
                        "Payment failed. Please dobara try karein.",
                        "error"
                    );
                }
            );


            razorpay.open();
        }


        /*
        |--------------------------------------------------------------------------
        | HELPERS
        |--------------------------------------------------------------------------
        */

        function showMessage(
            message,
            type
        ) {

            if (!messageBox) {
                return;
            }


            messageBox.className =
                "aarti-donation-message";


            if (type === "success") {

                messageBox.classList.add(
                    "aarti-success"
                );
            }


            if (type === "error") {

                messageBox.classList.add(
                    "aarti-error"
                );
            }


            messageBox.textContent =
                message;
        }


        function firstError(result) {

            if (
                result &&
                result.errors
            ) {

                const keys =
                    Object.keys(
                        result.errors
                    );


                if (
                    keys.length &&
                    result.errors[keys[0]][0]
                ) {

                    return result.errors[keys[0]][0];
                }
            }


            return (
                result.message ||
                "Something went wrong."
            );
        }

    }
);