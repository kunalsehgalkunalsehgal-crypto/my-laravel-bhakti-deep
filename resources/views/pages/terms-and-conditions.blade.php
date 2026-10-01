@extends('layouts.app')

@section('title', 'Terms & Conditions | BhaktiDeep')

@section('description', 'Read the terms and conditions for using BhaktiDeep.')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/legal-pages-simple.css') }}?v=20260930b">
@endpush

@section('body')

<main class="bd-policy-page">

    <div class="container bd-policy-container">

        <header class="bd-policy-heading">
            <h1>Terms &amp; Conditions</h1>

            <p>
                Please read these terms carefully before using
                BhaktiDeep or booking a spiritual service.
            </p>

            <small>Last updated: 30 September 2026</small>
        </header>


        <article class="bd-policy-list">


            <!-- 1. ACCEPTANCE -->

            <section class="bd-policy-block">

                <h2>
                    <span>1.</span> Acceptance of Terms
                </h2>

                <p>
                    Welcome to BhaktiDeep. These terms apply to visitors,
                    devotees and pandits using the BhaktiDeep platform.
                </p>

                <p>
                    By accessing our website, registering an account or
                    making a booking, you agree to these Terms &amp;
                    Conditions and acknowledge our
                    <a href="{{ route('privacy-policy') }}">
                        Privacy Policy
                    </a>.
                </p>

                <p>
                    Additional conditions may apply to individual services.
                    Please review the relevant details before booking.
                </p>

            </section>



            <!-- 2. SERVICES -->

            <section class="bd-policy-block">

                <h2>
                    <span>2.</span> Services We Provide
                </h2>

                <p>
                    BhaktiDeep provides access to spiritual experiences
                    and devotional services.
                </p>

                <p>
                    These may include digital diya offerings,
                    personalized poojas, hawans, live aarti,
                    digital devotional content, spiritual sessions,
                    optional dakshina and donations.
                </p>

                <p>
                    Service availability, pricing, duration and
                    inclusions depend on the selected service
                    and the information provided during booking.
                </p>

            </section>



            <!-- 3. ACCOUNTS -->

            <section class="bd-policy-block">

                <h2>
                    <span>3.</span> Accounts and Eligibility
                </h2>

                <p>
                    Users are responsible for providing accurate
                    registration and booking information.
                </p>

                <ul>

                    <li>
                        Provide accurate contact and booking details.
                    </li>

                    <li>
                        Keep your account access and OTP secure.
                    </li>

                    <li>
                        Do not share another person's private
                        information without appropriate permission.
                    </li>

                    <li>
                        Minors should use the platform with
                        appropriate parental or guardian involvement.
                    </li>

                    <li>
                        Pandits may be required to provide verification,
                        professional and payout information.
                    </li>

                </ul>

            </section>



            <!-- 4. BOOKINGS -->

            <section class="bd-policy-block">

                <h2>
                    <span>4.</span> Bookings and Live Sessions
                </h2>

                <p>
                    Before confirming a booking, please verify the
                    selected service, date, time, participant details,
                    sankalp information and applicable charges.
                </p>

                <p>
                    Booking confirmation depends on the applicable
                    booking process and payment status.
                </p>

                <p>
                    Online sessions may use third-party meeting services.
                    Participants should have a suitable device and
                    internet connection.
                </p>

                <p>
                    Scheduling may change because of pandit
                    availability, technical issues or circumstances
                    outside reasonable control.
                </p>

            </section>



            <!-- 5. PAYMENTS -->

            <section class="bd-policy-block">

                <h2>
                    <span>5.</span> Prices, Payments and Donations
                </h2>

                <p>
                    Service prices and applicable charges are
                    displayed during booking or checkout.
                </p>

                <p>
                    Payments are processed through an external
                    payment service provider.
                </p>

                <p>
                    Payment confirmation is subject to successful
                    transaction verification.
                </p>

                <p>
                    Optional donations and dakshina should
                    be made voluntarily.
                </p>

            </section>



            <!-- 6. REFUNDS -->

            <section class="bd-policy-block">

                <h2>
                    <span>6.</span> Cancellations and Refunds
                </h2>

                <p>
                    Cancellation, rescheduling and refund eligibility
                    may differ depending on the selected service,
                    booking status and applicable conditions.
                </p>

                <p>
                    If a booked service cannot proceed as arranged,
                    please contact support with your booking reference.
                </p>

                <p>
                    Where applicable, service issues and disputes
                    may be reviewed before a refund decision is made.
                </p>

                <p>
                    Approved refunds are processed according to
                    the payment provider's procedures and
                    applicable law.
                </p>

                <div class="bd-policy-callout">

                    <i class="bi bi-info-circle"
                       aria-hidden="true"></i>

                    <p>
                        The final cancellation and refund policy,
                        including eligibility and timeframes,
                        must be confirmed before publication.
                    </p>

                </div>

            </section>



            <!-- 7. ACCEPTABLE USE -->

            <section class="bd-policy-block">

                <h2>
                    <span>7.</span> Acceptable Use
                </h2>

                <p>
                    Users must use BhaktiDeep respectfully
                    and lawfully.
                </p>

                <ul>

                    <li>
                        Do not provide fraudulent information.
                    </li>

                    <li>
                        Do not harass other users or pandits.
                    </li>

                    <li>
                        Do not attempt unauthorized access.
                    </li>

                    <li>
                        Do not misuse private meeting links.
                    </li>

                    <li>
                        Do not record or redistribute private
                        sessions without appropriate permission.
                    </li>

                </ul>

            </section>



            <!-- 8. SPIRITUAL SERVICES -->

            <section class="bd-policy-block">

                <h2>
                    <span>8.</span> Nature of Spiritual Services
                </h2>

                <p>
                    BhaktiDeep facilitates devotional and
                    spiritual experiences.
                </p>

                <p>
                    Spiritual beliefs and outcomes are personal.
                    No particular spiritual, medical,
                    financial or other real-world result
                    is guaranteed.
                </p>

                <p>
                    Information provided through the platform
                    should not replace qualified medical,
                    legal or financial advice.
                </p>

            </section>



            <!-- 9. CONTENT -->

            <section class="bd-policy-block">

                <h2>
                    <span>9.</span> Platform Content and Privacy
                </h2>

                <p>
                    Unless otherwise specified, BhaktiDeep
                    branding, website design and platform
                    materials belong to BhaktiDeep or
                    their respective rights holders.
                </p>

                <p>
                    Users must have the necessary rights
                    to share uploaded content, reviews,
                    messages and supporting documents.
                </p>

                <p>
                    Personal information is handled according to our
                    <a href="{{ route('privacy-policy') }}">
                        Privacy Policy
                    </a>.
                </p>

            </section>



            <!-- 10. AVAILABILITY -->

            <section class="bd-policy-block">

                <h2>
                    <span>10.</span> Platform Availability
                </h2>

                <p>
                    We work to keep BhaktiDeep accessible
                    and functional.
                </p>

                <p>
                    However, website features, payment
                    integrations and third-party meeting
                    services may occasionally experience
                    interruptions.
                </p>

                <p>
                    Nothing in these terms excludes
                    rights or remedies that cannot
                    lawfully be excluded.
                </p>

            </section>



            <!-- 11. CHANGES -->

            <section class="bd-policy-block">

                <h2>
                    <span>11.</span> Changes to These Terms
                </h2>

                <p>
                    These terms may be updated as our
                    services, platform or applicable
                    requirements change.
                </p>

                <p>
                    The latest revision date will be
                    displayed on this page.
                </p>

            </section>



            <!-- 12. CONTACT -->

            <section class="bd-policy-block">

                <h2>
                    <span>12.</span> Contact Information
                </h2>

                <p>
                    For questions regarding these terms,
                    payments or services, please visit our
                    <a href="{{ route('contact') }}">
                        Contact Us
                    </a>
                    page.
                </p>

                <p>
                    <strong>Platform Operator:</strong>
                    [INSERT LEGAL ENTITY NAME]
                </p>

                <p>
                    <strong>Business Address:</strong>
                    [INSERT BUSINESS ADDRESS]
                </p>

                <p>
                    <strong>Applicable Law:</strong>
                    [CONFIRM WITH LEGAL ADVISER]
                </p>

            </section>


        </article>



        <div class="bd-policy-bottom">

            <p>
                Have a question?
                <a href="{{ route('contact') }}">
                    Contact us
                </a>.
            </p>

            <p>
                Also read our
                <a href="{{ route('privacy-policy') }}">
                    Privacy Policy
                </a>.
            </p>

        </div>


    </div>

</main>

@endsection