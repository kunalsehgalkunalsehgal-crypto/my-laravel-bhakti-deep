<?php

namespace App\Http\Controllers;

use App\Models\Pandit\Pandit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View
    {
        if ($request->boolean('change')) {
            $this->clearOtpFlow();
        }

        return view('pages.login');
    }

    public function showUserRegister(Request $request): View
    {
        if ($request->boolean('change')) {
            $this->clearOtpFlow();
        }

        return view('pages.signup', ['accountType' => 'user']);
    }

    public function showPanditRegister(Request $request): View
    {
        if ($request->boolean('change')) {
            $this->clearOtpFlow();
        }

        return view('pandit.register', ['accountType' => 'pandit']);
    }

    public function sendOTP(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = Str::lower($request->email);
        $account = $this->findAccountByEmail($email);

        if (!$account) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'No account found with this Gmail. Please register first.']);
        }

        if ($account['type'] === 'conflict') {
            return back()
                ->withInput()
                ->withErrors(['email' => 'This Gmail is linked to more than one account. Please contact support.']);
        }

        $this->startOtpFlow('login', $account['type'], [
            'email' => $email,
        ]);

        return back()
            ->withInput(['email' => $email, '_otp_pending' => 'login'])
            ->with('success', 'OTP sent to your Gmail address.');
    }

    public function verifyOTP(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'otp' => ['required', 'digits:6'],
        ]);

        $flow = Session::get('otp_flow');

        if (!$this->isValidOtpFlow($flow, 'login', $request->email, $request->otp)) {
            return back()
                ->withInput(['email' => $request->email, '_otp_pending' => 'login'])
                ->withErrors(['otp' => 'Invalid or expired OTP. Please try again.']);
        }

        $account = $this->findAccountByEmail($flow['email']);

        if (!$account || $account['type'] === 'conflict') {
            $this->clearOtpFlow();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Account not found. Please register again.']);
        }

        if ($account['type'] === 'pandit') {
            Auth::guard('pandit')->login($account['model']);
            $request->session()->regenerate();
            Session::put('pandit_id', $account['model']->id);
            $this->clearOtpFlow();

            return redirect()
                ->route('pandit.dashboard')
                ->with('success', 'Pandit login successful.');
        }

        Auth::guard('web')->login($account['model']);
        $request->session()->regenerate();
        $this->clearOtpFlow();

        return redirect()
            // ->route ('home')
                ->intended(route('home'))

            ->with('success', 'Login successful.');
    }

    public function sendRegistrationOtp(Request $request, string $type): RedirectResponse
    {
        abort_unless(in_array($type, ['user', 'pandit'], true), 404);

        $request->validate([
            'full_name' => ['required', 'string', 'min:2', 'max:255'],
            'mobile' => ['required', 'digits:10'],
            'email' => ['required', 'email', 'max:255'],
            'terms' => ['accepted'],
        ]);

        $email = Str::lower($request->email);
        $mobile = $request->mobile;

        if ($this->findAccountByEmail($email)) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'This Gmail is already registered. Please login instead.']);
        }

        if (User::where('mobile', $mobile)->exists() || Pandit::where('mobile', $mobile)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['mobile' => 'This phone number is already registered.']);
        }

        $this->startOtpFlow('register', $type, [
            'full_name' => $request->full_name,
            'mobile' => $mobile,
            'email' => $email,
        ]);

        return back()
            ->withInput([
                'full_name' => $request->full_name,
                'mobile' => $mobile,
                'email' => $email,
                'terms' => '1',
                '_otp_pending' => $type,
            ])
            ->with('success', 'OTP sent to your Gmail address.');
    }

    public function verifyRegistrationOtp(Request $request, string $type): RedirectResponse
    {
        abort_unless(in_array($type, ['user', 'pandit'], true), 404);

        $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'otp' => ['required', 'digits:6'],
        ]);

        $flow = Session::get('otp_flow');

        if (!$this->isValidOtpFlow($flow, 'register', $request->email, $request->otp, $type)) {
            return back()
                ->withInput([
                    'email' => $request->email,
                    '_otp_pending' => $type,
                ])
                ->withErrors(['otp' => 'Invalid or expired OTP. Please try again.']);
        }

        if ($this->findAccountByEmail($flow['email'])) {
            $this->clearOtpFlow();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'This Gmail is already registered. Please login.']);
        }

        if ($type === 'pandit') {
            $pandit = Pandit::create([
                'full_name' => $flow['full_name'],
                'pandit_name' => $flow['full_name'],
                'mobile' => $flow['mobile'],
                'email' => $flow['email'],
                'country' => 'India',
                'status' => 'under_review',
            ]);

            Auth::guard('pandit')->login($pandit);
            $request->session()->regenerate();
            Session::put('pandit_id', $pandit->id);
            $this->clearOtpFlow();

            return redirect()
                ->route('pandit.dashboard')
                ->with('success', 'Pandit registration successful. Complete your profile for approval.');
        }

        $user = User::create([
            'name' => $flow['full_name'],
            'mobile' => $flow['mobile'],
            'email' => $flow['email'],
            'email_verified_at' => now(),
            'password' => Str::random(40),
        ]);

        Auth::guard('web')->login($user);
        $request->session()->regenerate();
        $this->clearOtpFlow();

        return redirect()
            // ->route('home')
                ->intended(route('home'))
            ->with('success', 'Registration successful.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Logged out successfully.');
    }

    private function findAccountByEmail(string $email): ?array
    {
        $email = Str::lower($email);
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        $pandit = Pandit::whereRaw('LOWER(email) = ?', [$email])->first();

        if ($user && $pandit) {
            return ['type' => 'conflict', 'model' => null];
        }

        if ($pandit) {
            return ['type' => 'pandit', 'model' => $pandit];
        }

        if ($user) {
            return ['type' => 'user', 'model' => $user];
        }

        return null;
    }

    private function startOtpFlow(string $context, string $type, array $data): void
    {
        $otp = (string) random_int(100000, 999999);

        Session::put('otp_flow', [
            ...$data,
            'context' => $context,
            'type' => $type,
            'otp_hash' => Hash::make($otp),
            'otp_preview' => $this->shouldShowDevOtp() ? $otp : null,
            'expires_at' => now()->addMinutes(1)->toIso8601String(),
        ]);

        $subject = $context === 'register'
            ? 'Your BhaktiDeep registration OTP'
            : 'Your BhaktiDeep login OTP';
        $greetingName = $data['full_name'] ?? 'Devotee';

        Mail::send('emails.otp', [
            'greetingName' => $greetingName,
            'otp' => $otp,
            'subject' => $subject,
            'context' => $context,
        ], fn ($message) => $message->to($data['email'])->subject($subject));
    }

    private function isValidOtpFlow(?array $flow, string $context, string $email, string $otp, ?string $type = null): bool
    {
        if (!$flow || ($flow['context'] ?? null) !== $context) {
            return false;
        }

        if ($type && ($flow['type'] ?? null) !== $type) {
            return false;
        }

        if (!hash_equals($flow['email'] ?? '', Str::lower($email))) {
            return false;
        }

        if (Carbon::parse($flow['expires_at'] ?? now()->subMinute())->isPast()) {
            return false;
        }

        if (Hash::check($otp, $flow['otp_hash'] ?? '')) {
            return true;
        }

        return $this->shouldShowDevOtp()
            && !empty($flow['otp_preview'])
            && hash_equals($flow['otp_preview'], $otp);
    }

    private function clearOtpFlow(): void
    {
        Session::forget('otp_flow');
    }

    private function shouldShowDevOtp(): bool
    {
        return app()->environment('local') || config('mail.default') === 'log';
    }
}
