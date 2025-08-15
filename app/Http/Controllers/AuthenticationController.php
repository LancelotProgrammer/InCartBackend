<?php

namespace App\Http\Controllers;

use App\Exceptions\AuthenticationException;
use App\Http\Resources\Authentication\UserInfoResource;
use App\Http\Resources\Authentication\UserResource;
use App\Http\Resources\EmptySuccessfulResponseResource;
use App\Http\Resources\SuccessfulResponseResource;
use App\Mail\ForgotPassword;
use App\Mail\VerifyEmail;
use App\Models\EmailVerificationRequest;
use App\Models\PasswordResetRequest;
use App\Models\PhoneVerificationRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\UserFirebaseToken;
use App\Services\Otp;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InvalidArgumentException;

/*
    TODO:
        - test update user info, middleware for optional auth, middleware for blocked users
        - null edge case at model::where
        - improve phone number validation
        - refactor code to use services
        - refactor code to use repositories
        - write tests for all functions
*/

class AuthenticationController extends Controller
{
    private const PASSWORD_ATTEMPTS = 5;

    private const OTP_ATTEMPTS = 10;

    private const EMAIL_VERIFICATION_ATTEMPTS = 10;

    private const EMAIL_VERIFICATION_DAYS = 30;

    private const FORGOT_PASSWORD_ATTEMPTS = 10;

    private const FORGOT_PASSWORD_DAYS = 30;

    /**
     * @unauthenticated
     *
     * @group Authentication Actions
     */
    public function emailRegister(Request $request): SuccessfulResponseResource
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'cityId' => 'required|int|exists:cities,id',
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'city_id' => $request->input('cityId'),
            'role_id' => Role::where('title', '=', 'user')->value('id'),
        ]);

        $this->requestVerifyEmail($user->id, $request->input('email'));

        return new SuccessfulResponseResource(
            $this->createUserResponse($user)
        );
    }

    /**
     * @unauthenticated
     *
     * @group Authentication Actions
     */
    public function emailLogin(Request $request): SuccessfulResponseResource
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $ip = $request->ip();
        $user = User::where('email', $request->input('email'))->first();
        if (! $user) {
            throw new AuthenticationException(
                trans('auth.credentials_are_incorrect'),
                'The user email is not found'
            );
        }
        if (RateLimiter::tooManyAttempts("Login|$user->email|$ip", self::PASSWORD_ATTEMPTS)) {
            RateLimiter::increment("Login|$user->email|$ip");
            throw new AuthenticationException(
                trans('auth.credentials_are_incorrect'),
                'Too many login attempts'
            );
        }
        if (! Hash::check($request->input('password'), $user->password)) {
            RateLimiter::increment("Login|$user->email|$ip");
            throw new AuthenticationException(
                trans('auth.credentials_are_incorrect'),
                'Password is wrong'
            );
        }
        $user->tokens()->delete();

        return new SuccessfulResponseResource(
            $this->createUserResponse($user)
        );
    }

    /**
     * @unauthenticated
     *
     * @group Authentication Actions
     */
    public function phoneRegister(Request $request): EmptySuccessfulResponseResource
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'cityId' => 'required|int|exists:cities,id',
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'city_id' => $request->input('cityId'),
            'role_id' => Role::where('title', '=', 'user')->value('id'),
        ]);

        $this->requestVerifyPhone($user->id, $request->input('phone'));

        return new EmptySuccessfulResponseResource;
    }

    /**
     * @unauthenticated
     *
     * @group Authentication Actions
     */
    public function phoneLogin(Request $request): EmptySuccessfulResponseResource
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $user = User::where('phone', $request->input('phone'))->first();
        if (! $user) {
            throw new AuthenticationException(
                trans('auth.credentials_are_incorrect'),
                'The user phone is not found'
            );
        }

        $this->requestVerifyPhone($user->id, $request->input('phone'));

        return new EmptySuccessfulResponseResource;
    }

    /**
     * @group Authentication Actions
     */
    public function logout(Request $request): EmptySuccessfulResponseResource
    {
        $request->user()->currentAccessToken()->delete();

        return new EmptySuccessfulResponseResource;
    }

    /**
     * @unauthenticated
     *
     * @group Verify OTP
     */
    public function verifyOtp(Request $request): SuccessfulResponseResource
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string',
        ]);

        $phone = $request->input('phone');
        $code = $request->input('code');

        if (RateLimiter::tooManyAttempts("VerifyPhone|$phone", self::OTP_ATTEMPTS)) {
            throw new AuthenticationException(
                trans('auth.opt_verification_error'),
                'Too many attempts'
            );
        }

        $phoneVerificationRequest = PhoneVerificationRequest::where('code', $code)->first();
        if (! $phoneVerificationRequest || $phoneVerificationRequest->phone !== $phone) {
            RateLimiter::increment("VerifyPhone|$phone");
            throw new AuthenticationException(
                trans('auth.opt_verification_error'),
                'Code is invalid'
            );
        }

        $requestDate = Carbon::parse($phoneVerificationRequest->created_at);
        if (Carbon::now()->diffInMinutes($requestDate) > 1) {
            PhoneVerificationRequest::where('user_id', $phoneVerificationRequest->user_id)->delete();
            RateLimiter::increment("VerifyPhone|$phone");
            throw new AuthenticationException(
                trans('auth.opt_verification_error'),
                'Code has expired'
            );
        }

        User::where('id', $phoneVerificationRequest->user_id)
            ->update([
                'phone_verified_at' => Carbon::now(),
                'phone' => $phoneVerificationRequest->phone,
            ]);
        PhoneVerificationRequest::where('user_id', $phoneVerificationRequest->user_id)->delete();

        RateLimiter::clear("VerifyPhone|$phone");

        $user = User::where('phone', $phone)->first();

        if ($user === null) {
            throw new AuthenticationException(
                trans('auth.opt_verification_error'),
                'User phone not found'
            );
        }

        return new SuccessfulResponseResource(
            $this->createUserResponse($user)
        );
    }

    private function createUserResponse(User $user): UserResource
    {
        $accessToken = $user->createToken('access_token', ['*'])->plainTextToken;

        return new UserResource($user, $accessToken);
    }

    /**
     * @group Account Management
     */
    public function getUser(Request $request): SuccessfulResponseResource
    {
        return new SuccessfulResponseResource(
            new UserInfoResource($request->user())
        );
    }

    /**
     * @group Account Management
     */
    public function createFirebaseToken(Request $request): EmptySuccessfulResponseResource
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        UserFirebaseToken::updateOrCreate(
            ['user_id' => $request->user()->id],
            ['token' => $request->input('token')]
        );

        return new EmptySuccessfulResponseResource;
    }

    /**
     * @group Account Management
     */
    public function updateUserPhone(Request $request): EmptySuccessfulResponseResource
    {
        $request->validate([
            'phone' => 'required|string|unique:users,phone,'.$request->user()->id,
        ]);

        $user = $request->user();
        $user->update(['phone' => $request->input('phone')]);

        $this->requestVerifyPhone($user->id, $request->input('phone'));

        return new EmptySuccessfulResponseResource;
    }

    /**
     * @group Account Management
     */
    public function updateUserEmail(Request $request): EmptySuccessfulResponseResource
    {
        $request->validate([
            'email' => 'required|email|unique:users,email,'.$request->user()->id,
        ]);

        $user = $request->user();
        $user->update(['email' => $request->input('email')]);

        $this->requestVerifyEmail($user->id, $request->input('email'));

        return new EmptySuccessfulResponseResource;
    }

    /**
     * @group Account Management
     */
    public function updateUserName(Request $request): EmptySuccessfulResponseResource
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = $request->user();
        $user->update(['name' => $request->input('name')]);

        return new EmptySuccessfulResponseResource;
    }

    /**
     * @group Account Management
     */
    public function updateUserPassword(Request $request): EmptySuccessfulResponseResource
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8',
        ]);

        $user = $request->user();
        if (! Hash::check($request->input('current_password'), $user->password)) {
            throw new AuthenticationException(
                trans('auth.current_password_is_incorrect'),
                'The provided current password is incorrect.'
            );
        }

        $user->update(['password' => Hash::make($request->input('new_password'))]);

        return new EmptySuccessfulResponseResource;
    }

    private function requestVerifyPhone(int $userId, string $phone): void
    {
        $code = $this->generateRandomNumber();

        PhoneVerificationRequest::updateOrInsert(
            ['user_id' => $userId],
            [
                'phone' => $phone,
                'code' => $code,
                'created_at' => now(),
            ]
        );
        $verifyPhone = PhoneVerificationRequest::where('phone', $phone)->where('code', $code)->first();

        try {
            Otp::send($phone, $verifyPhone->code);
        } catch (Exception $e) {
            Log::channel('error')->error('OTP Error'.$e->getMessage());
            throw new AuthenticationException(
                trans('auth.something_went_wrong'),
                'OTP verification request failed. look for OTP Error in error logs for more details'
            );
        }
    }

    public function verifyEmail(Request $request): View
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        $email = $request->input('email');
        $token = $request->input('token');

        if (RateLimiter::tooManyAttempts("VerifyEmail|$email", self::EMAIL_VERIFICATION_ATTEMPTS)) {
            RateLimiter::increment("VerifyEmail|$email");

            return view('pages.email-verification-failure');
        }

        $emailVerificationRequest = EmailVerificationRequest::where('token', $token)->first();
        if (! $emailVerificationRequest || $emailVerificationRequest->email !== $email) {
            RateLimiter::increment("VerifyEmail|$email");

            return view('pages.email-verification-failure');
        }

        $requestDate = Carbon::parse($emailVerificationRequest->created_at);
        if (Carbon::now()->diffInDays($requestDate) > self::EMAIL_VERIFICATION_DAYS) {
            EmailVerificationRequest::where('user_id', $emailVerificationRequest->user_id)->delete();
            RateLimiter::increment("VerifyEmail|$email");

            return view('pages.email-verification-failure');
        }

        User::where('id', $emailVerificationRequest->user_id)
            ->update([
                'email_verified_at' => Carbon::now(),
                'email' => $emailVerificationRequest->email,
            ]);
        EmailVerificationRequest::where('user_id', $emailVerificationRequest->user_id)->delete();

        RateLimiter::clear("VerifyEmail|$email");

        return view('pages.email-verification-success');
    }

    /**
     * @unauthenticated
     *
     * @group Request Verify Email
     */
    public function getVerifyEmail(Request $request): EmptySuccessfulResponseResource
    {
        $this->requestVerifyEmail($request->user()->id);

        return new EmptySuccessfulResponseResource;
    }

    public function requestVerifyEmail(int $userId, ?string $email = null): void
    {
        $user = User::find($userId);
        if ($email === null) {
            if ($user->email_verified_at !== null) {
                throw new AuthenticationException(
                    trans('auth.user_is_verified'),
                    'User is verified'
                );
            }
            $this->sendVerifyEmailToEmail($user, $user->email);
        } else {
            $this->sendVerifyEmailToEmail($user, $email);
        }
    }

    public function sendVerifyEmailToEmail(User $user, string $email): void
    {
        $token = Str::random();

        EmailVerificationRequest::updateOrInsert(
            ['user_id' => $user->id],
            [
                'email' => $email,
                'token' => $token,
                'created_at' => now(),
            ]
        );
        $verifyEmail = EmailVerificationRequest::where('email', $email)->where('token', $token)->first();

        try {
            Mail::to($email)->send(new VerifyEmail($user->name, $verifyEmail->email, $verifyEmail->token));
        } catch (Exception $e) {
            Log::channel('error')->error('Email Error'.$e->getMessage());
            throw new AuthenticationException(
                trans('auth.something_went_wrong'),
                'Email verification request failed. look for Email Error in error logs for more details'
            );
        }
    }

    /**
     * @unauthenticated
     *
     * @group Forgot password
     */
    public function forgotPasswordRequest(Request $request): EmptySuccessfulResponseResource
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = $request->input('email');

        $rateLimitKey = "ForgotPasswordRequest|$email";

        if (RateLimiter::tooManyAttempts($rateLimitKey, self::FORGOT_PASSWORD_ATTEMPTS)) {
            throw new AuthenticationException(
                trans('auth.something_went_wrong'),
                'Too many requests'
            );
        }

        $user = User::where('email', $email)->first();
        if (! $user) {
            RateLimiter::increment($rateLimitKey);
            throw new AuthenticationException(
                trans('auth.something_went_wrong'),
                'User not found'
            );
        }
        if ($user->email_verified_at === null) {
            RateLimiter::increment($rateLimitKey);
            throw new AuthenticationException(
                trans('auth.something_went_wrong'),
                'Email is not verified'
            );
        }

        $this->validateUserStatus($user);

        RateLimiter::clear($rateLimitKey);

        $code = $this->createAndStoreForgotPasswordCode($user->id);
        $this->sendEmailForResetPassword($user->email, $user->name, $code);

        return new EmptySuccessfulResponseResource;
    }

    private function createAndStoreForgotPasswordCode(int $userId): string
    {
        $code = $this->generateRandomNumber();
        PasswordResetRequest::updateOrInsert(
            ['user_id' => $userId],
            [
                'code' => $code,
                'attempts' => 0,
                'created_at' => now(),
            ]
        );

        return $code;
    }

    private function sendEmailForResetPassword(string $email, string $userName, string $code): void
    {
        try {
            Mail::to($email)->send(new ForgotPassword($userName, $code));
        } catch (Exception $e) {
            Log::channel('error')->error('Email Error'.$e->getMessage());
            throw new AuthenticationException(
                trans('auth.something_went_wrong'),
                'Email verification request failed. look for Email Error in error logs for more details'
            );
        }
    }

    /**
     * @unauthenticated
     *
     * @group Forgot password
     */
    public function verifyForgetPasswordRequest(Request $request): SuccessfulResponseResource
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|int',
        ]);

        $email = $request->input('email');
        $code = $request->input('code');

        $user = User::where('email', $email)->first();
        $PasswordResetRequest = PasswordResetRequest::where('user_id', $user->id)->first();

        if (! $PasswordResetRequest) {
            throw new AuthenticationException(
                trans('auth.something_went_wrong'),
                'Password reset request not found'
            );
        }

        if ($PasswordResetRequest->attempts >= self::FORGOT_PASSWORD_ATTEMPTS) {
            PasswordResetRequest::where('user_id', $user->id)->delete();
            throw new AuthenticationException(
                trans('auth.something_went_wrong'),
                'Password reset request has been deleted because of too many attempts'
            );
        }

        $passwordReset = PasswordResetRequest::where('user_id', $user->id)->where('code', $code)->first();
        if (! $passwordReset) {
            PasswordResetRequest::where('user_id', $user->id)->increment('attempts');
            throw new AuthenticationException(
                trans('auth.something_went_wrong'),
                'Password reset code is invalid'
            );
        }

        if (Carbon::now()->diffInMinutes(Carbon::parse($passwordReset->created_at)) >= self::FORGOT_PASSWORD_DAYS) {
            throw new AuthenticationException(
                trans('auth.something_went_wrong'),
                'Password reset code expired'
            );
        }
        do {
            $token = Str::random(16);
        } while (PasswordResetRequest::where('token', $token)->exists());

        PasswordResetRequest::where('user_id', $user->id)->update(['token' => $token]);

        return new SuccessfulResponseResource(
            [
                'token' => $token,
            ]
        );
    }

    /**
     * @unauthenticated
     *
     * @group Forgot password
     */
    public function resetPasswordRequest(Request $request): EmptySuccessfulResponseResource
    {
        $request->validate([
            'password' => 'required|string|min:8',
            'token' => 'required|string',
            'logoutFromAll' => 'boolean',
        ]);

        $password = $request->input('password');
        $token = $request->input('token');
        $logoutFromAll = $request->input('logoutFromAll', false);

        $PasswordReset = PasswordResetRequest::where('token', $token)->first();
        if (! $PasswordReset) {
            throw new AuthenticationException(
                trans('auth.something_went_wrong'),
                'Token or code are invalid'
            );
        }

        if (Carbon::now()->diffInMinutes(Carbon::parse($PasswordReset->created_at)) >= self::FORGOT_PASSWORD_DAYS) {
            throw new AuthenticationException(
                trans('auth.something_went_wrong'),
                'Reset password code has expired'
            );
        }

        $user = User::find($PasswordReset->user_id);

        if (Hash::check($password, $user->password)) {
            throw new AuthenticationException(
                trans('auth.something_went_wrong'),
                'New password must be different from old password'
            );
        }

        $user->update(['password' => Hash::make($password)]);
        if ($logoutFromAll) {
            $this->logoutFromAll($user->id);
            PasswordResetRequest::where('user_id', $user->id)->where('token', $token)->delete();
        }
        PasswordResetRequest::where('user_id', $user->id)->where('token', $token)->delete();

        return new EmptySuccessfulResponseResource;
    }

    private function logoutFromAll(int $userId): void
    {
        User::find($userId)->tokens()->delete();
    }

    public function generateRandomNumber(int $length = 6): string
    {
        do {
            $randomNumber = self::generateSecurePINCode($length);
        } while (self::isWeakNumber($randomNumber));

        return $randomNumber;
    }

    public function generateSecurePINCode(int $keyLength = 4): string
    {
        if ($keyLength <= 0) {
            throw new InvalidArgumentException('PIN code length must be positive.');
        }

        $key = '';
        for ($x = 1; $x <= $keyLength; $x++) {
            $key .= random_int(0, 9);
        }

        return $key;
    }

    private function isWeakNumber(string $number): bool
    {
        $length = strlen($number);
        if (preg_match('/^(\d)\1{'.($length - 1).'}$/', $number)) {
            return true;
        }
        for ($patternLength = 1; $patternLength <= $length / 2; $patternLength++) {
            if ($length % $patternLength !== 0) {
                continue;
            }

            $pattern = substr($number, 0, $patternLength);
            $repetitions = $length / $patternLength;
            $builtNumber = str_repeat($pattern, $repetitions);

            if ($builtNumber === $number) {
                return true;
            }
        }

        return false;
    }

    public function validateUserStatus(User $user): void
    {
        if ($user->blocked_at !== null) {
            throw new AuthenticationException(
                trans('auth.user_is_blocked'),
                'User is blocked'
            );
        }
    }
}
