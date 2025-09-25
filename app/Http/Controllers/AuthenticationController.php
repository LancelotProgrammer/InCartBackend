<?php

namespace App\Http\Controllers;

use App\Enums\OtpType;
use App\Exceptions\AuthenticationException;
use App\Exceptions\SetupException;
use App\Http\Resources\Authentication\UserInfoResource;
use App\Http\Resources\Authentication\UserResource;
use App\Http\Resources\EmptySuccessfulResponseResource;
use App\Http\Resources\SuccessfulResponseResource;
use App\Mail\ForgotPassword;
use App\Mail\VerifyEmail;
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
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;
use InvalidArgumentException;

class AuthenticationController extends Controller
{
    private const PASSWORD_ATTEMPTS = 5;

    private const OTP_ATTEMPTS = 10;

    private const OTP_VERIFICATION_MINUTES = 2;

    private const FORGOT_PASSWORD_ATTEMPTS = 10;

    private const FORGOT_PASSWORD_DAYS = 30;

    /**
     * @unauthenticated
     * @group Authentication Actions
     */
    public function emailRegister(Request $request): SuccessfulResponseResource
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'city_id' => 'required|int|exists:cities,id',
        ]);

        $roleId = Role::where('code', '=', 'user')->value('id');
        if (!$roleId) {
            throw new SetupException(
                trans('auth.something_went_wrong'),
                'System setup error. Pleas add a user role to the system'
            );
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'city_id' => $request->input('city_id'),
            'role_id' => $roleId,
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
        $user = User::where('email', '=', $request->input('email'))->first();

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

        $this->validateUserStatus($user);

        return new SuccessfulResponseResource(
            $this->createUserResponse($user)
        );
    }

    /**
     * @unauthenticated
     *
     * @group Authentication Actions
     */
    public function phoneRegister(Request $request): SuccessfulResponseResource
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|phone:SA|unique:users,phone',
            'otp' => 'required|integer',
            'city_id' => 'required|int|exists:cities,id',
        ]);

        $this->verifyOtp($request->input('phone'), $request->input('otp'), OtpType::REGISTER->value);

        $roleId = Role::where('code', '=', 'user')->value('id');
        if (!$roleId) {
            throw new SetupException(
                trans('auth.something_went_wrong'),
                'System setup error. Pleas add a user role to the system'
            );
        }

        $user = User::create([
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'phone_verified_at' => Carbon::now(),
            'city_id' => $request->input('city_id'),
            'role_id' => $roleId,
        ]);

        return new SuccessfulResponseResource(
            $this->createUserResponse($user)
        );
    }

    /**
     * @unauthenticated
     *
     * @group Authentication Actions
     */
    public function phoneLogin(Request $request): SuccessfulResponseResource
    {
        $request->validate([
            'phone' => 'required|phone:SA',
            'otp' => 'required|integer',
        ]);

        $user = User::where('phone', '=', $request->input('phone'))->first();

        if (! $user) {
            throw new AuthenticationException(
                trans('auth.credentials_are_incorrect'),
                'The user phone is not found'
            );
        }

        $this->verifyOtp($request->input('phone'), $request->input('otp'), OtpType::LOGIN->value);

        $this->validateUserStatus($user);

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
            'firebase_token' => 'required|string',
        ]);

        UserFirebaseToken::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'token_id' => $request->user()->currentAccessToken()->id,
            ],
            [
                'firebase_token' => $request->input('firebase_token')
            ]
        );

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
     * @group Account Management
     *
     * @bodyParam name string The user’s name. Example: John Doe
     */
    public function updateUser(Request $request): EmptySuccessfulResponseResource
    {
        // future: enable this to allow users to update their phone number / email / password
        //  * @bodyParam phone string The user’s phone number. Example: +123456789
        //  * @bodyParam otp integer The user’s phone number otp. Example: 123456
        // if ($request->has('phone') && $request->has('otp')) {
        //     return $this->updateUserPhone($request);
        // }
        // if ($request->has('email')) {
        //     return $this->updateUserEmail($request);
        // }
        // if ($request->has(['current_password', 'new_password'])) {
        //     return $this->updateUserPassword($request);
        // }

        if ($request->has('name')) {
            return $this->updateUserName($request);
        }

        throw new InvalidArgumentException('No valid update field provided.');
    }

    private function updateUserName(Request $request): EmptySuccessfulResponseResource
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = $request->user();
        $user->update(['name' => $request->input('name')]);

        return new EmptySuccessfulResponseResource;
    }

    // future: enable this to allow users to update their phone number / email / password
    // private function updateUserPhone(Request $request): EmptySuccessfulResponseResource
    // {
    //     $this->validateUserAccountType($request->user(), UserAccountType::PHONE);
    //     $request->validate([
    //         'phone' => 'required|phone:SA|unique:users,phone,' . $request->user()->id,
    //         'otp' => 'required|integer',
    //     ]);
    //     $this->verifyOtp($request->input('phone'), $request->input('otp'), OtpType::UPDATE->value);
    //     $user = $request->user();
    //     $user->update(['phone' => $request->input('phone')]);
    //     return new EmptySuccessfulResponseResource;
    // }

    // private function updateUserEmail(Request $request): EmptySuccessfulResponseResource
    // {
    //     $this->validateUserAccountType($request->user(), UserAccountType::EMAIL);
    //     $request->validate([
    //         'email' => 'required|email|unique:users,email,' . $request->user()->id,
    //     ]);
    //     $user = $request->user();
    //     $user->update(['pending_email' => $request->input('email')]);
    //     $this->requestVerifyEmail($user->id, $request->input('email'));
    //     return new EmptySuccessfulResponseResource;
    // }

    // private function updateUserPassword(Request $request): EmptySuccessfulResponseResource
    // {
    //     $this->validateUserAccountType($request->user(), UserAccountType::EMAIL);
    //     $request->validate([
    //         'current_password' => 'required|string',
    //         'new_password' => 'required|string|min:8',
    //     ]);
    //     $user = $request->user();
    //     if (! Hash::check($request->input('current_password'), $user->password)) {
    //         throw new AuthenticationException(
    //             trans('auth.current_password_is_incorrect'),
    //             'The provided current password is incorrect.'
    //         );
    //     }
    //     $user->update(['password' => Hash::make($request->input('new_password'))]);
    //     return new EmptySuccessfulResponseResource;
    // }

    // private function validateUserAccountType(User $user, UserAccountType $userAccountType): void
    // {
    //     $isValid = match ($userAccountType) {
    //         UserAccountType::PHONE => !empty($user->phone) && empty($user->email),
    //         UserAccountType::EMAIL => !empty($user->email),
    //     };
    //     if (! $isValid) {
    //         throw new AuthenticationException(
    //             trans('auth.something_went_wrong'),
    //             'The provided account type does not match the user.'
    //         );
    //     }
    // }

    // public function addCredentials(Request $request): EmptySuccessfulResponseResource
    // {
    //     $validated = $request->validate([
    //         'email' => 'required|email|unique:users,email',
    //         'password' => 'required|string|min:8',
    //     ]);
    //     $user = $request->user();
    //     $this->validateUserAccountType($user, UserAccountType::PHONE);
    //     $user->update([
    //         'email' => $validated['email'],
    //         'password' => Hash::make($validated['password']),
    //     ]);
    //     return new EmptySuccessfulResponseResource();
    // }

    /**
     * @unauthenticated
     * @bodyParam phone string required The user’s phone number. Example: +123456789
     * @bodyParam type string required The OTP screen type 1 => REGISTER, 2 => LOGIN, 3 => UPDATE. Example: 1
     * @group Send OTP
     */
    public function sendOtp(Request $request): EmptySuccessfulResponseResource
    {
        $request->validate([
            'phone' => 'required|phone:SA',
            'type' => ['required', new Enum(OtpType::class)]
        ]);

        $phone = $request->input('phone');
        $type = $request->input('type');
        $rateLimiterKey = "SendOtp|$phone|$type";

        if (RateLimiter::tooManyAttempts($rateLimiterKey, self::OTP_ATTEMPTS)) {
            throw new AuthenticationException(
                trans('auth.opt_resending_error'),
                'Too many attempts'
            );
        }

        $phoneVerificationRequest = PhoneVerificationRequest::where('phone', '=', $phone)
            ->where('type', '=', $type)
            ->first();

        if ($phoneVerificationRequest) {

            if (! (Carbon::parse($phoneVerificationRequest->created_at)->diffInMinutes(Carbon::now()) > self::OTP_VERIFICATION_MINUTES)) {
                RateLimiter::increment($rateLimiterKey);
                throw new AuthenticationException(
                    trans('auth.opt_resending_error'),
                    'Code is not expired to resend'
                );
            }

            $phoneVerificationRequest->update([
                'code' => '123456', // TODO: replace with $this->generateRandomNumber() in production
                'created_at' => now(),
            ]);
        } else {
            PhoneVerificationRequest::create([
                'phone' => $phone,
                'code' => '123456', // TODO: replace with $this->generateRandomNumber() in production
                'created_at' => now(),
                'type' => $type
            ]);
        }

        try {
            $verifyCode = PhoneVerificationRequest::where('phone', '=', $phone)->latest()->first()->code;
            Otp::send($phone, $verifyCode);
        } catch (Exception $e) {
            Log::channel('error')->error('OTP Error: ' . $e->getMessage());
            throw new AuthenticationException(
                trans('auth.something_went_wrong'),
                'OTP verification request failed. Check error logs for details.'
            );
        }

        RateLimiter::clear($rateLimiterKey);
        return new EmptySuccessfulResponseResource;
    }

    private function verifyOtp(string $phone, string $code, int $type): void
    {
        $rateLimiterKey = "VerifyPhone|$phone|$type";

        if (RateLimiter::tooManyAttempts($rateLimiterKey, self::OTP_ATTEMPTS)) {
            throw new AuthenticationException(
                trans('auth.opt_verification_error'),
                'Too many attempts'
            );
        }

        $phoneVerificationRequest = PhoneVerificationRequest::where('code', '=', $code)
            ->where('type', '=', $type)
            ->where('phone', '=', $phone)
            ->first();

        if (! $phoneVerificationRequest) {
            RateLimiter::increment($rateLimiterKey);
            throw new AuthenticationException(
                trans('auth.opt_verification_error'),
                'Code or phone is invalid'
            );
        }

        if (Carbon::parse($phoneVerificationRequest->created_at)->diffInMinutes(Carbon::now()) > self::OTP_VERIFICATION_MINUTES) {
            RateLimiter::increment($rateLimiterKey);
            throw new AuthenticationException(
                trans('auth.opt_verification_error'),
                'Code has expired'
            );
        }

        PhoneVerificationRequest::where('phone', '=', $phoneVerificationRequest->phone)->delete();
        RateLimiter::clear($rateLimiterKey);
    }

    /**
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
            if ($user->email === null) {
                throw new AuthenticationException(
                    trans('auth.user_is_verified'),
                    'User does not have an email'
                );
            }
            $this->sendVerifyEmailToEmail($user, $user->email, false);
        } else {
            $user->pending_email = $email;
            $user->save();
            $this->sendVerifyEmailToEmail($user, $email, true);
        }
    }
    public function sendVerifyEmailToEmail(User $user, string $email, bool $isUpdate = false): void
    {
        $link = URL::temporarySignedRoute(
            'verify.email',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($email),  // important: hash the actual target email
                'update' => $isUpdate ? 1 : 0,
            ]
        );

        try {
            Mail::to($email)->send(new VerifyEmail($link, $user->name));
        } catch (Exception $e) {
            Log::channel('error')->error('Email Error: ' . $e->getMessage());
            throw new AuthenticationException(
                trans('auth.something_went_wrong'),
                'Email verification request failed. look for Email Error in error logs for more details'
            );
        }
    }

    public function verifyEmail(Request $request): View
    {
        $id = $request->route('id');
        $hash = $request->route('hash');
        $isUpdate = $request->query('update') == 1;

        $user = User::find($id);

        if (! $user) {
            return view('pages.email-verification-failure');
        }

        $targetEmail = $isUpdate ? $user->pending_email : $user->email;

        if (! hash_equals((string) $hash, sha1($targetEmail))) {
            return view('pages.email-verification-failure');
        }

        if ($request->hasValidSignature()) {
            if ($isUpdate) {
                $user->forceFill([
                    'email' => $user->pending_email,
                    'pending_email' => null,
                    'email_verified_at' => now(),
                ])->save();
            } else {
                $user->forceFill([
                    'email_verified_at' => now(),
                ])->save();
            }

            return view('pages.email-verification-success');
        }

        return view('pages.email-verification-failure');
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

        $user = User::where('email', '=', $email)->first();
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
            Log::channel('error')->error('Email Error: ' . $e->getMessage());
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

        $user = User::where('email', '=', $email)->first();
        $PasswordResetRequest = PasswordResetRequest::where('user_id', '=', $user->id)->first();

        if (! $PasswordResetRequest) {
            throw new AuthenticationException(
                trans('auth.something_went_wrong'),
                'Password reset request not found'
            );
        }

        if ($PasswordResetRequest->attempts >= self::FORGOT_PASSWORD_ATTEMPTS) {
            PasswordResetRequest::where('user_id', '=', $user->id)->delete();
            throw new AuthenticationException(
                trans('auth.something_went_wrong'),
                'Password reset request has been deleted because of too many attempts'
            );
        }

        $passwordReset = PasswordResetRequest::where('user_id', '=', $user->id)->where('code', '=', $code)->first();
        if (! $passwordReset) {
            PasswordResetRequest::where('user_id', '=', $user->id)->increment('attempts');
            throw new AuthenticationException(
                trans('auth.something_went_wrong'),
                'Password reset code is invalid'
            );
        }

        if (Carbon::parse($passwordReset->created_at)->diffInDays(Carbon::now()) >= self::FORGOT_PASSWORD_DAYS) {
            throw new AuthenticationException(
                trans('auth.something_went_wrong'),
                'Password reset code expired'
            );
        }
        do {
            $token = Str::random(32);
        } while (PasswordResetRequest::where('token', '=', $token)->exists());

        PasswordResetRequest::where('user_id', '=', $user->id)
            ->update(
                ['token' => $token]
            );

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

        $passwordReset = PasswordResetRequest::where('token', '=', $token)->first();

        if (! $passwordReset) {
            throw new AuthenticationException(
                trans('auth.something_went_wrong'),
                'Token is invalid'
            );
        }

        if (Carbon::parse($passwordReset->created_at)->diffInDays(Carbon::now()) >= self::FORGOT_PASSWORD_DAYS) {
            throw new AuthenticationException(
                trans('auth.something_went_wrong'),
                'Reset password code has expired'
            );
        }

        $user = User::find($passwordReset->user_id);

        if (Hash::check($password, $user->password)) {
            throw new AuthenticationException(
                trans('auth.something_went_wrong'),
                'New password must be different from old password'
            );
        }

        $user->update(['password' => Hash::make($password)]);
        if ($logoutFromAll) {
            $this->logoutFromAll($user->id);
            PasswordResetRequest::where('user_id', '=', $user->id)->where('token', '=', $token)->delete();
        }
        PasswordResetRequest::where('user_id', '=', $user->id)->where('token', '=', $token)->delete();

        return new EmptySuccessfulResponseResource;
    }

    private function logoutFromAll(int $userId): void
    {
        $user = User::find($userId);
        if ($user) {
            $user->tokens()->delete();
        }
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
        if (preg_match('/^(\d)\1{' . ($length - 1) . '}$/', $number)) {
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
        if ($user->isBlocked()) {
            throw new AuthenticationException(
                trans('auth.user_is_blocked'),
                'User is blocked'
            );
        }
    }
}
