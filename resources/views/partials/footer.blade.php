<footer class="footer-section" id="footer">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-3">
                <div class="brand-wrap">
                    <span class="brand-icon"><i class="bi bi-fire"></i></span>
                    <span><span class="brand-title gold-text">BhaktiDeep</span><span class="brand-tagline">Har Deep Mein Bhakti</span></span>
                </div>
                <p>AI Powered Virtual Temple for Personalized Pooja, Diya &amp; Hawan.</p>
                <div class="socials">
                    <a><i class="bi bi-facebook"></i></a>
                    <a><i class="bi bi-instagram"></i></a>
                    <a><i class="bi bi-youtube"></i></a>
                    <a><i class="bi bi-whatsapp"></i></a>
                </div>
            </div>
            @foreach ([
                ['Quick Links', ['Home', 'Light Diya', 'Book Pooja', 'Book Hawan', 'Live Aarti', 'Blogs', 'Contact Us']],
                ['Our Services', ['All Diyas', 'All Poojas', 'All Hawans', 'Sankalp', 'Live Sessions', 'Spiritual Dashboard']],
                ['Important Links', ['Privacy Policy', 'Terms & Conditions', 'Refund Policy', 'Donation Policy', 'Spiritual Disclaimer', 'Cookie Policy']],
            ] as $col)
                <div class="col-sm-6 col-lg-2 footer-links">
                    <h4 class="gold-text">{{ $col[0] }}</h4>
                    @foreach ($col[1] as $link)<a>{{ $link }}</a>@endforeach
                </div>
            @endforeach
            <div class="col-lg-3">
                <h4 class="gold-text">We Accept</h4>
                <div class="payment-box">
                    <strong>Razorpay</strong>
                    <div><span>VISA</span><span>MC</span><span>RuPay</span><span>UPI</span></div>
                    <small>100% Secure Payments</small>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} BhaktiDeep. All rights reserved.</p>
            <p>Made with <i class="bi bi-heart-fill"></i> for Devotees</p>
        </div>
    </div>
</footer>
