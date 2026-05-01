This file is a merged representation of the entire codebase, combined into a single document by Repomix.

# File Summary

## Purpose
This file contains a packed representation of the entire repository's contents.
It is designed to be easily consumable by AI systems for analysis, code review,
or other automated processes.

## File Format
The content is organized as follows:
1. This summary section
2. Repository information
3. Directory structure
4. Repository files (if enabled)
5. Multiple file entries, each consisting of:
  a. A header with the file path (## File: path/to/file)
  b. The full contents of the file in a code block

## Usage Guidelines
- This file should be treated as read-only. Any changes should be made to the
  original repository files, not this packed version.
- When processing this file, use the file path to distinguish
  between different files in the repository.
- Be aware that this file may contain sensitive information. Handle it with
  the same level of security as you would the original repository.

## Notes
- Some files may have been excluded based on .gitignore rules and Repomix's configuration
- Binary files are not included in this packed representation. Please refer to the Repository Structure section for a complete list of file paths, including binary files
- Files matching patterns in .gitignore are excluded
- Files matching default ignore patterns are excluded
- Files are sorted by Git change count (files with more changes are at the bottom)

# Directory Structure
```
app/
  Console/
    Commands/
      AutoCheckoutExpiredCheckins.php
      DatabaseBackup.php
      ImportMembers.php
  Http/
    Controllers/
      Auth/
        AuthenticatedSessionController.php
        ConfirmablePasswordController.php
        EmailVerificationNotificationController.php
        EmailVerificationPromptController.php
        NewPasswordController.php
        PasswordController.php
        PasswordResetLinkController.php
        RegisteredUserController.php
        VerifyEmailController.php
      AdminController.php
      Controller.php
      ProfileController.php
      RegistrationController.php
      StaffController.php
    Middleware/
      IsAdmin.php
    Requests/
      Auth/
        LoginRequest.php
      ProfileUpdateRequest.php
  Models/
    Checkin.php
    Member.php
    Registration.php
    User.php
  Providers/
    AppServiceProvider.php
  View/
    Components/
      AppLayout.php
      GuestLayout.php
bootstrap/
  cache/
    .gitignore
  app.php
  providers.php
config/
  app.php
  auth.php
  cache.php
  database.php
  filesystems.php
  logging.php
  mail.php
  queue.php
  services.php
  session.php
database/
  factories/
    UserFactory.php
  migrations/
    0001_01_01_000000_create_users_table.php
    0001_01_01_000001_create_cache_table.php
    0001_01_01_000002_create_jobs_table.php
    2026_04_14_143101_create_registrations_table.php
    2026_04_14_200149_add_checked_in_at_to_registrations_table.php
    2026_04_15_194021_create_members_table.php
    2026_04_18_101811_create_checkins_table.php
    2026_04_18_104411_add_exception_fields_to_registrations_table.php
    2026_04_18_142444_add_trial_visits_to_registrations_table.php
    2026_04_18_171622_add_minor_fields_to_registrations_table.php
    2026_04_22_200654_add_is_admin_to_users_table.php
  seeders/
    DatabaseSeeder.php
  .gitignore
docker/
  nginx.conf
  supervisord.conf
public/
  images/
    logo-large.png
    logo-small.png
    logo.png
  .htaccess
  favicon.ico
  index.php
  robots.txt
resources/
  css/
    app.css
  js/
    app.js
    bootstrap.js
  views/
    admin/
      index.blade.php
    auth/
      confirm-password.blade.php
      forgot-password.blade.php
      login.blade.php
      register.blade.php
      reset-password.blade.php
      verify-email.blade.php
    components/
      application-logo.blade.php
      auth-session-status.blade.php
      danger-button.blade.php
      dropdown-link.blade.php
      dropdown.blade.php
      input-error.blade.php
      input-label.blade.php
      modal.blade.php
      nav-link.blade.php
      primary-button.blade.php
      responsive-nav-link.blade.php
      secondary-button.blade.php
      text-input.blade.php
    layouts/
      app.blade.php
      guest.blade.php
      navigation.blade.php
    profile/
      partials/
        delete-user-form.blade.php
        update-password-form.blade.php
        update-profile-information-form.blade.php
      edit.blade.php
    staff/
      index.blade.php
    dashboard.blade.php
    register.blade.php
    verify.blade.php
    welcome.blade.php
routes/
  auth.php
  console.php
  web.php
storage/
  app/
    private/
      .gitignore
    public/
      .gitignore
    .gitignore
  framework/
    cache/
      data/
        .gitignore
      .gitignore
    sessions/
      .gitignore
    testing/
      .gitignore
    views/
      .gitignore
    .gitignore
  logs/
    .gitignore
tests/
  Feature/
    Auth/
      AuthenticationTest.php
      EmailVerificationTest.php
      PasswordConfirmationTest.php
      PasswordResetTest.php
      PasswordUpdateTest.php
      RegistrationTest.php
    ExampleTest.php
    ProfileTest.php
  Unit/
    ExampleTest.php
  Pest.php
  TestCase.php
.editorconfig
.env alt
.env.example
.gitattributes
.gitignore
artisan
composer.json
docker-compose.yml
Dockerfile
Dockerfile.old
kletterdom-deployment-anleitung.md
package.json
phpstan.neon
phpunit.xml
README.md
tailwind.config.js
vite.config.js
```

# Files

## File: app/Console/Commands/DatabaseBackup.php
````php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DatabaseBackup extends Command
{
    protected $signature = 'backup:database';
    protected $description = 'MySQL Datenbank-Backup erstellen';

    public function handle(): void
    {
        $backupDir = storage_path('backups');

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $date     = now()->format('Y-m-d_H-i-s');
        $filename = "kletterdom_{$date}.sql.gz";
        $path     = "{$backupDir}/{$filename}";

        $db       = config('database.connections.mysql.database');
        $user     = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host     = config('database.connections.mysql.host');

        $command = "mysqldump -h {$host} -u {$user} -p{$password} {$db} | gzip > {$path}";
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error("Backup fehlgeschlagen!");
            \Log::error("DB Backup fehlgeschlagen", ['exit' => $exitCode]);
            return;
        }

        // Backups älter als 30 Tage löschen
        collect(glob("{$backupDir}/*.sql.gz"))
            ->filter(fn($f) => filemtime($f) < now()->subDays(30)->timestamp)
            ->each(fn($f) => unlink($f));

        $this->info("✅ Backup gespeichert: {$filename}");
        \Log::info("DB Backup erfolgreich", ['file' => $filename]);
    }
}
````

## File: app/Http/Controllers/Auth/AuthenticatedSessionController.php
````php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
````

## File: app/Http/Controllers/Auth/ConfirmablePasswordController.php
````php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password view.
     */
    public function show(): View
    {
        return view('auth.confirm-password');
    }

    /**
     * Confirm the user's password.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
````

## File: app/Http/Controllers/Auth/EmailVerificationNotificationController.php
````php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
````

## File: app/Http/Controllers/Auth/EmailVerificationPromptController.php
````php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(route('dashboard', absolute: false))
                    : view('auth.verify-email');
    }
}
````

## File: app/Http/Controllers/Auth/NewPasswordController.php
````php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
````

## File: app/Http/Controllers/Auth/PasswordController.php
````php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('status', 'password-updated');
    }
}
````

## File: app/Http/Controllers/Auth/PasswordResetLinkController.php
````php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
````

## File: app/Http/Controllers/Auth/RegisteredUserController.php
````php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
````

## File: app/Http/Controllers/Auth/VerifyEmailController.php
````php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
````

## File: app/Http/Controllers/Controller.php
````php
<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //
}
````

## File: app/Http/Controllers/ProfileController.php
````php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
````

## File: app/Http/Middleware/IsAdmin.php
````php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Prüfen, ob der Nutzer eingeloggt und ein Admin ist
        if (auth()->check() && auth()->user()->is_admin) {
            return $next($request);
        }

        // Wenn kein Admin, leite zurück zum Staff-Bereich mit einer Fehlermeldung
        return redirect()->route('staff')->with('error', 'Du hast keine Berechtigung für den Admin-Bereich.');
    }
}
````

## File: app/Http/Requests/Auth/LoginRequest.php
````php
<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
````

## File: app/Http/Requests/ProfileUpdateRequest.php
````php
<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }
}
````

## File: app/Models/Checkin.php
````php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $registration_id
 * @property \Illuminate\Support\Carbon $checked_in_at
 * @property \Illuminate\Support\Carbon|null $checked_out_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Registration $registration
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereCheckedInAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereCheckedOutAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereRegistrationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Checkin whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Checkin extends Model
{
    protected $fillable = [
        'registration_id',
        'checked_in_at',
        'checked_out_at',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
````

## File: app/Models/Member.php
````php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $member_number
 * @property string $first_name
 * @property string $last_name
 * @property string|null $email
 * @property string $membership_status
 * @property string $payment_status
 * @property \Illuminate\Support\Carbon|null $birth_date
 * @property \Illuminate\Support\Carbon|null $last_imported_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereLastImportedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereMemberNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereMembershipStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Member extends Model
{
    protected $fillable = [
        'member_number',
        'first_name',
        'last_name',
        'email',
        'membership_status',
        'payment_status',
        'birth_date',
        'last_imported_at',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'last_imported_at' => 'datetime',
        ];
    }
}
````

## File: app/Models/Registration.php
````php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property \Illuminate\Support\Carbon|null $birth_date
 * @property string|null $email
 * @property string $member_type
 * @property string|null $member_number
 * @property bool $waiver_accepted
 * @property string $waiver_version
 * @property string $payment_status
 * @property string $access_status
 * @property string|null $access_reason
 * @property string|null $manual_exception_reason
 * @property \Illuminate\Support\Carbon|null $manual_exception_until
 * @property \Illuminate\Support\Carbon|null $checked_in_at
 * @property string $qr_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $trial_visits_count
 * @property bool $needs_supervision
 * @property bool $needs_parent_consent
 * @property bool $parent_consent_received
 * @property \Illuminate\Support\Carbon|null $parent_consent_received_at
 * @property bool $supervision_confirmed
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Checkin> $checkins
 * @property-read int|null $checkins_count
 * @property-read \App\Models\Checkin|null $currentCheckin
 * @property-read string $full_name
 * @property-read bool $is_checked_in
 * @property-read string $status_color
 * @property-read string $status_label
 * @property-read \App\Models\Member|null $member
 * @method static Builder<static>|Registration newModelQuery()
 * @method static Builder<static>|Registration newQuery()
 * @method static Builder<static>|Registration query()
 * @method static Builder<static>|Registration whereAccessReason($value)
 * @method static Builder<static>|Registration whereAccessStatus($value)
 * @method static Builder<static>|Registration whereBirthDate($value)
 * @method static Builder<static>|Registration whereCheckedInAt($value)
 * @method static Builder<static>|Registration whereCreatedAt($value)
 * @method static Builder<static>|Registration whereEmail($value)
 * @method static Builder<static>|Registration whereFirstName($value)
 * @method static Builder<static>|Registration whereId($value)
 * @method static Builder<static>|Registration whereLastName($value)
 * @method static Builder<static>|Registration whereManualExceptionReason($value)
 * @method static Builder<static>|Registration whereManualExceptionUntil($value)
 * @method static Builder<static>|Registration whereMemberNumber($value)
 * @method static Builder<static>|Registration whereMemberType($value)
 * @method static Builder<static>|Registration whereNeedsParentConsent($value)
 * @method static Builder<static>|Registration whereNeedsSupervision($value)
 * @method static Builder<static>|Registration whereParentConsentReceived($value)
 * @method static Builder<static>|Registration whereParentConsentReceivedAt($value)
 * @method static Builder<static>|Registration wherePaymentStatus($value)
 * @method static Builder<static>|Registration whereQrToken($value)
 * @method static Builder<static>|Registration whereSupervisionConfirmed($value)
 * @method static Builder<static>|Registration whereTrialVisitsCount($value)
 * @method static Builder<static>|Registration whereUpdatedAt($value)
 * @method static Builder<static>|Registration whereWaiverAccepted($value)
 * @method static Builder<static>|Registration whereWaiverVersion($value)
 * @mixin \Eloquent
 */
class Registration extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'birth_date',
        'email',
        'member_type',
        'member_number',
        'waiver_accepted',
        'waiver_version',
        'payment_status',
        'access_status',
        'qr_token',
        'checked_in_at',
        'manual_exception_reason',
        'manual_exception_until',
        'access_reason',
        'trial_visits_count',
        'needs_supervision',
        'needs_parent_consent',
        'parent_consent_received',
        'parent_consent_received_at',
        'supervision_confirmed',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'waiver_accepted' => 'boolean',
        'checked_in_at' => 'datetime',
        'manual_exception_until' => 'datetime',
        'needs_supervision' => 'boolean',
        'needs_parent_consent' => 'boolean',
        'parent_consent_received' => 'boolean',
        'parent_consent_received_at' => 'datetime',
        'supervision_confirmed' => 'boolean',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_number', 'member_number');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getStatusColorAttribute(): string
    {
        return $this->access_status;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->access_status) {
            'green' => 'Mitglied aktiv',
            'blue' => 'Schnuppergast ok',
            'orange' => 'Warnung',
            default => 'Kein Zutritt',
        };
    }

    public function getIsCheckedInAttribute(): bool
    {
        return $this->currentCheckin !== null; // Greift auf die HasOne Relation zu
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(Checkin::class);
    }

    /**
     * Holt den aktuell offenen Check-in als echte HasOne Beziehung.
     * Dadurch können wir es im Controller mit ->with('currentCheckin') laden!
     */
    public function currentCheckin(): HasOne
    {
        return $this->hasOne(Checkin::class)->ofMany(
            ['checked_in_at' => 'max'],
            function (Builder $query) {
                $query->whereNull('checked_out_at');
            }
        );
    }
}
````

## File: app/Models/User.php
````php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $is_admin
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
````

## File: app/Providers/AppServiceProvider.php
````php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
````

## File: app/View/Components/AppLayout.php
````php
<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
````

## File: app/View/Components/GuestLayout.php
````php
<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
````

## File: bootstrap/cache/.gitignore
````
*
!.gitignore
````

## File: bootstrap/app.php
````php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
    $middleware->trustProxies(at: '*');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => \App\Http\Middleware\IsAdmin::class,
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
````

## File: bootstrap/providers.php
````php
<?php

use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
];
````

## File: config/app.php
````php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'Europe/Vienna'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
````

## File: config/auth.php
````php
<?php

use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. You may change these values
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | which utilizes session storage plus the Eloquent user provider.
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | If you have multiple user tables or models you may configure multiple
    | providers to represent the model / table. These providers may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', User::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | These configuration options specify the behavior of Laravel's password
    | reset functionality, including the table utilized for token storage
    | and the user provider that is invoked to actually retrieve users.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the number of seconds before a password confirmation
    | window expires and users are asked to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
````

## File: config/cache.php
````php
<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache store that will be used by the
    | framework. This connection is utilized if another isn't explicitly
    | specified when running a cache operation inside the application.
    |
    */

    'default' => env('CACHE_STORE', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    | Supported drivers: "array", "database", "file", "memcached",
    |                    "redis", "dynamodb", "octane",
    |                    "failover", "null"
    |
    */

    'stores' => [

        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_CACHE_CONNECTION'),
            'table' => env('DB_CACHE_TABLE', 'cache'),
            'lock_connection' => env('DB_CACHE_LOCK_CONNECTION'),
            'lock_table' => env('DB_CACHE_LOCK_TABLE'),
        ],

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
            'lock_path' => storage_path('framework/cache/data'),
        ],

        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT => 2000,
            ],
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
        ],

        'dynamodb' => [
            'driver' => 'dynamodb',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'table' => env('DYNAMODB_CACHE_TABLE', 'cache'),
            'endpoint' => env('DYNAMODB_ENDPOINT'),
        ],

        'octane' => [
            'driver' => 'octane',
        ],

        'failover' => [
            'driver' => 'failover',
            'stores' => [
                'database',
                'array',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | When utilizing the APC, database, memcached, Redis, and DynamoDB cache
    | stores, there might be other applications using the same cache. For
    | that reason, you may prefix every cache key to avoid collisions.
    |
    */

    'prefix' => env('CACHE_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-cache-'),

    /*
    |--------------------------------------------------------------------------
    | Serializable Classes
    |--------------------------------------------------------------------------
    |
    | This value determines the classes that can be unserialized from cache
    | storage. By default, no PHP classes will be unserialized from your
    | cache to prevent gadget chain attacks if your APP_KEY is leaked.
    |
    */

    'serializable_classes' => false,

];
````

## File: config/database.php
````php
<?php

use Illuminate\Support\Str;
use Pdo\Mysql;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    'default' => env('DB_CONNECTION', 'sqlite'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
            'transaction_mode' => 'DEFERRED',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                (PHP_VERSION_ID >= 80500 ? Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA) => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mariadb' => [
            'driver' => 'mariadb',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                (PHP_VERSION_ID >= 80500 ? Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA) => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => env('DB_SSLMODE', 'prefer'),
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-database-'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

    ],

];
````

## File: config/filesystems.php
````php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
````

## File: config/logging.php
````php
<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that is utilized to write
    | messages to your logs. The value provided here should match one of
    | the channels present in the list of "channels" configured below.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Laravel
    | utilizes the Monolog PHP logging library, which includes a variety
    | of powerful log handlers and formatters that you're free to use.
    |
    | Available drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog", "custom", "stack"
    |
    */

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            'channels' => explode(',', (string) env('LOG_STACK', 'single')),
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => env('LOG_SLACK_USERNAME', env('APP_NAME', 'Laravel')),
            'emoji' => env('LOG_SLACK_EMOJI', ':boom:'),
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://'.env('PAPERTRAIL_URL').':'.env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'handler_with' => [
                'stream' => 'php://stderr',
            ],
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

    ],

];
````

## File: config/mail.php
````php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers that can be used
    | when delivering an email. You may specify which one you're using for
    | your mailers below. You may also add additional mailers if needed.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin"
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
            'retry_after' => 60,
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
            'retry_after' => 60,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Laravel')),
    ],

];
````

## File: config/queue.php
````php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | Laravel's queue supports a variety of backends via a single, unified
    | API, giving you convenient access to each backend using identical
    | syntax for each. The default queue connection is defined below.
    |
    */

    'default' => env('QUEUE_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection options for every queue backend
    | used by your application. An example configuration is provided for
    | each backend supported by Laravel. You're also free to add more.
    |
    | Drivers: "sync", "database", "beanstalkd", "sqs", "redis",
    |          "deferred", "background", "failover", "null"
    |
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_QUEUE_CONNECTION'),
            'table' => env('DB_QUEUE_TABLE', 'jobs'),
            'queue' => env('DB_QUEUE', 'default'),
            'retry_after' => (int) env('DB_QUEUE_RETRY_AFTER', 90),
            'after_commit' => false,
        ],

        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => env('BEANSTALKD_QUEUE_HOST', 'localhost'),
            'queue' => env('BEANSTALKD_QUEUE', 'default'),
            'retry_after' => (int) env('BEANSTALKD_QUEUE_RETRY_AFTER', 90),
            'block_for' => 0,
            'after_commit' => false,
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue' => env('SQS_QUEUE', 'default'),
            'suffix' => env('SQS_SUFFIX'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 90),
            'block_for' => null,
            'after_commit' => false,
        ],

        'deferred' => [
            'driver' => 'deferred',
        ],

        'background' => [
            'driver' => 'background',
        ],

        'failover' => [
            'driver' => 'failover',
            'connections' => [
                'database',
                'deferred',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Job Batching
    |--------------------------------------------------------------------------
    |
    | The following options configure the database and table that store job
    | batching information. These options can be updated to any database
    | connection and table which has been defined by your application.
    |
    */

    'batching' => [
        'database' => env('DB_CONNECTION', 'sqlite'),
        'table' => 'job_batches',
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control how and where failed jobs are stored. Laravel ships with
    | support for storing failed jobs in a simple file or in a database.
    |
    | Supported drivers: "database-uuids", "dynamodb", "file", "null"
    |
    */

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'sqlite'),
        'table' => 'failed_jobs',
    ],

];
````

## File: config/services.php
````php
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
````

## File: config/session.php
````php
<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Session Driver
    |--------------------------------------------------------------------------
    |
    | This option determines the default session driver that is utilized for
    | incoming requests. Laravel supports a variety of storage options to
    | persist session data. Database storage is a great default choice.
    |
    | Supported: "file", "cookie", "database", "memcached",
    |            "redis", "dynamodb", "array"
    |
    */

    'driver' => env('SESSION_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Session Lifetime
    |--------------------------------------------------------------------------
    |
    | Here you may specify the number of minutes that you wish the session
    | to be allowed to remain idle before it expires. If you want them
    | to expire immediately when the browser is closed then you may
    | indicate that via the expire_on_close configuration option.
    |
    */

    'lifetime' => (int) env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    /*
    |--------------------------------------------------------------------------
    | Session Encryption
    |--------------------------------------------------------------------------
    |
    | This option allows you to easily specify that all of your session data
    | should be encrypted before it's stored. All encryption is performed
    | automatically by Laravel and you may use the session like normal.
    |
    */

    'encrypt' => env('SESSION_ENCRYPT', false),

    /*
    |--------------------------------------------------------------------------
    | Session File Location
    |--------------------------------------------------------------------------
    |
    | When utilizing the "file" session driver, the session files are placed
    | on disk. The default storage location is defined here; however, you
    | are free to provide another location where they should be stored.
    |
    */

    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Session Database Connection
    |--------------------------------------------------------------------------
    |
    | When using the "database" or "redis" session drivers, you may specify a
    | connection that should be used to manage these sessions. This should
    | correspond to a connection in your database configuration options.
    |
    */

    'connection' => env('SESSION_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Session Database Table
    |--------------------------------------------------------------------------
    |
    | When using the "database" session driver, you may specify the table to
    | be used to store sessions. Of course, a sensible default is defined
    | for you; however, you're welcome to change this to another table.
    |
    */

    'table' => env('SESSION_TABLE', 'sessions'),

    /*
    |--------------------------------------------------------------------------
    | Session Cache Store
    |--------------------------------------------------------------------------
    |
    | When using one of the framework's cache driven session backends, you may
    | define the cache store which should be used to store the session data
    | between requests. This must match one of your defined cache stores.
    |
    | Affects: "dynamodb", "memcached", "redis"
    |
    */

    'store' => env('SESSION_STORE'),

    /*
    |--------------------------------------------------------------------------
    | Session Sweeping Lottery
    |--------------------------------------------------------------------------
    |
    | Some session drivers must manually sweep their storage location to get
    | rid of old sessions from storage. Here are the chances that it will
    | happen on a given request. By default, the odds are 2 out of 100.
    |
    */

    'lottery' => [2, 100],

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Name
    |--------------------------------------------------------------------------
    |
    | Here you may change the name of the session cookie that is created by
    | the framework. Typically, you should not need to change this value
    | since doing so does not grant a meaningful security improvement.
    |
    */

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug((string) env('APP_NAME', 'laravel')).'-session'
    ),

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Path
    |--------------------------------------------------------------------------
    |
    | The session cookie path determines the path for which the cookie will
    | be regarded as available. Typically, this will be the root path of
    | your application, but you're free to change this when necessary.
    |
    */

    'path' => env('SESSION_PATH', '/'),

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Domain
    |--------------------------------------------------------------------------
    |
    | This value determines the domain and subdomains the session cookie is
    | available to. By default, the cookie will be available to the root
    | domain without subdomains. Typically, this shouldn't be changed.
    |
    */

    'domain' => env('SESSION_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | HTTPS Only Cookies
    |--------------------------------------------------------------------------
    |
    | By setting this option to true, session cookies will only be sent back
    | to the server if the browser has a HTTPS connection. This will keep
    | the cookie from being sent to you when it can't be done securely.
    |
    */

    'secure' => env('SESSION_SECURE_COOKIE'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Access Only
    |--------------------------------------------------------------------------
    |
    | Setting this value to true will prevent JavaScript from accessing the
    | value of the cookie and the cookie will only be accessible through
    | the HTTP protocol. It's unlikely you should disable this option.
    |
    */

    'http_only' => env('SESSION_HTTP_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Same-Site Cookies
    |--------------------------------------------------------------------------
    |
    | This option determines how your cookies behave when cross-site requests
    | take place, and can be used to mitigate CSRF attacks. By default, we
    | will set this value to "lax" to permit secure cross-site requests.
    |
    | See: https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie#samesitesamesite-value
    |
    | Supported: "lax", "strict", "none", null
    |
    */

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    /*
    |--------------------------------------------------------------------------
    | Partitioned Cookies
    |--------------------------------------------------------------------------
    |
    | Setting this value to true will tie the cookie to the top-level site for
    | a cross-site context. Partitioned cookies are accepted by the browser
    | when flagged "secure" and the Same-Site attribute is set to "none".
    |
    */

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

    /*
    |--------------------------------------------------------------------------
    | Session Serialization
    |--------------------------------------------------------------------------
    |
    | This value controls the serialization strategy for session data, which
    | is JSON by default. Setting this to "php" allows the storage of PHP
    | objects in the session but can make an application vulnerable to
    | "gadget chain" serialization attacks if the APP_KEY is leaked.
    |
    | Supported: "json", "php"
    |
    */

    'serialization' => 'json',

];
````

## File: database/factories/UserFactory.php
````php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
````

## File: database/migrations/0001_01_01_000000_create_users_table.php
````php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
````

## File: database/migrations/0001_01_01_000001_create_cache_table.php
````php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->bigInteger('expiration')->index();
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->bigInteger('expiration')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
````

## File: database/migrations/0001_01_01_000002_create_jobs_table.php
````php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
````

## File: database/migrations/2026_04_14_143101_create_registrations_table.php
````php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date')->nullable();
            $table->string('email')->nullable();

            $table->enum('member_type', ['member', 'guest']);
            $table->string('member_number')->nullable();

            $table->boolean('waiver_accepted')->default(false);
            $table->string('waiver_version')->default('v1');

            $table->enum('payment_status', ['paid', 'overdue'])->default('paid');
            $table->enum('access_status', ['green', 'blue', 'orange', 'red'])->default('red');

            $table->string('qr_token')->unique();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
````

## File: database/migrations/2026_04_14_200149_add_checked_in_at_to_registrations_table.php
````php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->timestamp('checked_in_at')->nullable()->after('access_status');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn('checked_in_at');
        });
    }
};
````

## File: database/migrations/2026_04_15_194021_create_members_table.php
````php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();

            $table->string('member_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();

            $table->string('membership_status')->default('active');
            $table->string('payment_status')->default('paid');

            $table->date('birth_date')->nullable();
            $table->timestamp('last_imported_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
````

## File: database/migrations/2026_04_18_101811_create_checkins_table.php
````php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->timestamp('checked_in_at');
            $table->timestamp('checked_out_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkins');
    }
};
````

## File: database/migrations/2026_04_18_104411_add_exception_fields_to_registrations_table.php
````php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->string('manual_exception_reason')->nullable()->after('access_status');
            $table->dateTime('manual_exception_until')->nullable()->after('manual_exception_reason');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['manual_exception_reason', 'manual_exception_until']);
        });
    }
};
````

## File: database/migrations/2026_04_18_142444_add_trial_visits_to_registrations_table.php
````php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            // Wir fügen access_reason hinzu, falls es fehlt
            if (!Schema::hasColumn('registrations', 'access_reason')) {
                $table->string('access_reason')->nullable()->after('access_status');
            }
            
            // Wir fügen trial_visits_count hinzu (jetzt ohne strictes 'after', 
            // damit es auf jeden Fall klappt)
            if (!Schema::hasColumn('registrations', 'trial_visits_count')) {
                $table->integer('trial_visits_count')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            if (Schema::hasColumn('registrations', 'access_reason')) {
                $table->dropColumn('access_reason');
            }
            
            if (Schema::hasColumn('registrations', 'trial_visits_count')) {
                $table->dropColumn('trial_visits_count');
            }
        });
    }
};
````

## File: database/migrations/2026_04_18_171622_add_minor_fields_to_registrations_table.php
````php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('registrations', 'needs_supervision')) {
                $table->boolean('needs_supervision')->default(false);
            }

            if (!Schema::hasColumn('registrations', 'needs_parent_consent')) {
                $table->boolean('needs_parent_consent')->default(false);
            }

            if (!Schema::hasColumn('registrations', 'parent_consent_received')) {
                $table->boolean('parent_consent_received')->default(false);
            }

            if (!Schema::hasColumn('registrations', 'parent_consent_received_at')) {
                $table->timestamp('parent_consent_received_at')->nullable();
            }

            if (!Schema::hasColumn('registrations', 'supervision_confirmed')) {
                $table->boolean('supervision_confirmed')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $drop = [];

            foreach ([
                'needs_supervision',
                'needs_parent_consent',
                'parent_consent_received',
                'parent_consent_received_at',
                'supervision_confirmed',
            ] as $column) {
                if (Schema::hasColumn('registrations', $column)) {
                    $drop[] = $column;
                }
            }

            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};
````

## File: database/migrations/2026_04_22_200654_add_is_admin_to_users_table.php
````php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false); // Standardmäßig false = Hallendienst
        });
    }

};
````

## File: database/seeders/DatabaseSeeder.php
````php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
````

## File: database/.gitignore
````
*.sqlite*
````

## File: public/.htaccess
````
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Handle X-XSRF-Token Header
    RewriteCond %{HTTP:x-xsrf-token} .
    RewriteRule .* - [E=HTTP_X_XSRF_TOKEN:%{HTTP:X-XSRF-Token}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
````

## File: public/index.php
````php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
````

## File: public/robots.txt
````
User-agent: *
Disallow:
````

## File: resources/css/app.css
````css
@import "tailwindcss";
@config "../../tailwind.config.js"; 

@source "../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php";
@source "../../storage/framework/views/*.php";
@source "../views/**/*.blade.php";
@source "../js/**/*.js";
````

## File: resources/js/app.js
````javascript
import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
````

## File: resources/js/bootstrap.js
````javascript
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
````

## File: resources/views/auth/confirm-password.blade.php
````php
<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>
                {{ __('Confirm') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
````

## File: resources/views/auth/forgot-password.blade.php
````php
<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Email Password Reset Link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
````

## File: resources/views/auth/register.blade.php
````php
<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
````

## File: resources/views/auth/reset-password.blade.php
````php
<x-guest-layout>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Reset Password') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
````

## File: resources/views/auth/verify-email.blade.php
````php
<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Resend Verification Email') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
````

## File: resources/views/components/application-logo.blade.php
````php
<img src="{{ asset('images/logo-small.png') }}" {{ $attributes->merge(['class' => 'object-contain']) }} alt="ÖTK Logo" />
````

## File: resources/views/components/auth-session-status.blade.php
````php
@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-green-600 dark:text-green-400']) }}>
        {{ $status }}
    </div>
@endif
````

## File: resources/views/components/danger-button.blade.php
````php
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
````

## File: resources/views/components/dropdown-link.blade.php
````php
<a {{ $attributes->merge(['class' => 'block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out']) }}>{{ $slot }}</a>
````

## File: resources/views/components/dropdown.blade.php
````php
@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white dark:bg-gray-700'])

@php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

$width = match ($width) {
    '48' => 'w-48',
    default => $width,
};
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute z-50 mt-2 {{ $width }} rounded-md shadow-lg {{ $alignmentClasses }}"
            style="display: none;"
            @click="open = false">
        <div class="rounded-md ring-1 ring-black ring-opacity-5 {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
````

## File: resources/views/components/input-error.blade.php
````php
@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-sm text-red-600 dark:text-red-400 space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
````

## File: resources/views/components/input-label.blade.php
````php
@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-700 dark:text-gray-300']) }}>
    {{ $value ?? $slot }}
</label>
````

## File: resources/views/components/modal.blade.php
````php
@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

<div
    x-data="{
        show: @js($show),
        focusables() {
            // All focusable element types...
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)]
                // All non-disabled elements...
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) -1 },
    }"
    x-init="$watch('show', value => {
        if (value) {
            document.body.classList.add('overflow-y-hidden');
            {{ $attributes->has('focusable') ? 'setTimeout(() => firstFocusable().focus(), 100)' : '' }}
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    })"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-show="show"
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
    style="display: {{ $show ? 'block' : 'none' }};"
>
    <div
        x-show="show"
        class="fixed inset-0 transform transition-all"
        x-on:click="show = false"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-gray-500 dark:bg-gray-900 opacity-75"></div>
    </div>

    <div
        x-show="show"
        class="mb-6 bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full {{ $maxWidth }} sm:mx-auto"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        {{ $slot }}
    </div>
</div>
````

## File: resources/views/components/nav-link.blade.php
````php
@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 dark:border-indigo-600 text-sm font-medium leading-5 text-gray-900 dark:text-gray-100 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
````

## File: resources/views/components/primary-button.blade.php
````php
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
````

## File: resources/views/components/responsive-nav-link.blade.php
````php
@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-indigo-400 dark:border-indigo-600 text-start text-base font-medium text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/50 focus:outline-none focus:text-indigo-800 dark:focus:text-indigo-200 focus:bg-indigo-100 dark:focus:bg-indigo-900 focus:border-indigo-700 dark:focus:border-indigo-300 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 focus:outline-none focus:text-gray-800 dark:focus:text-gray-200 focus:bg-gray-50 dark:focus:bg-gray-700 focus:border-gray-300 dark:focus:border-gray-600 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
````

## File: resources/views/components/secondary-button.blade.php
````php
<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
````

## File: resources/views/components/text-input.blade.php
````php
@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm']) }}>
````

## File: resources/views/layouts/app.blade.php
````php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
````

## File: resources/views/layouts/guest.blade.php
````php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
````

## File: resources/views/layouts/navigation.blade.php
````php
<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

                    {{-- ✅ Admin-Link nur für Admins zeigen --}}
                    @if(Auth::user()->is_admin)
                        <x-nav-link :href="route('admin.index')" :active="request()->routeIs('admin.*')">
                            🛠️ Admin
                        </x-nav-link>
                    @endif

                    <x-nav-link :href="route('staff')" :active="request()->routeIs('staff*')">
                        🧗 Check-In
                    </x-nav-link>

                    <!-- NEUER LINK -->
                    <x-nav-link :href="url('/halle-register')" target="_blank">
                        📝 Registrierung
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('staff')" :active="request()->routeIs('staff*')">
                🧗 Check-In
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('admin.index')" :active="request()->routeIs('admin.*')">
                🛠️ Admin
            </x-responsive-nav-link>

            <!-- NEUER LINK MOBILE -->
            <x-responsive-nav-link :href="url('/halle-register')" target="_blank">
                📝 Registrierung
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
````

## File: resources/views/profile/partials/delete-user-form.blade.php
````php
<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
````

## File: resources/views/profile/partials/update-password-form.blade.php
````php
<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
````

## File: resources/views/profile/partials/update-profile-information-form.blade.php
````php
<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
````

## File: resources/views/profile/edit.blade.php
````php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
````

## File: resources/views/dashboard.blade.php
````php
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
````

## File: resources/views/welcome.blade.php
````php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Startseite Kletterdom</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900 flex flex-col min-h-screen">
    
    <main class="flex-grow flex items-center justify-center p-6">
        <!-- max-w-xl statt max-w-md für angenehme Desktop-Breite -->
        <div class="w-full max-w-xl rounded-xl border border-gray-200 bg-white p-8 text-center shadow-sm">
            
            <!-- Icon/Logo -->
            <div class="mx-auto mb-8 flex justify-center">
                <img 
                    src="{{ asset('images/logo.png') }}" 
                    alt="ÖTK Langenlois Alpinsport Logo" 
                    class="h-24 w-auto object-contain"
                />
            </div>

            <h1 class="mb-2 text-2xl font-bold text-gray-900">Willkommen!</h1>
            <p class="mb-8 text-sm leading-relaxed text-gray-600">
                Bitte registriere dich für den Zutritt zur Kletterhalle. Wenn du noch kein Vereinsmitglied bist, kannst du hier direkt dem ÖTK beitreten.
            </p>

            <div class="space-y-4">
                <a
                    href="{{ url('halle-register') }}"
                    class="flex w-full items-center justify-center rounded-lg border border-transparent bg-indigo-600 px-6 py-3.5 font-semibold text-white shadow-sm transition hover:bg-indigo-700"
                >
                    Registrierung Kletterdom
                </a>

                <a
                    href="https://beitritt.oetk.at"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-6 py-3.5 font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                >
                    ÖTK Mitglied werden
                    <svg class="ml-2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                </a>
            </div>
        </div>
    </main>

    <footer class="py-6 text-center">
        <a
            href="{{ route('login') }}"
            class="text-xs font-medium text-gray-400 transition hover:text-gray-600"
        >
            Hallendienst Login
        </a>
    </footer>

</body>
</html>
````

## File: routes/auth.php
````php
<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
//    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');

//    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
````

## File: storage/app/private/.gitignore
````
*
!.gitignore
````

## File: storage/app/public/.gitignore
````
*
!.gitignore
````

## File: storage/app/.gitignore
````
*
!private/
!public/
!.gitignore
````

## File: storage/framework/cache/data/.gitignore
````
*
!.gitignore
````

## File: storage/framework/cache/.gitignore
````
*
!data/
!.gitignore
````

## File: storage/framework/sessions/.gitignore
````
*
!.gitignore
````

## File: storage/framework/testing/.gitignore
````
*
!.gitignore
````

## File: storage/framework/views/.gitignore
````
*
!.gitignore
````

## File: storage/framework/.gitignore
````
compiled.php
config.php
down
events.scanned.php
maintenance.php
routes.php
routes.scanned.php
schedule-*
services.json
````

## File: storage/logs/.gitignore
````
*
!.gitignore
````

## File: tests/Feature/Auth/AuthenticationTest.php
````php
<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
````

## File: tests/Feature/Auth/EmailVerificationTest.php
````php
<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

test('email verification screen can be rendered', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get('/verify-email');

    $response->assertStatus(200);
});

test('email can be verified', function () {
    $user = User::factory()->unverified()->create();

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
});

test('email is not verified with invalid hash', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});
````

## File: tests/Feature/Auth/PasswordConfirmationTest.php
````php
<?php

use App\Models\User;

test('confirm password screen can be rendered', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/confirm-password');

    $response->assertStatus(200);
});

test('password can be confirmed', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/confirm-password', [
        'password' => 'password',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
});

test('password is not confirmed with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/confirm-password', [
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors();
});
````

## File: tests/Feature/Auth/PasswordResetTest.php
````php
<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertStatus(200);

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        return true;
    });
});
````

## File: tests/Feature/Auth/PasswordUpdateTest.php
````php
<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('password can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('updatePassword', 'current_password')
        ->assertRedirect('/profile');
});
````

## File: tests/Feature/Auth/RegistrationTest.php
````php
<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
````

## File: tests/Feature/ExampleTest.php
````php
<?php

it('returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
````

## File: tests/Feature/ProfileTest.php
````php
<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});
````

## File: tests/Unit/ExampleTest.php
````php
<?php

test('that true is true', function () {
    expect(true)->toBeTrue();
});
````

## File: tests/Pest.php
````php
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
````

## File: tests/TestCase.php
````php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    //
}
````

## File: .editorconfig
````
root = true

[*]
charset = utf-8
end_of_line = lf
indent_size = 4
indent_style = space
insert_final_newline = true
trim_trailing_whitespace = true

[*.md]
trim_trailing_whitespace = false

[*.{yml,yaml}]
indent_size = 2

[compose.yaml]
indent_size = 4
````

## File: .env alt
````
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:MA+co+zJXP13/Ff17ejVQVKefl+ClIFPcEBFJUeB5yE=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8080
ASSET_URL=APP_URL=http://127.0.0.1:8080

APP_TIMEZONE=Europe/Vienna
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
# APP_MAINTENANCE_STORE=database

# PHP_CLI_SERVER_WORKERS=4

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=klettercheckin
DB_USERNAME=checkinuser
DB_PASSWORD=deinPasswort
DB_ROOT_PASSWORD=BeispielPasswort123!

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync

CACHE_DRIVER=file
CACHE_STORE=file
# CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"
````

## File: .gitattributes
````
* text=auto eol=lf

*.blade.php diff=html
*.css diff=css
*.html diff=html
*.md diff=markdown
*.php diff=php

/.github export-ignore
CHANGELOG.md export-ignore
.styleci.yml export-ignore
````

## File: .gitignore
````
*.log
.DS_Store
.env
.env.backup
.env.production
.phpactor.json
.phpunit.result.cache
/.fleet
/.idea
/.nova
/.phpunit.cache
/.vscode
/.zed
/auth.json
/node_modules
/public/build
/public/hot
/public/storage
/storage/*.key
/storage/pail
/vendor
_ide_helper.php
Homestead.json
Homestead.yaml
Thumbs.db
````

## File: artisan
````
#!/usr/bin/env php
<?php

use Illuminate\Foundation\Application;
use Symfony\Component\Console\Input\ArgvInput;

define('LARAVEL_START', microtime(true));

// Register the Composer autoloader...
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the command...
/** @var Application $app */
$app = require_once __DIR__.'/bootstrap/app.php';

$status = $app->handleCommand(new ArgvInput);

exit($status);
````

## File: composer.json
````json
{
    "$schema": "https://getcomposer.org/schema.json",
    "name": "laravel/laravel",
    "type": "project",
    "description": "The skeleton application for the Laravel framework.",
    "keywords": ["laravel", "framework"],
    "license": "MIT",
    "require": {
        "php": "^8.3",
        "laravel/framework": "^13.0",
        "laravel/tinker": "^3.0",
        "simplesoftwareio/simple-qrcode": "^4.2"
    },
    "require-dev": {
        "barryvdh/laravel-ide-helper": "^3.7",
        "fakerphp/faker": "^1.23",
        "larastan/larastan": "^3.9",
        "laravel/breeze": "^2.4",
        "laravel/pail": "^1.2.5",
        "laravel/pint": "^1.27",
        "mockery/mockery": "^1.6",
        "nunomaduro/collision": "^8.6",
        "pestphp/pest": "^4.5",
        "pestphp/pest-plugin-laravel": "^4.1"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "scripts": {
        "setup": [
            "composer install",
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\"",
            "@php artisan key:generate",
            "@php artisan migrate --force",
            "npm install --ignore-scripts",
            "npm run build"
        ],
        "dev": [
            "Composer\\Config::disableProcessTimeout",
            "npx concurrently -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74\" \"php artisan serve\" \"php artisan queue:listen --tries=1 --timeout=0\" \"php artisan pail --timeout=0\" \"npm run dev\" --names=server,queue,logs,vite --kill-others"
        ],
        "test": [
            "@php artisan config:clear --ansi",
            "@php artisan test"
        ],
        "post-autoload-dump": [
            "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
            "@php artisan package:discover --ansi"
        ],
        "post-update-cmd": [
            "@php artisan vendor:publish --tag=laravel-assets --ansi --force"
        ],
        "post-root-package-install": [
            "@php -r \"file_exists('.env') || copy('.env.example', '.env');\""
        ],
        "post-create-project-cmd": [
            "@php artisan key:generate --ansi",
            "@php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\"",
            "@php artisan migrate --graceful --ansi"
        ],
        "pre-package-uninstall": [
            "Illuminate\\Foundation\\ComposerScripts::prePackageUninstall"
        ]
    },
    "extra": {
        "laravel": {
            "dont-discover": []
        }
    },
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true,
        "allow-plugins": {
            "pestphp/pest-plugin": true,
            "php-http/discovery": true
        }
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
````

## File: Dockerfile.old
````
FROM php:8.4-fpm

# System-Abhängigkeiten inkl. GD + cron
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    cron \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Cronjob für Laravel Scheduler
RUN echo '* * * * * cd /var/www/html && php artisan schedule:run >> /proc/1/fd/1 2>> /proc/1/fd/2' > /etc/cron.d/laravel-scheduler \
    && chmod 0644 /etc/cron.d/laravel-scheduler \
    && crontab /etc/cron.d/laravel-scheduler

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Nur composer-Dateien zuerst (für Layer-Caching)
COPY composer.json composer.lock ./

# Abhängigkeiten installieren
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Restliche Dateien kopieren
COPY . .

# Autoloader nach vollständigem Code neu generieren
RUN composer dump-autoload --no-dev --optimize

# Berechtigungen
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

# Startet cron und danach php-fpm im Vordergrund
CMD ["sh", "-c", "cron && php-fpm -F"]
````

## File: package.json
````json
{
    "$schema": "https://www.schemastore.org/package.json",
    "private": true,
    "type": "module",
    "scripts": {
        "build": "vite build",
        "dev": "vite"
    },
    "devDependencies": {
        "@tailwindcss/forms": "^0.5.2",
        "@tailwindcss/vite": "^4.2.2",
        "alpinejs": "^3.4.2",
        "autoprefixer": "^10.4.2",
        "axios": ">=1.11.0 <=1.14.0",
        "concurrently": "^9.0.1",
        "laravel-vite-plugin": "^3.0.0",
        "postcss": "^8.4.31",
        "tailwindcss": "^4.2.2",
        "vite": "^8.0.0"
    }
}
````

## File: phpstan.neon
````
includes:
    - vendor/larastan/larastan/extension.neon

parameters:
    paths:
        - app/
        - routes/
        - database/
    level: 5 # Starte mit 5, erhöhe später bis auf 9 für strengere Prüfungen
````

## File: phpunit.xml
````xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
>
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>app</directory>
        </include>
    </source>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="APP_MAINTENANCE_DRIVER" value="file"/>
        <env name="BCRYPT_ROUNDS" value="4"/>
        <env name="BROADCAST_CONNECTION" value="null"/>
        <env name="CACHE_STORE" value="array"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="DB_URL" value=""/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="PULSE_ENABLED" value="false"/>
        <env name="TELESCOPE_ENABLED" value="false"/>
        <env name="NIGHTWATCH_ENABLED" value="false"/>
    </php>
</phpunit>
````

## File: README.md
````markdown
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
````

## File: tailwind.config.js
````javascript
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
````

## File: vite.config.js
````javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    // kein "base" eintrag hier
});
````

## File: app/Console/Commands/ImportMembers.php
````php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportMembers extends Command
{
    protected $signature = 'members:import {csvfile}';
    protected $description = 'Importiert Mitglieder aus CSV (Semikolon-getrennt, österreichisches Format)';

    public function handle()
    {
        $csvPath = $this->argument('csvfile');

        if (!file_exists($csvPath)) {
            $this->error('CSV-Datei nicht gefunden: ' . $csvPath);
            return self::FAILURE;
        }

        $handle = fopen($csvPath, 'r');

        if (!$handle) {
            $this->error('CSV-Datei konnte nicht geöffnet werden.');
            return self::FAILURE;
        }

        // BOM entfernen
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Semikolon als Trennzeichen
        $header = fgetcsv($handle, 0, ';');

        if (!$header) {
            $this->error('CSV-Datei ist leer.');
            fclose($handle);
            return self::FAILURE;
        }

        // BOM aus erstem Header-Wert entfernen
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $header = array_map('trim', $header);

        // Pflicht-Spalten prüfen
        $required = ['Mitgliedsnummer', 'Vorname', 'Nachname', 'Email', 'Status', 'Betrag offen', 'Geburtsdatum'];
        $missing = array_diff($required, $header);

        if (!empty($missing)) {
            $this->error('Fehlende Spalten: ' . implode(', ', $missing));
            $this->line('Gefundene Spalten: ' . implode(', ', $header));
            fclose($handle);
            return self::FAILURE;
        }

        // Spalten-Indizes dynamisch ermitteln
        $col = array_flip($header);

        $imported = 0;
        $skipped  = 0;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) < count($header)) {
                $skipped++;
                continue;
            }

            $memberNumber = trim($row[$col['Mitgliedsnummer']] ?? '');
            if (empty($memberNumber)) {
                $skipped++;
                continue;
            }

            // Mitgliedsstatus
            $statusRaw        = strtolower(trim($row[$col['Status']] ?? ''));
            $membershipStatus = match (true) {
                str_contains($statusRaw, 'ausgetreten') => 'inactive',
                str_contains($statusRaw, 'inaktiv')     => 'inactive',
                str_contains($statusRaw, 'gelöscht')    => 'inactive',  // ← NEU
                str_contains($statusRaw, 'geloescht')   => 'inactive',  // ← NEU (Fallback ohne Umlaut)
                default                                 => 'active',
            };

            // Beitragsstatus aus "Betrag offen"
            $betragOffen   = floatval(trim($row[$col['Betrag offen']] ?? '0'));
            $paymentStatus = $betragOffen > 0 ? 'open' : 'paid';

            // Geburtsdatum: TT.MM.JJJJ → JJJJ-MM-TT
            $birthDateRaw = trim($row[$col['Geburtsdatum']] ?? '');
            $birthDate    = $this->parseBirthDate($birthDateRaw);

            // E-Mail bereinigen
            $email = trim($row[$col['Email']] ?? '') ?: null;

            $data = [
                'member_number'     => $memberNumber,
                'first_name'        => trim($row[$col['Vorname']] ?? ''),
                'last_name'         => trim($row[$col['Nachname']] ?? ''),
                'email'             => $email,
                'membership_status' => $membershipStatus,
                'payment_status'    => $paymentStatus,
                'birth_date'        => $birthDate,
                'last_imported_at'  => now(),
                'updated_at'        => now(),
            ];

            DB::table('members')->updateOrInsert(
                ['member_number' => $data['member_number']],
                array_merge($data, ['created_at' => now()])
            );

            $imported++;
        }

        fclose($handle);

        $this->info("Importiert/Aktualisiert: {$imported}");
        $this->info("Übersprungen: {$skipped}");

        Log::info('Members import finished', [
            'imported' => $imported,
            'skipped'  => $skipped,
            'file'     => $csvPath,
        ]);

        return self::SUCCESS;
    }

    private function parseBirthDate(string $value): ?string
    {
        $value = trim($value);
        if (empty($value)) return null;

        // TT.MM.JJJJ
        if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $value, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }

        // Bereits JJJJ-MM-TT
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        return null;
    }
}
````

## File: docker/nginx.conf
````ini
server {
    listen 80;
    server_name _;
    root /var/www/html/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
````

## File: docker/supervisord.conf
````ini
[unix_http_server]
file=/var/run/supervisor.sock
chmod=0700

[supervisord]
nodaemon=true
logfile=/dev/null
logfile_maxbytes=0
pidfile=/var/run/supervisord.pid

[rpcinterface:supervisor]
supervisor.rpcinterface_factory = supervisor.rpcinterface:make_main_rpcinterface

[supervisorctl]
serverurl=unix:///var/run/supervisor.sock

[program:php-fpm]
command=php-fpm -F
autostart=true
autorestart=true
priority=10
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:scheduler]
command=sh -c "while true; do php /var/www/html/artisan schedule:run; sleep 60; done"
directory=/var/www/html
autostart=true
autorestart=true
priority=20
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
````

## File: resources/views/auth/login.blade.php
````php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} – Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 dark:bg-gray-900 flex flex-col justify-center items-center">

    <div class="w-full sm:max-w-md px-6 py-8 bg-white dark:bg-gray-800 shadow-md rounded-lg">

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Remember Me -->
            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="remember">
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                       href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-primary-button class="ms-3">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </form>

    </div>

</body>
</html>
````

## File: resources/views/verify.blade.php
````php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registrierungsbestätigung | Kletterdom</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">

    @includeWhen(Auth::check(), 'layouts.navigation')

    <main class="py-12">
        <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            <div class="flex items-center justify-center mb-8">
                <h2 class="text-xl font-bold text-gray-800 text-center">
                    Registrierungsbestätigung
                </h2>
            </div>

            @php
                $currentCheckin = $registration->currentCheckin;

                $colors = [
                    'green'  => ['bg' => 'bg-green-50',   'border' => 'border-green-500',  'text' => 'text-green-800',  'icon' => '✅', 'label' => 'Zutritt OK'],
                    'blue'   => ['bg' => 'bg-blue-50',    'border' => 'border-blue-500',   'text' => 'text-blue-800',   'icon' => '🔵', 'label' => 'Schnupperklettern'],
                    'orange' => ['bg' => 'bg-orange-50',  'border' => 'border-orange-500', 'text' => 'text-orange-800', 'icon' => '⚠️', 'label' => 'Bitte beim Hallendienst melden'],
                    'red'    => ['bg' => 'bg-red-50',     'border' => 'border-red-500',    'text' => 'text-red-800',    'icon' => '🚫', 'label' => 'Kein Zutritt'],
                ];
                $c = $colors[$registration->access_status] ?? $colors['red'];
                
                // Prüfen ob Check-in blockiert werden muss (Status Rot oder Orange ohne aktive Kulanz)
                $hasActiveKulanz = $registration->manual_exception_until && $registration->manual_exception_until->isFuture();
                $isTrialUsed = $registration->access_status === 'blue'
                   && ($registration->trial_visits_count ?? 0) >= 1
                   && !$hasActiveKulanz;

                $isBlocked = !in_array($registration->access_status, ['green', 'blue']) || $isTrialUsed;
            @endphp

            <div class="bg-white rounded-xl border-2 {{ $c['border'] }} {{ $c['bg'] }} p-6 mb-6 text-center shadow-sm">
                <div class="text-5xl mb-3">{{ $c['icon'] }}</div>
                <div class="text-2xl font-bold {{ $c['text'] }}">{{ $c['label'] }}</div>
                @if($registration->access_reason)
                    <div class="mt-2 text-sm {{ $c['text'] }} opacity-80 font-medium">
                        {{ $registration->access_reason }}
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Deine Registrierung</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <dt class="text-gray-500">Name</dt>
                        <dd class="font-bold text-gray-900">{{ $registration->first_name }} {{ $registration->last_name }}</dd>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <dt class="text-gray-500">Typ</dt>
                        <dd class="font-bold text-gray-900">
                            {{ $registration->member_type === 'member' ? 'Mitglied' : 'Schnuppergast' }}
                        </dd>
                    </div>
                    @if($registration->member_number)
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                            <dt class="text-gray-500">Mitgliedsnummer</dt>
                            <dd class="font-bold text-gray-900">{{ $registration->member_number }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between pt-1">
                        <dt class="text-gray-500">Haftungsausschluss</dt>
                        <dd class="font-bold text-green-700 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Akzeptiert
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 text-center">
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Dein persönlicher QR-Code</h3>
                <div class="flex justify-center mb-5 bg-white p-4 rounded-lg border border-gray-100 inline-block mx-auto">
                    {!! QrCode::size(200)->margin(1)->generate(url('verify/' . $registration->qr_token)) !!}
                </div>
                <p class="text-sm text-gray-600 leading-relaxed max-w-[280px] mx-auto">
                    Checke mit diesem Code beim Hallendienst im Kletterdom ein.<br>
                    <span class="text-xs text-gray-400 mt-1 block">Tipp: Speichere diese Seite als Lesezeichen oder mache einen Screenshot.</span>
                </p>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="{{ url('verify/' . $registration->qr_token) }}"
                       class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline break-all font-medium">
                        {{ url('verify/' . $registration->qr_token) }}
                    </a>
                </div>
            </div>

            @auth
                <div class="mt-10 bg-gray-900 rounded-xl shadow-lg border border-gray-800 p-6 text-center">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-5">Personal-Aktion</h3>

                    @if($currentCheckin)
                        <div class="bg-green-900/30 border border-green-800 rounded-lg p-4">
                            <div class="text-green-400 font-bold text-lg flex items-center justify-center gap-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Bereits eingecheckt
                            </div>
                            <div class="text-green-500/80 text-sm mt-1">
                                am {{ $currentCheckin->checked_in_at->format('d.m.Y \u\m H:i') }} Uhr
                            </div>
                        </div>
                    @elseif($isBlocked)
                        <div class="bg-amber-900/30 border border-amber-800 rounded-lg p-4 mb-2">
                            <div class="text-amber-400 font-bold text-lg flex items-center justify-center gap-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                Check-in nicht möglich
                            </div>
                            <div class="text-amber-500/80 text-sm mt-2">
                                @if($registration->access_status === 'red')
                                    Kein Zutritt erlaubt. Bitte beim Hallendienst melden.
                                @elseif($isTrialUsed)
                                    Erstbesuch bereits absolviert. Zweiter Besuch nur mit Kulanz durch den Hallendienst.
                                @else
                                    Zutritt erfordert manuelle Freigabe. Bitte in der Staff-Übersicht prüfen.
                                @endif
                            </div>
                        </div>
                    
                    @else
                        <form method="POST" action="{{ url('verify/' . $registration->qr_token . '/checkin') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center justify-center gap-2 bg-green-600 hover:bg-green-500 text-white px-6 py-4 rounded-lg font-bold text-lg shadow-[0_0_15px_rgba(22,163,74,0.3)] transition transform hover:-translate-y-0.5">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Check-in bestätigen
                            </button>
                        </form>
                    @endif

                    <div class="mt-6 pt-5 border-t border-gray-800">
                        <a href="{{ route('staff') }}" class="inline-flex items-center gap-2 text-gray-400 hover:text-white text-sm font-medium transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                            Zurück zur Staff-Übersicht
                        </a>
                    </div>
                </div>
            @endauth

        </div>
    </main>

</body>
</html>
````

## File: routes/console.php
````php
<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\DatabaseBackup;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('checkins:auto-checkout')
    ->name('checkins-auto-checkout')
    ->everyMinute();

Schedule::command(DatabaseBackup::class)->dailyAt('03:00');
````

## File: .env.example
````
# -----------------------------------------------
# App
# -----------------------------------------------
APP_NAME="Kletterdom Check-in"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://192.168.x.x        # lokale IP des Raspberry Pi eintragen

APP_LOCALE=de
APP_FALLBACK_LOCALE=de
APP_FAKER_LOCALE=de_AT
APP_TIMEZONE=Europe/Vienna

APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

# -----------------------------------------------
# Logging
# -----------------------------------------------
LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=warning                  # in Produktion nicht debug!

# -----------------------------------------------
# Datenbank (MySQL via Docker)
# -----------------------------------------------
DB_CONNECTION=mysql
DB_HOST=db                         # Docker-Servicename – nicht ändern!
DB_PORT=3306
DB_DATABASE=klettercheckin
DB_USERNAME=checkinuser
DB_PASSWORD=                       # sicheres Passwort eintragen – muss mit docker-compose.yml übereinstimmen!

# -----------------------------------------------
# Session / Cache / Queue
# -----------------------------------------------
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=database

# -----------------------------------------------
# Vite
# -----------------------------------------------
VITE_APP_NAME="${APP_NAME}"
````

## File: app/Console/Commands/AutoCheckoutExpiredCheckins.php
````php
<?php

namespace App\Console\Commands;

use App\Models\Checkin;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoCheckoutExpiredCheckins extends Command
{
    protected $signature = 'checkins:auto-checkout';

    protected $description = 'Schließt offene Check-ins automatisch nach 3 Stunden';

    public function handle(): int
    {
        $expiredCheckins = Checkin::whereNull('checked_out_at')
            ->where('checked_in_at', '<=', now()->subHours(3))
            ->get();

        $closedCount = 0;

        foreach ($expiredCheckins as $checkin) {
            $checkedOutAt = Carbon::parse($checkin->checked_in_at)->copy()->addHours(3);
            $checkin->update(['checked_out_at' => $checkedOutAt]);
        
            $registration = Registration::find($checkin->registration_id);
            if ($registration) {
                $hasOpenCheckin = Checkin::where('registration_id', $registration->id)
                    ->whereNull('checked_out_at')
                    ->exists();
        
                if (!$hasOpenCheckin) {
                    $registration->update(['checked_in_at' => null]);
                }
        
                // Schnuppergast: nach 3 Besuchen → red
                if ($registration->member_type === 'guest'
                    && $registration->trial_visits_count >= 3) {
                    $registration->update([
                        'access_status' => 'red',
                        'access_reason' => 'Schnupperlimit ausgeschöpft (3/3)',
                    ]);
                }
        
                // NEU: Unverified Member: nach 3 Besuchen → red
                $isUnverifiedMember = $registration->member_type === 'member'
                                      && $registration->member === null;
        
                if ($isUnverifiedMember && $registration->trial_visits_count >= 3) {
                    $registration->update([
                        'access_status' => 'red',
                        'access_reason' => 'Mitgliedsnummer nicht im System – Limit erreicht',
                    ]);
                }
            }
        
            $closedCount++;
        }

        $this->info("Auto-Checkout abgeschlossen: {$closedCount} Check-ins geschlossen.");

        return self::SUCCESS;
    }
}
````

## File: kletterdom-deployment-anleitung.md
````markdown
# Kletterdom Check-in – Deployment Anleitung
**Docker · Raspberry Pi 4 · HTTPS · Lokales Netzwerk**

---

## Voraussetzungen

### Hardware & Betriebssystem
- Raspberry Pi 4 (min. 4 GB RAM empfohlen)
- MicroSD-Karte ≥ 32 GB (Class 10 / A2)
- Netzteil 5V / 3A USB-C
- LAN-Kabel (stabiler als WLAN)
- Raspberry Pi OS Lite 64-bit **oder** Ubuntu Server 24.04 LTS (arm64)
- SSH aktiviert (via Raspberry Pi Imager → Erweiterte Optionen)

### Netzwerk
- Feste lokale IP am Router reservieren (DHCP-Reservierung per MAC-Adresse)
- MAC-Adresse abrufen: `ip link show eth0`
- Ziel-IP Beispiel: `192.168.178.54`

### Erforderliche Software am Pi
Nur **Docker** und **Git** – PHP, Node.js und MySQL laufen alle im Container.

---

## Schritt 1: SSH & Grundeinrichtung

```bash
# Per SSH verbinden
ssh pi@192.168.x.x

# Docker installieren
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER

# Neu einloggen damit Docker-Gruppe aktiv wird
exit
```

Wieder per SSH einloggen, dann:

```bash
sudo apt update && sudo apt install -y git

# Hostname setzen (für kletterdom.local im Netzwerk)
sudo hostnamectl set-hostname kletterdom
sudo apt install -y avahi-daemon
sudo systemctl enable avahi-daemon
```

---

## Schritt 2: Repository klonen

```bash
git clone <REPO-URL> kletter-checkin
cd kletter-checkin
```

---

## Schritt 3: Konfigurationsdateien

### 3.1 docker-compose.yml

```yaml
services:

  app:
    build: .
    image: kletter-checkin
    container_name: kletter-app
    restart: unless-stopped
    depends_on:
      db:
        condition: service_healthy    # wartet bis MySQL wirklich bereit ist
    networks:
      - kletternet
    environment:
      - TZ=Europe/Vienna
    # KEIN volumes-Mount auf den App-Root! vendor/ würde sonst überschrieben.
    volumes:
      - ./backups:/var/www/html/storage/backups   # nur Backup-Unterordner

  nginx:
    image: nginx:alpine
    container_name: kletter-nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx.conf:/etc/nginx/conf.d/default.conf
      - ./docker/ssl:/etc/nginx/ssl
    depends_on:
      - app
    networks:
      - kletternet

  db:
    image: mysql:8.0
    container_name: kletter-db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: klettercheckin
      MYSQL_USER: checkinuser
      MYSQL_PASSWORD: ${DB_PASSWORD}
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
    volumes:
      - dbdata:/var/lib/mysql
    networks:
      - kletternet
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-p${DB_ROOT_PASSWORD}"]
      interval: 10s
      timeout: 5s
      retries: 5

  node:
    image: node:20-alpine
    working_dir: /var/www
    volumes:
      - .:/var/www
    profiles: ["build"]

volumes:
  dbdata:

networks:
  kletternet:
    driver: bridge
```

### 3.2 Dockerfile

```dockerfile
FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    default-mysql-client \
    libpng-dev libonig-dev libxml2-dev \
    libfreetype6-dev libjpeg62-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer VOR composer install (wichtig!)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY . .
RUN composer dump-autoload --no-dev --optimize

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
```

> `default-mysql-client` stellt `mysqldump` im Container bereit – wird für automatische Backups benötigt.

### 3.3 Nginx-Konfiguration (`docker/nginx.conf`)

```nginx
server {
    listen 80;
    server_name _;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl;
    server_name _;

    ssl_certificate     /etc/nginx/ssl/nginx.crt;
    ssl_certificate_key /etc/nginx/ssl/nginx.key;
    ssl_protocols       TLSv1.2 TLSv1.3;
    ssl_ciphers         HIGH:!aNULL:!MD5;

    root /var/www/html/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### 3.4 Backup-Command (`app/Console/Commands/DatabaseBackup.php`)

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DatabaseBackup extends Command
{
    protected $signature   = 'backup:database';
    protected $description = 'MySQL Datenbank-Backup erstellen';

    public function handle(): void
    {
        $backupDir = storage_path('backups');

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $date     = now()->format('Y-m-d_H-i-s');
        $filename = "kletterdom_{$date}.sql.gz";
        $path     = "{$backupDir}/{$filename}";

        $db       = config('database.connections.mysql.database');
        $user     = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host     = config('database.connections.mysql.host');

        $command = "mysqldump -h {$host} -u {$user} -p{$password} {$db} | gzip > {$path}";
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->error('Backup fehlgeschlagen!');
            \Log::error('DB Backup fehlgeschlagen', ['exit' => $exitCode]);
            return;
        }

        // Backups älter als 30 Tage löschen
        collect(glob("{$backupDir}/*.sql.gz"))
            ->filter(fn($f) => filemtime($f) < now()->subDays(30)->timestamp)
            ->each(fn($f) => unlink($f));

        $this->info("✅ Backup gespeichert: {$filename}");
        \Log::info('DB Backup erfolgreich', ['file' => $filename]);
    }
}
```

### 3.5 Schedule registrieren (`routes/console.php`)

```php
use App\Console\Commands\DatabaseBackup;

Schedule::command(DatabaseBackup::class)->dailyAt('03:00');
```

---

## Schritt 4: SSL-Zertifikat erstellen

```bash
mkdir -p docker/ssl

openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
  -keyout docker/ssl/nginx.key \
  -out docker/ssl/nginx.crt \
  -subj "/CN=192.168.x.x" \
  -addext "subjectAltName=IP:192.168.x.x,DNS:kletterdom.local"
```

Die IP `192.168.x.x` durch die tatsächliche Pi-IP ersetzen. Das Zertifikat gilt 10 Jahre.

---

## Schritt 5: .env anlegen

```bash
cp .env.example .env
nano .env
```

```env
APP_NAME="Kletterdom Check-in"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://192.168.x.x

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=klettercheckin
DB_USERNAME=checkinuser
DB_PASSWORD=SicheresPasswort!
DB_ROOT_PASSWORD=SicheresRootPw!

APP_KEY=                            # wird in Schritt 7 generiert

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

---

## Schritt 6: Frontend-Assets bauen

```bash
docker compose --profile build run --rm node sh -c "npm install && npm run build"
```

Beim ersten Mal dauert das 3–8 Minuten auf dem Pi.

---

## Schritt 7: Container starten & Laravel einrichten

```bash
# Container starten
docker compose up -d --build

# .env in Container kopieren + Berechtigungen setzen
docker compose cp .env app:/var/www/html/.env
docker compose exec app chown www-data:www-data /var/www/html/.env

# App-Key generieren
docker compose exec app php artisan key:generate

# .env mit generiertem Key zurückholen
docker compose cp app:/var/www/html/.env .env

# Datenbank migrieren
docker compose exec app php artisan migrate --force

# Storage-Link
docker compose exec app php artisan storage:link

# Cache aktivieren
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

---

## Schritt 8: Admin-User anlegen

```bash
docker compose exec app php artisan tinker
```

```php
$user = \App\Models\User::create([
    'name'     => 'Admin',
    'email'    => 'admin@kletterdom.at',
    'password' => bcrypt('SicheresAdminPasswort!'),
]);
$user->is_admin = 1;
$user->save();
exit
```

`is_admin` muss separat gesetzt werden – es ist bewusst nicht in `$fillable` (Sicherheitsmaßnahme gegen Mass Assignment).

---

## Schritt 9: Autostart aktivieren

```bash
sudo systemctl enable docker
```

Durch `restart: unless-stopped` starten alle Container nach jedem Reboot automatisch.

---

## Schritt 10: Automatische Backups einrichten

Backups laufen über den **Laravel Scheduler** und werden täglich um 03:00 Uhr erstellt.  
Die `.sql.gz`-Dateien landen direkt im Projektordner unter `kletter-checkin/backups/` auf dem Pi.

### Cron-Job einrichten (einmalig)

```bash
crontab -e
```

Zeile einfügen:

* * * * * cd /home/pi/kletter-checkin && docker compose exec -T app php artisan schedule:run >> /var/log/kletterdom-scheduler.log 2>&1


> Dieser eine Cron-Job reicht – der Laravel Scheduler übernimmt ab dann alle zeitgesteuerten Aufgaben (Backups, Auto-Checkout etc.).

### Backup manuell testen

```bash
# Backup sofort auslösen
docker compose exec app php artisan backup:database

# Ergebnis prüfen
ls -lh backups/
```

### Backup wiederherstellen (Notfall)

```bash
gunzip < backups/kletterdom_2026-05-01_03-00-00.sql.gz | \
  docker compose exec -T db mysql -u checkinuser -pSicheresPasswort! klettercheckin
```

---

## App aufrufen

| URL | Beschreibung |
|---|---|
| `https://192.168.x.x` | Direkt per IP |
| `https://kletterdom.local` | Per Hostname (alle Geräte im Netzwerk) |

Beim ersten Aufruf zeigt der Browser eine Zertifikatswarnung (Self-Signed) – einmalig pro Gerät bestätigen.

---

## Update deployen

```bash
cd kletter-checkin

git pull
docker compose --profile build run --rm node sh -c "npm run build"
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

echo "Update fertig!"
```

---

## Häufige Befehle

| Aktion | Befehl |
|---|---|
| Status prüfen | `docker compose ps` |
| App neu starten | `docker compose restart app` |
| Alles stoppen | `docker compose down` |
| Alles starten | `docker compose up -d` |
| Laravel-Logs | `docker compose exec app tail -f storage/logs/laravel.log` |
| Alle Logs live | `docker compose logs -f` |
| In DB einloggen | `docker compose exec db mysql -u checkinuser -p klettercheckin` |
| Artisan ausführen | `docker compose exec app php artisan <befehl>` |
| Backup manuell | `docker compose exec app php artisan backup:database` |
| Backup-Dateien | `ls -lh backups/` |
| Mitglieder aus DB entfernen | `‌docker compose exec app php artisan tinker --execute="DB::table('members')->truncate(); echo 'Done';"` |

---

## Troubleshooting

| Problem | Lösung |
|---|---|
| 500 Error | `docker compose exec app tail -50 storage/logs/laravel.log` |
| `vendor/autoload.php` fehlt | `volumes: .:/var/www/html` beim app-Service entfernen, neu bauen |
| `composer: not found` im Build | `COPY --from=composer:latest` muss **vor** `RUN composer install` stehen |
| `.env` fehlt im Container | `docker compose cp .env app:/var/www/html/.env` |
| `APP_KEY` fehlt | `echo "APP_KEY=" >> .env` dann `key:generate` |
| Permission denied auf `.env` | `docker compose exec app chown www-data:www-data /var/www/html/.env` |
| DB nicht erreichbar | `docker compose ps` – ist `kletter-db` healthy? |
| Assets fehlen / CSS kaputt | Node-Build-Schritt wiederholen |
| `is_admin` wird nicht gesetzt | `$user->is_admin = 1; $user->save();` statt über `create()` |
| Pi sehr langsam beim ersten Build | Normal – Docker-Layer werden danach gecacht |
| Backup fehlgeschlagen | `storage/logs/laravel.log` prüfen; `mysqldump` vorhanden? `docker compose exec app which mysqldump` |

---

*Kletterdom Check-in System · Deployment Guide · Stand Mai 2026*
````

## File: app/Http/Controllers/AdminController.php
````php
<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Member;
use App\Models\Checkin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // ── Dashboard ──────────────────────────────────────────
    public function index()
    {
        $registrations = Registration::withCount('checkins')
            ->orderByDesc('created_at')
            ->paginate(30);

        // Hallenauslastung: Checkins pro Tag (letzte 30 Tage)
        $chartData = Checkin::select(
                DB::raw('DATE(checked_in_at) as day'),
                DB::raw('COUNT(*) as total')
            )
            ->where('checked_in_at', '>=', Carbon::now()->subDays(29)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        // Auffüllen: auch Tage ohne Check-ins erscheinen im Chart
        $labels = [];
        $values = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i)->toDateString();
            $labels[] = Carbon::parse($day)->format('d.m');
            $values[] = $chartData->get($day)->total ?? 0;
        }

        $stats = [
            'total_registrations' => Registration::count(),
            'checked_in_today'    => Checkin::whereDate('checked_in_at', today())->count(),
            'members'             => Member::count(),
            'guests_today'        => Checkin::whereDate('checked_in_at', today())
                // HIER WAR VORHER member_type, DAS HAT GEPASST
                ->whereHas('registration', fn($q) => $q->where('member_type', 'guest'))
                ->count(),
        ];

        return view('admin.index', compact('registrations', 'labels', 'values', 'stats'));
    }

    // ── Registrierung löschen ──────────────────────────────
    public function destroyRegistration(Registration $registration)
    {
        // Checkins mitlöschen
        $registration->checkins()->delete();
        $registration->delete();

        return back()->with('success', 'Registrierung wurde gelöscht.');
    }

    // ── Mitglieder CSV-Import ──────────────────────────────

    public function importMembers(Request $request)
    {
        $request->validate([
            'members_csv' => ['required', 'file', 'mimes:csv,txt'],
        ]);
    
        $storedPath = $request->file('members_csv')->store('imports');
    
        if (!$storedPath) {
            return redirect()
                ->route('admin.index')
                ->with('error', 'CSV konnte nicht sicher gespeichert werden.');
        }
    
        $fullPath = Storage::path($storedPath);
        $handle = fopen($fullPath, 'r');
    
        if (!$handle) {
            Storage::delete($storedPath);
    
            return redirect()
                ->route('admin.index')
                ->with('error', 'CSV konnte nicht geöffnet werden.');
        }
    
        try {
            $headers = fgetcsv($handle, 0, ';');
    
            if (!$headers) {
                return redirect()
                    ->route('admin.index')
                    ->with('error', 'CSV konnte nicht gelesen werden.');
            }
    
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
    
            if (!in_array('Mitgliedsnummer', $headers, true)) {
                return redirect()
                    ->route('admin.index')
                    ->with('error', 'CSV-Format ungültig: Spalte "Mitgliedsnummer" fehlt.');
            }
    
            $imported = 0;
    
            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                if (count($row) !== count($headers)) {
                    continue;
                }
    
                $data = array_combine($headers, $row);
    
                $memberNumber = trim($data['Mitgliedsnummer'] ?? '');
    
                if ($memberNumber === '') {
                    continue;
                }
    
                $betragOffen = (float) str_replace(',', '.', trim((string) ($data['Betrag offen'] ?? '0')));
                $paymentStatus = $betragOffen > 0 ? 'open' : 'paid';
                
                $birthDate = $this->parseCsvDate($data['Geburtsdatum'] ?? null);
                $exitDate = $this->parseCsvDate($data['Austrittsdatum'] ?? null);
                
                // NEU: Status-Spalte direkt auslesen
                $csvStatus = strtolower(trim((string)($data['Status'] ?? '')));
                
                $inactiveStatuses = ['gelöscht', 'ausgetreten', 'gesperrt', 'inaktiv', 'gekündigt'];
                
                $membershipStatus = 'active';
                if (in_array($csvStatus, $inactiveStatuses, true)) {
                    $membershipStatus = 'inactive';
                } elseif ($exitDate && $exitDate->isPast()) {
                    $membershipStatus = 'inactive';
                }
    
                Member::updateOrCreate(
                    ['member_number' => $memberNumber],
                    [
                        'first_name' => trim((string) ($data['Vorname'] ?? '')),
                        'last_name' => trim((string) ($data['Nachname'] ?? '')),
                        'email' => $this->nullIfEmpty($data['Email'] ?? null),
                        'birth_date' => $birthDate?->format('Y-m-d'),
                        'membership_status' => $membershipStatus,
                        'payment_status' => $paymentStatus,
                        'last_imported_at' => now(),
                    ]
                );
                
                // ↓ NEU: Registrierungen synchronisieren
                if ($membershipStatus === 'active' && $paymentStatus === 'paid') {
                Registration::where('member_number', $memberNumber)
                    ->where('access_status', 'red')
                    ->where(function ($q) {
                        $q->where('access_reason', 'like', '%inaktiv%')
                          ->orWhere('access_reason', 'like', '%Schnupperlimit%');
                    })
                    ->update([
                        'access_status' => 'green',
                        'access_reason' => 'Mitgliedschaft aktiv bezahlt',
                    ]);
                } elseif ($membershipStatus === 'active' && $paymentStatus === 'open') {
                    Registration::where('member_number', $memberNumber)
                        ->whereIn('access_status', ['red', 'green'])
                        ->where('access_reason', 'like', '%inaktiv%')
                        ->update([
                            'access_status' => 'orange',
                            'access_reason' => 'Beitrag offen',
                        ]);
                } elseif ($membershipStatus === 'inactive') {
                    Registration::where('member_number', $memberNumber)
                        ->where('access_status', '!=', 'red')
                        ->update([
                            'access_status' => 'red',
                            'access_reason' => 'Mitgliedschaft inaktiv',
                        ]);
                }
    
                $imported++;
            }
    
            if ($imported === 0) {
                return redirect()
                    ->route('admin.index')
                    ->with('error', 'CSV wurde gelesen, aber es konnten keine Datensätze importiert werden.');
            }
    
            return redirect()
                ->route('admin.index')
                ->with('success', "Mitgliederimport abgeschlossen: {$imported} Datensätze verarbeitet.");
        } finally {
            fclose($handle);
            Storage::delete($storedPath);
        }
    }

    // ── Checkins CSV-Export ────────────────────────────────
    public function exportCheckins(Request $request)
    {
        $from = $request->input('from') ? Carbon::parse($request->input('from'))->startOfDay() : Carbon::now()->subDays(30)->startOfDay();
        $to   = $request->input('to')   ? Carbon::parse($request->input('to'))->endOfDay()     : Carbon::now()->endOfDay();
    
        $checkins = Checkin::with('registration')
            ->whereBetween('checked_in_at', [$from, $to])
            ->orderBy('checked_in_at')
            ->get();
    
        $filename = 'checkins_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.csv';
    
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
    
        $callback = function () use ($checkins) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
    
            fputcsv($handle, [
                'Check-in ID',
                'Vorname',
                'Nachname',
                'Geburtsdatum',
                'Mitgliedstyp',
                'Mitgliedsnummer',
                'Check-in Zeit',
                'Ampelstatus',
                'Zugangsstatus-Grund',
                'Schnupperbesuche',
                'Aufsicht erforderlich',
            ], ';');
    
            foreach ($checkins as $c) {
                $reg = $c->registration;
    
                fputcsv($handle, [
                    $c->id,
                    $reg->first_name  ?? '',
                    $reg->last_name   ?? '',
                    $reg->birth_date  ? Carbon::parse($reg->birth_date)->format('d.m.Y') : '',
                    $reg->member_type === 'member' ? 'Mitglied' : 'Gast',
                    $reg->member_number ?? '',
                    Carbon::parse($c->checked_in_at)->format('d.m.Y H:i'),
                    $reg->access_status ?? '',
                    $reg->access_reason ?? '',
                    $reg->member_type === 'guest' ? ($reg->trial_visits_count ?? 0) : '',
                    $reg->needs_supervision ? 'Ja' : 'Nein',
                ], ';');
            }
    
            fclose($handle);
        };
    
        return response()->stream($callback, 200, $headers);
    }

    private function parseCsvDate(?string $value): ?Carbon
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('d.m.Y', $value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function nullIfEmpty(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
````

## File: resources/views/register.blade.php
````php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registrierung | Kletterdom</title>

    <!-- Direkte Vite-Einbindung für sauberes Tailwind und JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Verhindert den Standard-Pfeil im Safari/Chrome bei details/summary */
        details > summary { list-style: none; }
        details > summary::-webkit-details-marker { display: none; }
        
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">

    <div class="min-h-screen py-12 flex flex-col justify-center sm:px-6 lg:px-8">
        
        <div class="sm:mx-auto sm:w-full sm:max-w-3xl text-center mb-6">
            <h2 class="text-3xl font-extrabold text-gray-900">
                Registrierung Kletterdom
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Bitte fülle das Formular aus, um dich für den Hallenbesuch zu registrieren.
            </p>
        </div>

        <div class="sm:mx-auto sm:w-full sm:max-w-3xl">
            <div class="bg-white py-8 px-4 shadow-xl sm:rounded-xl sm:px-10 border border-gray-100">
                
                @if (session('success'))
                    <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-800">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ url('/halle-register') }}" class="space-y-6">
                    @csrf
                    
                    @php
                        $hpTimestamp = now()->timestamp;
                    @endphp
                    
                    <input type="hidden" name="hp_time" value="{{ $hpTimestamp }}">
                    
                    <div class="sr-only" aria-hidden="true">
                        <label for="website">Website</label>
                        <input
                            type="text"
                            id="website"
                            name="website"
                            value="{{ old('website') }}"
                            tabindex="-1"
                            autocomplete="off"
                        >
                    
                        <label for="fax_number">Faxnummer</label>
                        <input
                            type="text"
                            id="fax_number"
                            name="fax_number"
                            value="{{ old('fax_number') }}"
                            tabindex="-1"
                            autocomplete="off"
                        >
                    </div>

                    <!-- Persönliche Daten -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">Vorname</label>
                            <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required class="p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('first_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">Nachname</label>
                            <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required class="p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('last_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="birth_date" class="block text-sm font-medium text-gray-700">Geburtsdatum</label>
                            <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date') }}" min="1900-01-01" max="{{ date('Y-m-d') }}" required class="p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('birth_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                            <!-- Hinweis 14-17 -->
                            <div id="minor-note" class="hidden mt-3 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                                <p class="font-semibold mb-1">Hinweis für Minderjährige</p>
                                <p id="minor-note-text">Für Kinder von 14 bis 18 Jahren muss beim Hallenbesuch ohne Aufsichtsperson eine unterschriebene Einverständniserklärung der Eltern mitgebracht werden.</p>
                                <a id="minor-pdf-link" href="https://www.oetk-langenlois.at/fileadmin/Einverstaendniserklaerung-14-18.pdf" target="_blank" rel="noopener noreferrer" class="hidden mt-2 inline-flex font-medium text-amber-900 underline hover:text-amber-700">Einverständniserklärung herunterladen</a>
                            </div>

                            <!-- Checkbox nur unter 14 -->
                            <div id="supervision-wrapper" class="hidden mt-3">
                                <label class="inline-flex items-start bg-amber-50 border border-amber-200 rounded-lg p-3 cursor-pointer">
                                    <input type="checkbox" id="supervision_confirmed" name="supervision_confirmed" value="1" class="mt-0.5 rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500" {{ old('supervision_confirmed') ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-amber-900 leading-snug">Ich bestätige, dass Kinder unter 14 Jahren nur unter Aufsicht klettern dürfen und beim Besuch eine erziehungsberechtigte oder aufsichtsführende Person dabei ist.</span>
                                </label>
                                @error('supervision_confirmed') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">E-Mail</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" class="p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <fieldset>
                        <legend class="block text-sm font-medium text-gray-700 mb-2">Typ</legend>
                        <div class="flex gap-6">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="member_type" value="member" class="text-indigo-600 border-gray-300 focus:ring-indigo-500" {{ old('member_type', 'member') === 'member' ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700 text-sm">Mitglied</span>
                            </label>
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="radio" name="member_type" value="guest" class="text-indigo-600 border-gray-300 focus:ring-indigo-500" {{ old('member_type') === 'guest' ? 'checked' : '' }}>
                                <span class="ml-2 text-gray-700 text-sm">Gast / Schnuppern</span>
                            </label>
                        </div>
                        @error('member_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </fieldset>

                    <div id="member-number-wrapper">
                        <label for="member_number" class="block text-sm font-medium text-gray-700">Mitgliedsnummer</label>
                        <input type="text" id="member_number" name="member_number" value="{{ old('member_number') }}" placeholder="z. B. 23-34567" pattern="\d{2}-\d{5}" class="p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" autocomplete="off" inputmode="numeric" maxlength="8">
                        <p class="mt-1 text-xs text-gray-500">Bitte die Vereins-Mitgliedsnummer eingeben.</p>
                        @error('member_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- NEU: Accordions für Regeln & Haftungsausschluss -->
                    <div class="pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Wichtige Informationen</h3>
                        
                        <!-- Accordion 1: Haftungsausschluss -->
                        <details class="group border border-gray-200 rounded-lg bg-gray-50 mb-3 overflow-hidden">
                            <summary class="flex justify-between items-center font-semibold cursor-pointer p-4 text-gray-800 hover:bg-gray-100 transition-colors">
                                <span class="flex items-center gap-2">🛡️ Haftungsausschluss & Sicherheit</span>
                                <span class="transition group-open:rotate-180">
                                    <svg fill="none" height="20" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="20"><path d="M19 9l-7 7-7-7"></path></svg>
                                </span>
                            </summary>
                            <div class="p-4 border-t border-gray-200 text-sm text-gray-700 bg-white space-y-2">
                                <p>Klettern und Bouldern sind mit Sturz- und Verletzungsrisiken verbunden. Ich nutze die Anlage eigenverantwortlich.</p>
                                <ul class="list-disc pl-5 space-y-1 mt-2">
                                    <li><strong>Bouldern:</strong> Ich bouldere nur über Matten, halte Sturzräume frei und übersteige keine Maximalhöhen.</li>
                                    <li><strong>Toprope:</strong> Nur wenn ich Gurt, Anseilen und Sicherungsgerät sicher beherrsche.</li>
                                    <li><strong>Vorstieg:</strong> Nur wenn ich Gurt, Einbinden, Clippen und das Halten von Stürzen sicher beherrsche.</li>
                                </ul>
                                <p class="text-amber-700 font-medium mt-2">Falls ich eine dieser Voraussetzungen nicht erfülle, klettere ich nur unter Aufsicht einer geschulten Person.</p>
                            </div>
                        </details>

                        <!-- Accordion 2: Hallenordnung -->
                        <details class="group border border-gray-200 rounded-lg bg-gray-50 mb-6 overflow-hidden">
                            <summary class="flex justify-between items-center font-semibold cursor-pointer p-4 text-gray-800 hover:bg-gray-100 transition-colors">
                                <span class="flex items-center gap-2">📋 Hallenordnung ÖTK-Langenlois</span>
                                <span class="transition group-open:rotate-180">
                                    <svg fill="none" height="20" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" width="20"><path d="M19 9l-7 7-7-7"></path></svg>
                                </span>
                            </summary>
                            <div class="p-4 border-t border-gray-200 text-sm text-gray-700 bg-white grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="font-semibold text-indigo-700 mb-1">🧹 Verhalten & Sauberkeit</h4>
                                    <ul class="list-disc pl-4 space-y-1 text-xs">
                                        <li>Dom sauber hinterlassen (Gurte & Schuhe aufräumen)</li>
                                        <li>Kein Essen im Dom; Getränke nur verschlossen</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-indigo-700 mb-1">🧗 Kletterregeln</h4>
                                    <ul class="list-disc pl-4 space-y-1 text-xs">
                                        <li>Bouldern: Rote Linie beachten, nicht übereinander</li>
                                        <li>Chalk-Balls erst ab VI+; nur mit Kletterschuhen</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-indigo-700 mb-1">🪝 Material</h4>
                                    <ul class="list-disc pl-4 space-y-1 text-xs">
                                        <li>Leih-Gurte & Karabiner an vorgesehene Haken</li>
                                        <li>Bälle/Stäbe/Tiere nur für Kursbetrieb</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-indigo-700 mb-1">📅 Termine & Beiträge</h4>
                                    <ul class="list-disc pl-4 space-y-1 text-xs">
                                        <li>Klettertreff & Kurse nur für ÖTK-Mitglieder</li>
                                        <li>Benützungsbeitrag: <strong>4 €</strong> Erwachsene · <strong>2 €</strong> unter 18 J. · <strong>10 €</strong> Familie</li>
                                    </ul>
                                </div>
                            </div>
                        </details>

                        <!-- Die Zustimmungs-Checkboxen -->
                        <div class="space-y-4 bg-gray-50 p-4 rounded-lg border border-gray-200">
                            <label class="flex items-start cursor-pointer">
                                <input type="checkbox" name="rules_accepted" value="1" class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('rules_accepted') ? 'checked' : '' }} required>
                                <span class="ml-3 text-sm text-gray-800">
                                    Ich habe die <strong>Hallenordnung</strong> gelesen und verpflichte mich, diese sowie die Anweisungen des Personals einzuhalten.
                                </span>
                            </label>

                            <label class="flex items-start cursor-pointer">
                                <input type="checkbox" name="waiver_accepted" value="1" class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ old('waiver_accepted') ? 'checked' : '' }} required>
                                <span class="ml-3 text-sm text-gray-800">
                                    Ich bestätige, dass ich die <strong>Risiken des Kletterns</strong> kenne und nur jene Bereiche selbständig nutze, für die ich ausreichend geschult bin (andernfalls nur unter Aufsicht).
                                </span>
                            </label>
                        </div>
                        
                        @error('rules_accepted') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        @error('waiver_accepted') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    {{-- Datenschutzhinweis --}}
                    <div class="mt-4 rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-xs text-blue-700">
                        🔒 <strong>Datenschutz:</strong> Wir speichern deine Daten gemäß DSGVO. Sie werden ausschließlich für den Hallenbetrieb verwendet, nicht an Dritte weitergegeben und auf Wunsch sofort gelöscht.
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            Registrierung absenden
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        
        document.getElementById('member_number').addEventListener('input', function (e) {
            let val = e.target.value.replace(/\D/g, ''); // nur Ziffern behalten
            if (val.length > 2) {
                val = val.slice(0, 2) + '-' + val.slice(2, 7);
            }
            e.target.value = val;
        });
        
        document.addEventListener('DOMContentLoaded', function () {
            // Radio Buttons für Mitglied/Gast
            const memberRadios = document.querySelectorAll('input[name="member_type"]');
            const memberNumberWrapper = document.getElementById('member-number-wrapper');
            const memberNumberInput = document.getElementById('member_number');
    
            function updateMemberNumberField() {
                const selected = document.querySelector('input[name="member_type"]:checked')?.value;
    
                if (selected === 'guest') {
                    memberNumberWrapper.classList.add('hidden');
                    memberNumberInput.required = false;
                    memberNumberInput.value = '';
                } else {
                    memberNumberWrapper.classList.remove('hidden');
                    memberNumberInput.required = true;
                }
            }
    
            memberRadios.forEach(function (radio) {
                radio.addEventListener('change', updateMemberNumberField);
            });
    
            updateMemberNumberField();
    
            // Alterslogik für Aufsicht und PDF
            const birthDateInput = document.getElementById('birth_date');
            const minorNote = document.getElementById('minor-note');
            const pdfLink = document.getElementById('minor-pdf-link');
            const supervisionWrapper = document.getElementById('supervision-wrapper');
            const supervisionCheckbox = document.getElementById('supervision_confirmed');
    
            function updateAgeRules() {
                if (!birthDateInput || !birthDateInput.value) {
                    minorNote.classList.add('hidden');
                    pdfLink.classList.add('hidden');
                    supervisionWrapper.classList.add('hidden');
                    supervisionCheckbox.required = false;
                    supervisionCheckbox.checked = false;
                    return;
                }
    
                const birthDate = new Date(birthDateInput.value);
                const today = new Date();
    
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
    
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
    
                if (age < 14) {
                    minorNote.classList.add('hidden');
                    pdfLink.classList.add('hidden');
                    supervisionWrapper.classList.remove('hidden');
                    supervisionCheckbox.required = true;
                } else if (age < 18) {
                    minorNote.classList.remove('hidden');
                    pdfLink.classList.remove('hidden');
                    supervisionWrapper.classList.add('hidden');
                    supervisionCheckbox.required = false;
                    supervisionCheckbox.checked = false;
                } else {
                    minorNote.classList.add('hidden');
                    pdfLink.classList.add('hidden');
                    supervisionWrapper.classList.add('hidden');
                    supervisionCheckbox.required = false;
                    supervisionCheckbox.checked = false;
                }
            }
    
            birthDateInput?.addEventListener('change', updateAgeRules);
            updateAgeRules();
    
            // Fix: pageshow feuert nach bfcache-Wiederherstellung, wenn der Browser
            // den Formularstatus bereits zurückgesetzt hat → UI-Zustand neu anwenden
            window.addEventListener('pageshow', function () { // ← NEU
                updateMemberNumberField();                    // ← NEU
                updateAgeRules();                             // ← NEU
            });                                               // ← NEU
        });
    </script>

</body>
</html>
````

## File: resources/views/admin/index.blade.php
````php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin | Kletterdom</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">

    @include('layouts.navigation')

    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    🛠️ Admin-Bereich
                </h2>
                <span class="text-sm text-gray-500">Hallenverwaltung</span>
            </div>
        </div>
    </header>

    <main class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg flex items-center gap-2">
                    <span>✅</span> {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg flex items-center gap-2">
                    <span>❌</span> {{ session('error') }}
                </div>
            @endif

            {{-- ── KPI-Karten ──────────────────────────────────────── --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <div class="text-3xl font-bold text-teal-600">{{ $stats['checked_in_today'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Heute eingecheckt</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <div class="text-3xl font-bold text-blue-500">{{ $stats['guests_today'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Davon Gäste heute</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <div class="text-3xl font-bold text-gray-700">{{ $stats['total_registrations'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Registrierungen gesamt</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <div class="text-3xl font-bold text-purple-500">{{ $stats['members'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Mitglieder (CSV)</div>
                </div>
            </div>

            {{-- ── Hallenauslastung Chart ───────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">📊 Hallenauslastung – letzte 30 Tage</h3>
                <div class="relative" style="height: 220px;">
                    <canvas id="auslastungChart"></canvas>
                </div>
            </div>

            {{-- ── Zwei-Spalten-Grid: Import + Export ──────────────── --}}
            <div class="grid md:grid-cols-2 gap-6">

                {{-- Mitglieder CSV-Import --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-1">📥 Mitglieder importieren</h3>
                    <p class="mt-1 text-xs text-gray-500">
                        CSV-Datei mit Spalten:
                        <code class="font-mono">Mitgliedsnummer; Nachname; Vorname; Email; Status; Betrag offen; Geburtsdatum</code>
                    </p>
                    <form action="{{ route('admin.importMembers') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">CSV-Datei wählen</label>
                            <input type="file" name="members_csv" accept=".csv,.txt"
                                class="block w-full text-sm text-gray-500
                                       file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                                       file:text-sm file:font-semibold file:bg-teal-50 file:text-teal-700
                                       hover:file:bg-teal-100 border border-gray-200 rounded-lg p-1">
                            @error('members_csv')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit"
                            class="w-full bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold py-2 px-4 rounded-lg transition">
                            Importieren
                        </button>
                    </form>
                </div>

                {{-- Check-ins CSV-Export --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-1">📤 Check-ins exportieren</h3>
                    <p class="text-sm text-gray-400 mb-4">
                        Exportiert alle Check-ins im gewählten Zeitraum als CSV (Excel-kompatibel, UTF-8 BOM, Semikolon-getrennt).
                    </p>
                    <form action="{{ route('admin.exportCheckins') }}" method="GET" class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Von</label>
                                <input type="date" name="from" value="{{ now()->subDays(30)->toDateString() }}"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-400 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Bis</label>
                                <input type="date" name="to" value="{{ now()->toDateString() }}"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-400 focus:outline-none">
                            </div>
                        </div>
                        <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-4 rounded-lg transition">
                            CSV herunterladen
                        </button>
                    </form>
                </div>
            </div>

            {{-- ── Registrierungen Tabelle ─────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-700">👥 Alle Registrierungen</h3>
                    <span class="text-sm text-gray-400">{{ $registrations->total() }} gesamt</span>
                </div>

                {{-- Desktop-Tabelle --}}
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wide">
                            <tr>
                                <th class="px-4 py-3 text-left">Name</th>
                                <th class="px-4 py-3 text-left">Typ</th>
                                <th class="px-4 py-3 text-left">Mitgliedsnr.</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Check-ins</th>
                                <th class="px-4 py-3 text-left">Registriert am</th>
                                <th class="px-4 py-3 text-left">Aktion</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse ($registrations as $reg)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 font-medium text-gray-800">
                                        {{ $reg->first_name }} {{ $reg->last_name }}
                                        @if ($reg->birth_date)
                                            <div class="text-xs text-gray-400">{{ $reg->birth_date?->format('d.m.Y') ?? '—' }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($reg->member_type === 'member')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Mitglied</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Gast</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-500">{{ $reg->member_number ?? '–' }}</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $colors = [
                                                'green'  => 'bg-green-100 text-green-700',
                                                'blue'   => 'bg-blue-100 text-blue-700',
                                                'orange' => 'bg-orange-100 text-orange-700',
                                                'red'    => 'bg-red-100 text-red-700',
                                            ];
                                            $cls = $colors[$reg->access_status] ?? 'bg-gray-100 text-gray-600';
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $cls }}">
                                            {{ $reg->access_status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 tabular-nums">{{ $reg->checkins_count ?? $reg->checkins->count() }}</td>
                                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $reg->created_at->format('d.m.Y H:i') }}</td>
                                    <td class="px-4 py-3">
                                        <form action="{{ route('admin.registrations.destroy', $reg) }}" method="POST"
                                              onsubmit="return confirm('Registrierung von {{ addslashes($reg->first_name . ' ' . $reg->last_name) }} wirklich löschen? Alle Check-ins werden mitgelöscht.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-xs text-red-500 hover:text-red-700 hover:underline transition">
                                                Löschen
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                                        Noch keine Registrierungen vorhanden.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Cards --}}
                <div class="md:hidden divide-y divide-gray-100">
                    @forelse ($registrations as $reg)
                        <div class="px-4 py-4 flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-gray-800 truncate">{{ $reg->first_name }} {{ $reg->last_name }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $reg->member_type === 'member' ? 'Mitglied' : 'Gast' }}
                                    @if ($reg->member_number) · {{ $reg->member_number }} @endif
                                    · {{ $reg->checkins->count() }} Check-ins
                                </div>
                                @php $colors = ['green'=>'text-green-600','blue'=>'text-blue-500','orange'=>'text-orange-500','red'=>'text-red-500']; @endphp
                                <div class="text-xs font-medium mt-1 {{ $colors[$reg->access_status] ?? 'text-gray-500' }}">
                                    ● {{ $reg->access_status }}
                                </div>
                            </div>
                            <form action="{{ route('admin.registrations.destroy', $reg) }}" method="POST"
                                  onsubmit="return confirm('Wirklich löschen?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-400 hover:text-red-600 shrink-0">🗑️</button>
                            </form>
                        </div>
                    @empty
                        <div class="px-4 py-10 text-center text-gray-400">Keine Registrierungen.</div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if ($registrations->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $registrations->links() }}
                    </div>
                @endif
            </div>

        </div>
    </main>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const labels = @json($labels);
        const values = @json($values);

        new Chart(document.getElementById('auslastungChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Check-ins',
                    data: values,
                    backgroundColor: 'rgba(13, 148, 136, 0.7)',   // teal-600
                    borderColor:     'rgba(13, 148, 136, 1)',
                    borderWidth: 1,
                    borderRadius: 4,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.parsed.y} Check-in${ctx.parsed.y !== 1 ? 's' : ''}`
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 11 },
                            maxRotation: 45,
                            callback: function(val, index) {
                                return index % 3 === 0 ? this.getLabelForValue(val) : '';
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, font: { size: 11 } },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    }
                }
            }
        });
    </script>
</body>
</html>
````

## File: routes/web.php
````php
<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\StaffController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/halle-register', [RegistrationController::class, 'create'])->name('register');
Route::post('/halle-register', [RegistrationController::class, 'store'])
    ->middleware('throttle:3,1')
    ->name('register.store');
Route::get('/verify/{token}', [RegistrationController::class, 'verify'])->name('verify');

// Angepasste Dashboard-Weiterleitung basierend auf der Rolle
Route::get('/dashboard', function () {
    if (auth()->check() && auth()->user()->is_admin) {
        return redirect()->route('admin.index');
    }
    
    return redirect()->route('staff');
})->middleware(['auth'])->name('dashboard');

require __DIR__ . '/auth.php';

// Alle geschützten Routen (erfordern Login)
Route::middleware(['auth'])->group(function () {
    
    // ==========================================
    // BEREICH FÜR ALLE (Staff & Admin)
    // ==========================================
    Route::get('/hallendienst', [StaffController::class, 'index'])->name('staff');
    Route::post('/hallendienst/check-in/{registration}', [StaffController::class, 'checkin'])->name('staff.checkin');
    Route::post('/hallendienst/import-members', [StaffController::class, 'importMembers'])->name('staff.importMembers');
    Route::post('/hallendienst/kulanz/{registration}', [StaffController::class, 'grantKulanz'])->name('staff.kulanz');
    Route::post('/hallendienst/{registration}/kulanz-checkin', [StaffController::class, 'kulanzCheckin'])->name('staff.kulanz-checkin');
    Route::post('/hallendienst/{registration}/parent-consent', [StaffController::class, 'confirmParentConsent'])->name('staff.parent-consent');
    Route::post('/hallendienst/checkout-all', [StaffController::class, 'checkoutAll'])->name('staff.checkout-all');
    Route::post('/verify/{token}/checkin', [RegistrationController::class, 'checkin'])->name('verify.checkin');
    
    // ==========================================
    // EXKLUSIVER ADMIN-BEREICH
    // ==========================================
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::post('/import-members', [AdminController::class, 'importMembers'])->name('importMembers');
        Route::get('/export-checkins', [AdminController::class, 'exportCheckins'])->name('exportCheckins');
        Route::delete('/registrations/{registration}', [AdminController::class, 'destroyRegistration'])->name('registrations.destroy');
    });
});
````

## File: docker-compose.yml
````yaml
services:

  app:
    build: .                          # Image lokal bauen
    image: kletter-checkin
    container_name: kletter-app
    restart: unless-stopped
    depends_on:
      db:
        condition: service_healthy    # Warten bis MySQL wirklich bereit ist
    networks:
      - kletternet
    environment:
      - TZ=Europe/Vienna
    # Kein Volume-Mount! vendor/ würde sonst überschrieben werden.
    volumes:
      - ./backups:/var/www/html/storage/backups  # ← NUR dieser Unterordner

  nginx:
    image: nginx:alpine
    container_name: kletter-nginx
    restart: unless-stopped
    ports:
      - "80:80"
    volumes:
      - ./docker/nginx.conf:/etc/nginx/conf.d/default.conf
      - ./public:/var/www/html/public   # ← NEU: public-Ordner vom Host
    depends_on:
      - app
    networks:
      - kletternet

  db:
    image: mysql:8.0
    container_name: kletter-db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: klettercheckin
      MYSQL_USER: checkinuser
      MYSQL_PASSWORD: ${DB_PASSWORD}              # aus .env – nicht hardcoded!
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}    # aus .env – nicht hardcoded!
    volumes:
      - dbdata:/var/lib/mysql
    networks:
      - kletternet
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-p${DB_ROOT_PASSWORD}"]
      interval: 10s
      timeout: 5s
      retries: 5

  node:
    image: node:20-alpine
    working_dir: /var/www
    volumes:
      - .:/var/www
    profiles: ["build"]

volumes:
  dbdata:

networks:
  kletternet:
    driver: bridge
````

## File: app/Http/Controllers/StaffController.php
````php
<?php

namespace App\Http\Controllers;

use App\Models\Checkin;
use App\Models\Member;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');
    
        $registrations = Registration::with('member', 'currentCheckin')
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('first_name', 'like', "%$query%")
                        ->orWhere('last_name', 'like', "%$query%")
                        ->orWhere('member_number', 'like', "%$query%");
                });
            })
            ->orderByDesc('created_at')
            ->get()
            ->sortByDesc(fn($r) => $r->currentCheckin?->checked_in_at?->timestamp ?? 0);
    
        $stats = [
            'checkedInToday'     => Checkin::whereDate('checked_in_at', today())->count(),
            'guestsToday'        => Checkin::whereDate('checked_in_at', today())
                ->whereHas('registration', fn($q) => $q->where('member_type', 'guest'))
                ->count(),
            'membersToday'       => Checkin::whereDate('checked_in_at', today())
                ->whereHas('registration', fn($q) => $q->where('member_type', 'member'))
                ->count(),
            'totalRegistrations' => Registration::count(),
        ];
    
        return view('staff.index', compact('registrations', 'query', 'stats'));
    }

    public function checkin(Registration $registration)
    {
        $registration->load('currentCheckin', 'member'); // ← 'member' neu laden
    
        if ($registration->access_status === 'red') {
            return redirect()
                ->route('staff')
                ->with('error', 'Check-in verweigert: Kein Zutritt erlaubt.');
        }
    
        if ($registration->currentCheckin) {
            return redirect()
                ->route('staff')
                ->with('error', 'Diese Person ist bereits eingecheckt.');
        }
    
        // Schnuppergast-Limit
        if ($registration->member_type === 'guest' && $registration->trial_visits_count >= 1) {
            $hasActiveKulanz = $registration->manual_exception_until
                && $registration->manual_exception_until->isFuture();
    
            if (! $hasActiveKulanz) {
                return redirect()
                    ->route('staff')
                    ->with('error', 'Check-in verweigert: Schnuppergast hat den Erstbesuch bereits absolviert. Bitte Kulanz gewähren.');
            }
        }
    
        // ↓ NEU: Mitglied nicht in CSV → max. 3 Check-ins
        $isUnverifiedMember = $registration->member_type === 'member'
                              && $registration->member === null;
    
        if ($isUnverifiedMember) {
            $totalCheckins = Checkin::where('registration_id', $registration->id)->count();
    
            if ($totalCheckins >= 3) {
                // Sicherheitsnetz: Status auf red setzen falls noch nicht geschehen
                $registration->update([
                    'access_status' => 'red',
                    'access_reason' => 'Mitgliedsnummer nicht im System – Limit erreicht',
                ]);
    
                return redirect()
                    ->route('staff')
                    ->with('error', 'Check-in verweigert: Mitgliedsnummer nicht im Mitgliedersystem. Limit von 3 Besuchen ausgeschöpft.');
            }
        }
    
        Checkin::create([
            'registration_id' => $registration->id,
            'checked_in_at'   => now(),
        ]);
    
        $registration->increment('trial_visits_count');
    
        // ↓ NEU: Nach dem Check-in prüfen ob Limit jetzt erreicht
        if ($isUnverifiedMember) {
            $totalCheckins = Checkin::where('registration_id', $registration->id)->count();
    
            if ($totalCheckins >= 3) {
                $registration->update([
                    'access_status' => 'red',
                    'access_reason' => 'Mitgliedsnummer nicht im System – Limit erreicht',
                ]);
            }
        }
    
        return redirect()
            ->route('staff')
            ->withSuccess($registration->first_name . ' ' . $registration->last_name . ' wurde erfolgreich eingecheckt.');
    }

    public function importMembers(Request $request)
    {
        $request->validate([
            'members_csv' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $storedPath = $request->file('members_csv')->store('imports');

        if (!$storedPath) {
            return redirect()
                ->route('staff')
                ->with('error', 'CSV konnte nicht sicher gespeichert werden.');
        }

        $fullPath = Storage::path($storedPath);
        $handle = fopen($fullPath, 'r');

        if (!$handle) {
            Storage::delete($storedPath);

            return redirect()
                ->route('staff')
                ->with('error', 'CSV konnte nicht geöffnet werden.');
        }

        try {
            $headers = fgetcsv($handle, 0, ';');

            if (!$headers) {
                return redirect()
                    ->route('staff')
                    ->with('error', 'CSV konnte nicht gelesen werden.');
            }

            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);

            if (!in_array('Mitgliedsnummer', $headers, true)) {
                return redirect()
                    ->route('staff')
                    ->with('error', 'CSV-Format ungültig: Spalte "Mitgliedsnummer" fehlt.');
            }

            $imported = 0;

            while (($row = fgetcsv($handle, 0, ';')) !== false) {
                if (count($row) !== count($headers)) {
                    continue;
                }

                $data = array_combine($headers, $row);

                $memberNumber = trim($data['Mitgliedsnummer'] ?? '');

                if ($memberNumber === '') {
                    continue;
                }

                $betragOffen = trim((string) ($data['Betrag_offen'] ?? ''));
                $paymentStatus = $betragOffen === '' ? 'paid' : 'open';

                $birthDate = $this->parseCsvDate($data['Geb_Datum'] ?? null);
                $exitDate  = $this->parseCsvDate($data['Austrittsdatum'] ?? null);

                $csvStatus = strtolower(trim((string) ($data['Status'] ?? '')));
                $inactiveStatuses = ['gelöscht', 'ausgetreten', 'gesperrt', 'inaktiv', 'gekündigt'];
                
                $membershipStatus = 'active';
                if (in_array($csvStatus, $inactiveStatuses, true)) {
                    $membershipStatus = 'inactive';
                } elseif ($exitDate && $exitDate->isPast()) {
                    $membershipStatus = 'inactive';
                }

                Member::updateOrCreate(
                    ['member_number' => $memberNumber],
                    [
                        'first_name'        => trim((string) ($data['Vorname'] ?? '')),
                        'last_name'         => trim((string) ($data['Nachname'] ?? '')),
                        'email'             => $this->nullIfEmpty($data['Email'] ?? null),
                        'birth_date'        => $birthDate?->format('Y-m-d'),
                        'membership_status' => $membershipStatus,
                        'payment_status'    => $paymentStatus,
                        'last_imported_at'  => now(),
                    ]
                );
                
                // ↓ NEU: Registrierungen synchronisieren
            if ($membershipStatus === 'active' && $paymentStatus === 'paid') {
                Registration::where('member_number', $memberNumber)
                    ->where('access_status', 'red')
                    ->where(function ($q) {
                        $q->where('access_reason', 'like', '%inaktiv%')
                          ->orWhere('access_reason', 'like', '%Schnupperlimit%')
                          ->orWhere('access_reason', 'like', '%ausgetreten%')
                          ->orWhere('access_reason', 'like', '%gelöscht%');
                    })
                    ->update([
                        'access_status' => 'green',
                        'access_reason' => 'Mitgliedschaft aktiv bezahlt',
                    ]);
            } elseif ($membershipStatus === 'inactive') {
                Registration::where('member_number', $memberNumber)
                    ->where('access_status', '!=', 'red')
                    ->update([
                        'access_status' => 'red',
                        'access_reason' => 'Mitgliedschaft inaktiv',
                    ]);
            }

                $imported++;
            }

            if ($imported === 0) {
                return redirect()
                    ->route('staff')
                    ->with('error', 'CSV wurde gelesen, aber es konnten keine Datensätze importiert werden.');
            }

            return redirect()
                ->route('staff')
                ->with('success', "Mitgliederimport abgeschlossen: {$imported} Datensätze verarbeitet.");
        } finally {
            fclose($handle);
            Storage::delete($storedPath);
        }
    }

    public function grantKulanz(Request $request, Registration $registration)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);
    
        $reason = strip_tags($request->reason); // ← NEU
    
        $registration->update([
            'manual_exception_reason' => $reason,
            'manual_exception_until'  => now()->endOfDay(),
            'access_status'           => 'orange',
            'access_reason'           => 'Kulanz: ' . $reason,
        ]);
    
        return redirect()
            ->route('staff')
            ->with('success', 'Kulanz gewährt für ' . e($registration->first_name) . '!');
    }
    
        /**
     * Kulanz erteilen UND sofort Check-in durchführen (für orange-Status).
     */
    public function kulanzCheckin(Request $request, Registration $registration): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);
    
        $reason = strip_tags($request->reason);
    
        $registration->load('currentCheckin');
        if ($registration->currentCheckin) {
            return redirect()
                ->route('staff')
                ->with('error', e($registration->first_name) . ' ist bereits eingecheckt.');
        }
    
        // 1. Kulanz erteilen
        $registration->update([
            'manual_exception_reason' => $reason,
            'manual_exception_until'  => now()->endOfDay(),
            'access_status'           => 'orange',
            'access_reason'           => 'Kulanz: ' . $reason,
        ]);
    
        // 2. Check-in sofort durchführen
        Checkin::create([
            'registration_id' => $registration->id,
            'checked_in_at'   => now(),
        ]);
    
        $registration->increment('trial_visits_count');
    
        return redirect()
            ->route('staff')
            ->with('success', '✓ Kulanz erteilt & ' . e($registration->first_name) . ' ' . e($registration->last_name) . ' eingecheckt.');
    }
    
    public function checkoutAll(): RedirectResponse
    {
        $now = now();
    
        $openCheckins = Checkin::whereNull('checked_out_at')->get();
        $count = $openCheckins->count();
    
        foreach ($openCheckins as $checkin) {
            $checkin->update(['checked_out_at' => $now]);
        
            $registration = Registration::find($checkin->registration_id);
            if (!$registration) continue;
        
            $registration->update(['checked_in_at' => null]);
        
            // Schnuppergast: nach 3 Besuchen → red
            if ($registration->member_type === 'guest'
                && $registration->trial_visits_count >= 3) {
                $registration->update([
                    'access_status' => 'red',
                    'access_reason' => 'Schnupperlimit ausgeschöpft (3/3)',
                ]);
            }
        
            // NEU: Unverified Member: nach 3 Besuchen → red
            $isUnverifiedMember = $registration->member_type === 'member'
                                  && $registration->member === null;
        
            if ($isUnverifiedMember && $registration->trial_visits_count >= 3) {
                $registration->update([
                    'access_status' => 'red',
                    'access_reason' => 'Mitgliedsnummer nicht im System – Limit erreicht',
                ]);
            }
        }
    
        return redirect()->route('staff')
            ->with('success', '✓ ' . $count . ' ' . ($count === 1 ? 'Person' : 'Personen') . ' ausgecheckt.');
    }

    public function confirmParentConsent(Registration $registration)
    {
        $registration->update([
            'parent_consent_received'    => true,
            'parent_consent_received_at' => now(),
            'needs_parent_consent'       => false,
        ]);

        return redirect()
            ->route('staff')
            ->with('success', 'Einverständniserklärung wurde bestätigt.');
    }

    private function parseCsvDate(?string $value): ?Carbon
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('d.m.Y', $value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function nullIfEmpty(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
````

## File: Dockerfile
````dockerfile
FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    supervisor \
    git \
    curl \
    zip \
    unzip \
    default-mysql-client \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mysqli \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && mkdir -p /var/log/supervisor /var/run/supervisor

# Copy supervisor config
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Composer install
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --optimize-autoloader

# Copy application
COPY . .

# Dump optimized autoload
RUN git config --global --add safe.directory /var/www/html && \
    composer dump-autoload --no-dev --optimize && \
    php artisan package:discover --ansi 2>/dev/null || true

# Set permissions
RUN chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache && \
    chmod -R 775 \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
````

## File: resources/views/staff/index.blade.php
````php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Check-in | Kletterdom</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">

    @include('layouts.navigation')

    <main class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- STATISTIK-KARTEN --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <div class="text-3xl font-bold text-teal-600">{{ $stats['checkedInToday'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Heute eingecheckt</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <div class="text-3xl font-bold text-blue-500">{{ $stats['guestsToday'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Davon Gäste</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <div class="text-3xl font-bold text-indigo-600">{{ $stats['membersToday'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Davon Mitglieder</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <div class="text-3xl font-bold text-gray-700">{{ $stats['totalRegistrations'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Registrierungen gesamt</div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                <h2 class="text-xl font-semibold text-gray-800">Check-In Ansicht</h2>
                <div class="flex flex-wrap items-center gap-2">

                    {{-- Alle auschecken --}}
                    <form method="POST" action="{{ route('staff.checkout-all') }}"
                          onsubmit="return confirm('Alle aktuell eingecheckten Personen auschecken?')">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 bg-white border border-gray-300 text-gray-700 rounded-lg px-4 py-2 text-sm font-semibold hover:bg-red-50 hover:border-red-300 hover:text-red-700 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                            </svg>
                            Alle auschecken
                        </button>
                    </form>

                    {{-- QR-Scanner --}}
                    <button id="qr-toggle-btn" onclick="toggleScanner()"
                        class="inline-flex items-center gap-2 bg-indigo-600 text-white rounded-lg px-4 py-2 text-sm font-semibold hover:bg-indigo-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4h6v6H4V4zm0 10h6v6H4v-6zm10-10h6v6h-6V4zm4 10h2v2h-2v-2zm-4 0h2v2h-2v-2zm0 4h2v2h-2v-2zm4-2h2v2h-2v-2zm0 4h2v2h-2v-2z"/>
                        </svg>
                        QR-Code scannen
                    </button>

                </div>
            </div>

            {{-- QR-SCANNER PANEL --}}
            <div id="qr-scanner-panel" class="hidden mb-6 bg-white border border-indigo-200 rounded-xl shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 bg-indigo-50 border-b border-indigo-100">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="text-sm font-semibold text-indigo-800">Kamera-Scanner</span>
                    </div>
                    <button onclick="toggleScanner()" class="text-indigo-400 hover:text-indigo-700 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-4">
                    <div class="mb-3 flex items-center gap-3">
                        <label for="camera-select" class="text-xs text-gray-500 whitespace-nowrap">Kamera:</label>
                        <select id="camera-select"
                            class="flex-1 border border-gray-300 rounded-md px-2 py-1.5 text-sm bg-white text-gray-900 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Wird geladen…</option>
                        </select>
                        <button onclick="startScanner()"
                            class="inline-flex items-center bg-indigo-600 text-white rounded-md px-3 py-1.5 text-xs font-semibold hover:bg-indigo-700 transition">
                            Starten
                        </button>
                        <button onclick="stopScanner()"
                            class="inline-flex items-center bg-white border border-gray-300 text-gray-700 rounded-md px-3 py-1.5 text-xs font-semibold hover:bg-gray-50 transition">
                            Stopp
                        </button>
                    </div>
                    <div id="qr-reader"
                        class="rounded-lg overflow-hidden border border-gray-200 bg-gray-900"
                        style="width:100%; max-width:480px; min-height:240px; margin:0 auto;">
                    </div>
                    <div id="qr-status" class="mt-3 hidden rounded-lg px-4 py-3 text-sm font-medium"></div>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                    <ul class="list-disc pl-5 m-0">
                        @foreach ($errors->all() as $error)
                            <li class="my-1">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Suche --}}
            <div class="mb-6 bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <form method="GET" action="{{ route('staff') }}"
                    class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-3">
                    <div class="flex-1 min-w-0 sm:min-w-[280px]">
                        <input type="text" name="q" value="{{ $query }}"
                            placeholder="Name oder Mitgliedsnummer suchen"
                            class="block w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white text-gray-900 focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="flex gap-2 flex-col sm:flex-row">
                        <button type="submit"
                            class="inline-flex items-center justify-center bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                            Suchen
                        </button>
                        <a href="{{ route('staff') }}"
                            class="inline-flex items-center justify-center bg-white border border-gray-300 rounded-lg px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition no-underline">
                            Zurücksetzen
                        </a>
                    </div>
                </form>
            </div>

            {{-- ============================================================ --}}
            {{-- Mobile Cards                                                  --}}
            {{-- ============================================================ --}}
            <div class="space-y-4 md:hidden">
                @php $shownDividerMobile = false; @endphp

                @forelse ($registrations as $registration)
                    @php
                        $currentCheckin  = $registration->currentCheckin;
                        $hasActiveKulanz = $registration->manual_exception_until
                                           && $registration->manual_exception_until->isFuture();
                        $visits          = $registration->trial_visits_count ?? 0;

                        $isTrialMaxReached        = $registration->member_type === 'guest' && $visits >= 3;
                        $isUnverifiedMemberBlocked = $registration->member_type === 'member'
                                                     && $registration->member === null
                                                     && $registration->access_status === 'red';
                        $isTrialLimitReached      = $registration->member_type === 'guest'
                                                     && $visits >= 1 && $visits < 3
                                                     && !$hasActiveKulanz;

                        $needsKulanz = (in_array($registration->access_status, ['red', 'orange']) && !$hasActiveKulanz)
                                       || $isTrialLimitReached;

                        // Definitiv gesperrt: kein Button, kein Kulanz-Formular
                        $isHardBlocked = $isTrialMaxReached
                                         || $isUnverifiedMemberBlocked
                                         || ($registration->access_status === 'red' && !$hasActiveKulanz);

                        $kulanzHint = match (true) {
                            $registration->access_status === 'red' => 'Person gesperrt',
                            $isTrialLimitReached                   => 'Schnupperlimit erreicht (' . $visits . ')',
                            default                                => 'Aktion erforderlich',
                        };
                        $hintIcon  = $registration->access_status === 'red' ? '🚫' : '⚠️';
                        $hintColor = $registration->access_status === 'red' ? 'text-red-600' : 'text-amber-600';

                        $accessStyle = match ($registration->access_status) {
                            'green'  => 'bg-green-100 text-green-800',
                            'blue'   => 'bg-blue-100 text-blue-800',
                            'orange' => 'bg-amber-100 text-amber-800',
                            default  => 'bg-red-100 text-red-800',
                        };
                        $accessText = match ($registration->access_status) {
                            'green'  => 'Zutritt ok',
                            'blue'   => 'Schnuppern',
                            'orange' => $registration->manual_exception_reason ? 'Kulanz' : 'Warnung',
                            default  => 'Gesperrt',
                        };
                    @endphp

                    @if (!$shownDividerMobile && !$currentCheckin)
                        @php $shownDividerMobile = true; @endphp
                        <div class="px-2 pt-2 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                            Noch nicht eingecheckt
                        </div>
                    @endif

                    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm space-y-3
                        {{ $currentCheckin ? 'border-l-2 border-l-indigo-300' : '' }}">

                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $registration->first_name }} {{ $registration->last_name }}
                                </div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $registration->birth_date?->format('d.m.Y') ?? '—' }}
                                    · {{ $registration->member_type === 'guest' ? 'Gast' : 'Mitglied' }}
                                    @if ($registration->member_number)
                                        · {{ $registration->member_number }}
                                    @endif
                                </div>
                            </div>
                            {{-- ZUTRITT-BADGE: eingecheckt → immer grün ✅ --}}
                            <div class="flex flex-col items-end gap-1 shrink-0">
                                @if ($currentCheckin)
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold bg-green-100 text-green-800">
                                        ✅ Eingecheckt
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $accessStyle }}">
                                        {{ $accessText }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Zusatzinfos --}}
                        @if ($registration->needs_parent_consent)
                            <div class="text-xs text-gray-600 space-y-1 border-t border-gray-100 pt-2">
                                <div>Klettert alleine? – dann Formular nötig
                                    (<a href="https://www.oetk-langenlois.at/fileadmin/Einverstaendniserklaerung-14-18.pdf"
                                        target="_blank" rel="noopener noreferrer"
                                        class="underline text-gray-500">PDF</a>)
                                    @if ($registration->parent_consent_received)
                                        <span class="text-gray-400">(geprüft)</span>
                                    @else
                                        <form method="POST" action="{{ route('staff.parent-consent', $registration) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="underline text-gray-600 bg-transparent border-none p-0 cursor-pointer text-xs">
                                                Formular abgegeben
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @elseif (!$currentCheckin && $registration->access_reason)
                            <div class="text-xs text-gray-500 border-t border-gray-100 pt-2">
                                {{ $registration->access_reason }}
                            </div>
                        @endif

                        <div class="border-t border-gray-100 pt-3">
                            @if ($currentCheckin)
                                <span class="text-sm text-gray-500">
                                    Eingecheckt {{ $currentCheckin->checked_in_at->format('H:i') }} Uhr
                                </span>
                            @elseif ($isHardBlocked)
                                <button type="button" disabled
                                    class="w-full inline-flex items-center justify-center border border-gray-200 bg-gray-100 text-gray-400 rounded-lg px-3 py-2 text-sm font-semibold cursor-not-allowed">
                                    Check-in
                                </button>
                            @elseif ($needsKulanz)
                                <form method="POST" action="{{ route('staff.kulanz-checkin', $registration) }}"
                                    class="flex flex-col gap-2">
                                    @csrf
                                    <span class="text-xs font-semibold {{ $hintColor }}">
                                        {{ $hintIcon }} {{ $kulanzHint }}
                                    </span>
                                    <input type="text" name="reason" placeholder="Kulanzgrund" required
                                        class="block w-full border border-gray-300 rounded-md px-3 py-2 text-sm bg-white text-gray-900 focus:border-amber-500 focus:ring-amber-500">
                                    <button type="submit"
                                        class="self-start text-xs font-semibold text-amber-700 underline bg-transparent border-none p-0 cursor-pointer hover:text-amber-900">
                                        Checkin mit Kulanz
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('staff.checkin', $registration) }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full inline-flex items-center justify-center border border-transparent bg-indigo-600 text-white rounded-lg px-3 py-2 text-sm font-semibold hover:bg-indigo-700 transition">
                                        Check-in
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="bg-white border border-gray-200 rounded-xl p-6 text-center text-sm text-gray-500 shadow-sm">
                        Keine Registrierungen gefunden.
                    </div>
                @endforelse
            </div>

            {{-- ============================================================ --}}
            {{-- Desktop Table                                                 --}}
            {{-- ============================================================ --}}
            <div class="hidden md:block bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Mitgliedsnr.</th>
                                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Zutritt</th>
                                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Zusatzinfos</th>
                                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">Check-in / Aktion</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @php $shownDivider = false; @endphp

                            @forelse ($registrations as $registration)
                                @php
                                    $currentCheckin  = $registration->currentCheckin;
                                    $hasActiveKulanz = $registration->manual_exception_until
                                                       && $registration->manual_exception_until->isFuture();
                                    $visits          = $registration->trial_visits_count ?? 0;

                                    $isTrialMaxReached        = $registration->member_type === 'guest' && $visits >= 3;
                                    $isUnverifiedMemberBlocked = $registration->member_type === 'member'
                                                                 && $registration->member === null
                                                                 && $registration->access_status === 'red';
                                    $isTrialLimitReached      = $registration->member_type === 'guest'
                                                                 && $visits >= 1 && $visits < 3
                                                                 && !$hasActiveKulanz;

                                    $needsKulanz = (in_array($registration->access_status, ['red', 'orange']) && !$hasActiveKulanz)
                                                   || $isTrialLimitReached;

                                    // Definitiv gesperrt: kein Button, kein Kulanz-Formular
                                    $isHardBlocked = $isTrialMaxReached
                                                     || $isUnverifiedMemberBlocked
                                                     || ($registration->access_status === 'red' && !$hasActiveKulanz);

                                    $kulanzHint = match (true) {
                                        $registration->access_status === 'red' => 'Person gesperrt',
                                        $isTrialLimitReached                   => 'Schnupperlimit erreicht (' . $visits . ')',
                                        default                                => 'Aktion erforderlich',
                                    };
                                    $hintIcon  = $registration->access_status === 'red' ? '🚫' : '⚠️';
                                    $hintColor = $registration->access_status === 'red' ? 'text-red-600' : 'text-amber-600';

                                    $accessStyle = match ($registration->access_status) {
                                        'green'  => 'bg-green-100 text-green-800',
                                        'blue'   => 'bg-blue-100 text-blue-800',
                                        'orange' => 'bg-amber-100 text-amber-800',
                                        default  => 'bg-red-100 text-red-800',
                                    };
                                    $accessText = match ($registration->access_status) {
                                        'green'  => 'Zutritt ok',
                                        'blue'   => 'Schnuppergast',
                                        'orange' => $registration->manual_exception_reason ? 'Kulanz' : 'Warnung',
                                        default  => 'Gesperrt',
                                    };
                                @endphp

                                @if (!$shownDivider && !$currentCheckin)
                                    @php $shownDivider = true; @endphp
                                    <tr>
                                        <td colspan="5"
                                            class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider bg-gray-50 border-t border-b border-gray-100">
                                            Noch nicht eingecheckt
                                        </td>
                                    </tr>
                                @endif

                                <tr class="hover:bg-gray-50">

                                    <td class="px-4 py-4 align-top">
                                        <div class="text-sm font-semibold text-gray-900">
                                            {{ $registration->first_name }} {{ $registration->last_name }}
                                        </div>
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            {{ $registration->birth_date?->format('d.m.Y') ?? '—' }}
                                            · {{ $registration->member_type === 'guest' ? 'Gast' : 'Mitglied' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 align-top text-sm text-gray-600">
                                        {{ $registration->member_number ?? '—' }}
                                    </td>

                                    {{-- ZUTRITT-SPALTE: eingecheckt → immer grün ✅ --}}
                                    <td class="px-4 py-4 align-top">
                                        @if ($currentCheckin)
                                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-bold bg-green-100 text-green-800">
                                                ✅ Eingecheckt
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $accessStyle }}">
                                                {{ $accessText }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- ZUSATZINFOS-SPALTE --}}
                                    <td class="px-4 py-4 align-top text-sm text-gray-600">
                                        @if ($registration->needs_parent_consent)
                                            <div>Klettert alleine? – dann Formular nötig
                                                (<a href="https://www.oetk-langenlois.at/fileadmin/Einverstaendniserklaerung-14-18.pdf"
                                                    target="_blank" rel="noopener noreferrer"
                                                    class="text-gray-500 underline">PDF</a>)
                                            </div>
                                            <div class="text-xs text-gray-400 mt-0.5">
                                                @if ($registration->parent_consent_received)
                                                    Formular geprüft
                                                @else
                                                    <form method="POST" action="{{ route('staff.parent-consent', $registration) }}">
                                                        @csrf
                                                        <button type="submit"
                                                            class="text-xs text-gray-600 underline bg-transparent border-none p-0 cursor-pointer hover:text-gray-900">
                                                            Formular abgegeben
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @elseif (!$currentCheckin && $registration->access_reason)
                                            <span class="text-gray-600">{{ $registration->access_reason }}</span>
                                        @elseif ($currentCheckin)
                                            <span class="text-gray-300">—</span>
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </td>

                                    {{-- CHECK-IN / AKTION --}}
                                    <td class="px-4 py-4 align-top">
                                        @if ($currentCheckin)
                                            <span class="text-sm text-gray-500">
                                                Eingecheckt {{ $currentCheckin->checked_in_at->format('H:i') }} Uhr
                                            </span>
                                        @elseif ($isHardBlocked)
                                            <button type="button" disabled
                                                class="inline-flex items-center justify-center border border-gray-200 bg-gray-100 text-gray-400 rounded-lg px-3 py-2 text-sm font-semibold cursor-not-allowed">
                                                Check-in
                                            </button>
                                        @elseif ($needsKulanz)
                                            <div class="flex flex-col gap-1.5">
                                                <span class="text-xs font-semibold {{ $hintColor }}">
                                                    {{ $hintIcon }} {{ $kulanzHint }}
                                                </span>
                                                <form method="POST" action="{{ route('staff.kulanz-checkin', $registration) }}"
                                                    class="flex flex-col gap-1.5">
                                                    @csrf
                                                    <input type="text" name="reason"
                                                        placeholder="Grund für Kulanz ..." required
                                                        class="block w-full max-w-[180px] border border-gray-300 rounded-md px-2 py-1.5 text-xs bg-white text-gray-900 focus:border-amber-500 focus:ring-amber-500">
                                                    <button type="submit"
                                                        class="self-start text-xs font-semibold text-amber-700 underline bg-transparent border-none p-0 cursor-pointer hover:text-amber-900">
                                                        Checkin mit Kulanz
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <form method="POST" action="{{ route('staff.checkin', $registration) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center justify-center border border-transparent bg-indigo-600 text-white rounded-lg px-3 py-2 text-sm font-semibold hover:bg-indigo-700 transition">
                                                    Check-in
                                                </button>
                                            </form>
                                        @endif
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"
                                        class="px-4 py-8 text-center text-sm text-gray-500 border-t border-gray-100">
                                        Keine Registrierungen gefunden.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    {{-- QR-SCANNER JAVASCRIPT --}}
    <script>
    let html5QrCode = null;
    let scannerRunning = false;
    let lastScanned = null;
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function toggleScanner() {
        const panel = document.getElementById('qr-scanner-panel');
        const isHidden = panel.classList.contains('hidden');
        if (isHidden) {
            panel.classList.remove('hidden');
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            initCameraList();
        } else {
            stopScanner();
            panel.classList.add('hidden');
        }
    }

    async function initCameraList() {
        try {
            const cameras = await Html5Qrcode.getCameras();
            const select = document.getElementById('camera-select');
            select.innerHTML = '';
            if (!cameras || cameras.length === 0) {
                select.innerHTML = '<option value="">Keine Kamera gefunden</option>';
                showStatus('Keine Kamera gefunden. Bitte Kamerazugriff erlauben.', 'error');
                return;
            }
            cameras.forEach((cam, i) => {
                const opt = document.createElement('option');
                opt.value = cam.id;
                opt.text = cam.label || `Kamera ${i + 1}`;
                select.appendChild(opt);
            });
            const backCam = cameras.find(c => /back|rear|environment/i.test(c.label));
            if (backCam) select.value = backCam.id;
            startScanner();
        } catch (err) {
            showStatus('Kamerazugriff verweigert. Bitte in den Browser-Einstellungen erlauben.', 'error');
        }
    }

    async function startScanner() {
        const select = document.getElementById('camera-select');
        const cameraId = select.value;
        if (!cameraId) { showStatus('Bitte zuerst eine Kamera auswählen.', 'error'); return; }
        if (scannerRunning) await stopScanner();
        html5QrCode = new Html5Qrcode('qr-reader');
        try {
            await html5QrCode.start(
                cameraId,
                { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 },
                onScanSuccess,
                onScanError
            );
            scannerRunning = true;
            showStatus('Scanner aktiv – QR-Code vor die Kamera halten.', 'info');
        } catch (err) {
            showStatus('Kamera konnte nicht gestartet werden: ' + err, 'error');
        }
    }

    async function stopScanner() {
        if (html5QrCode && scannerRunning) {
            try { await html5QrCode.stop(); } catch (_) {}
            scannerRunning = false;
        }
        clearStatus();
    }

    async function onScanSuccess(decodedText) {
        console.log('RAW SCAN:', decodedText);
        if (decodedText === lastScanned) return;
        lastScanned = decodedText;
        setTimeout(() => { lastScanned = null; }, 3000);
        if (html5QrCode && scannerRunning) { try { html5QrCode.pause(); } catch (_) {} }
        showStatus('QR-Code erkannt – wird geprüft …', 'info');
        let token = decodedText.trim();
        const urlMatch = token.match(/\/verify\/([^/?#]+)/);
        if (urlMatch) token = urlMatch[1];
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        let response;
        try {
            response = await fetch(`/verify/${token}/checkin`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken ?? '', 'Accept': 'application/json', 'Content-Type': 'application/json' },
            });
        } catch (networkErr) {
            showStatus('Verbindungsfehler – ist der Server erreichbar? (' + networkErr.message + ')', 'error');
            setTimeout(() => { try { html5QrCode.resume(); } catch (_) {} }, 3000);
            return;
        }
        if (response.status === 419) { showStatus('Sitzung abgelaufen – Seite wird neu geladen …', 'info'); setTimeout(() => window.location.reload(), 1500); return; }
        if (response.status === 404) { showStatus('⚠ QR-Code nicht erkannt – ungültiger oder abgelaufener Code.', 'error'); setTimeout(() => { try { html5QrCode.resume(); } catch (_) {} }, 3000); return; }
        let data = {};
        try { data = await response.json(); } catch (_) { showStatus('Unerwartete Server-Antwort.', 'error'); return; }
        if (response.ok && data.success) {
            showStatus('✓ ' + data.message, 'success');
            setTimeout(() => window.location.reload(), 1800);
        } else {
            showStatus('⚠ ' + (data.message ?? 'Unbekannter Fehler'), 'error');
            setTimeout(() => { try { html5QrCode.resume(); } catch (_) {} }, 3000);
        }
    }

    function onScanError() {}

    function showStatus(msg, type) {
        const el = document.getElementById('qr-status');
        el.textContent = msg;
        el.className = 'mt-3 rounded-lg px-4 py-3 text-sm font-medium';
        const styles = { info: 'bg-blue-50 border border-blue-200 text-blue-800', success: 'bg-green-50 border border-green-200 text-green-800', error: 'bg-red-50 border border-red-200 text-red-800' };
        el.classList.add(...(styles[type] ?? styles.info).split(' '));
        el.classList.remove('hidden');
    }

    function clearStatus() {
        const el = document.getElementById('qr-status');
        el.classList.add('hidden');
        el.textContent = '';
    }
    </script>

</body>
</html>
````

## File: app/Http/Controllers/RegistrationController.php
````php
<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Checkin;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrationController extends Controller
{
    public function create(Request $request)
    {
        $request->session()->put('register_form_started_at', now()->timestamp);
        return view('register');
    }

    public function store(Request $request)
    {
        // Honeypot-Spam-Schutz
        if (filled($request->input('website')) || filled($request->input('fax_number'))) {
            \Log::warning('Spam blockiert: Honeypot-Feld ausgefüllt', [
                'ip' => $request->ip(),
                'ua' => $request->userAgent(),
            ]);
            throw ValidationException::withMessages([
                'first_name' => 'Die Registrierung konnte nicht verarbeitet werden. Bitte versuche es erneut.',
            ]);
        }

        $formStartedAt = (int) $request->session()->get('register_form_started_at', 0);
        $secondsTaken  = now()->timestamp - $formStartedAt;
        if ($formStartedAt > 0 && $secondsTaken < 3) {
            \Log::warning('Spam blockiert: Formular zu schnell abgesendet', [
                'ip'           => $request->ip(),
                'ua'           => $request->userAgent(),
                'secondstaken' => $secondsTaken,
            ]);
            throw ValidationException::withMessages([
                'first_name' => 'Die Registrierung konnte nicht verarbeitet werden. Bitte versuche es erneut.',
            ]);
        }

        $validated = $request->validate([
            'first_name'          => 'required|string|max:255',
            'last_name'           => 'required|string|max:255',
            'birth_date'          => 'required|date|after_or_equal:1900-01-01|before_or_equal:today',
            'email'              => 'nullable|email|max:255',
            'member_type'         => 'required|in:member,guest',
            'member_number'       => [
                'required_if:member_type,member',
                'nullable',
                'string',
                // ✅ NEU: Format XX-XXXXX erzwingen
                'regex:/^\d{2}-\d{5}$/',
            ],
            'waiver_accepted'     => 'required|accepted',
            'rules_accepted'     => 'required|accepted',
            'supervision_confirmed' => 'nullable|boolean',
            // Honeypot-Felder
            'hp_time'             => 'required|integer',
            'website'            => 'nullable|max:0',
            'fax_number'          => 'nullable|max:0',
        ], [
            'birth_date.required'   => 'Das Geburtsdatum ist erforderlich, um doppelte Registrierungen zu vermeiden.',
            // ✅ NEU: Fehlermeldung für Format-Validierung
            'member_number.regex'   => 'Die Mitgliedsnummer muss im Format XX-XXXXX eingegeben werden (z.B. 12-34567).',
        ]);

        // Freitextfelder bereinigen
        $validated['first_name']    = strip_tags($validated['first_name']);
        $validated['last_name']     = strip_tags($validated['last_name']);
        $validated['member_number'] = isset($validated['member_number'])
            ? strip_tags($validated['member_number'])
            : null;

        $birthDate       = Carbon::parse($validated['birth_date']);
        $age             = $birthDate->age;
        $needsSupervision   = $age < 14;
        $needsParentConsent = $age >= 14 && $age < 18;

        if ($needsSupervision && !$request->boolean('supervision_confirmed')) {
            throw ValidationException::withMessages([
                'supervision_confirmed' => 'Für Kinder unter 14 Jahren muss bestätigt werden, dass Klettern nur unter Aufsicht erfolgt.',
            ]);
        }

        // Mitglieds-Verifikation gegen Mitgliederliste
        $member = null;
        if ($validated['member_type'] === 'member') {
            $member = DB::table('members')
                ->where('member_number', $validated['member_number'])
                ->first();
        
            $lastNameInput = strtolower(trim($validated['last_name']));
            $birthInput    = Carbon::parse($validated['birth_date'])->toDateString();
        
            if ($member) {
                $lastNameDb = strtolower(trim($member->last_name ?? ''));
                $birthDb    = $member->birth_date
                    ? Carbon::parse($member->birth_date)->toDateString()
                    : null;
        
                if ($lastNameInput !== $lastNameDb || $birthInput !== $birthDb) {
                    throw ValidationException::withMessages([
                        'member_number' => 'Die Mitgliedsnummer stimmt nicht mit den angegebenen Daten (Nachname + Geburtsdatum) überein. Bitte prüfen!',
                    ]);
                }
            } else {
                $nameBirthMatch = DB::table('members')
                    ->whereRaw('LOWER(last_name) = ?', [$lastNameInput])
                    ->whereDate('birth_date', $birthInput)
                    ->exists();
        
                if ($nameBirthMatch) {
                    throw ValidationException::withMessages([
                        'member_number' => 'Die Mitgliedsnummer stimmt nicht mit den angegebenen Daten (Nachname + Geburtsdatum) überein. Bitte prüfen!',
                    ]);
                }
            }
        }

        // Duplikat-Suche
        $query = Registration::query();
        if ($validated['member_type'] === 'member' && !empty($validated['member_number'])) {
            $query->where('member_number', $validated['member_number']);
        } else {
            $query->whereRaw('LOWER(last_name) = ?', [strtolower(trim($validated['last_name']))])
                  ->where('birth_date', $validated['birth_date']);
        }
        $existingReg = $query->first();

        // FIX 1: Gast-Upgrade nach Name/Birthdate suchen, falls Mitglied kein Treffer
        if (!$existingReg && $validated['member_type'] === 'member') {
            $existingReg = Registration::whereRaw('LOWER(last_name) = ?', [strtolower(trim($validated['last_name']))])
                ->where('birth_date', $validated['birth_date'])
                ->where('member_type', 'guest')
                ->first();
        }

        // Zugangsstatus bestimmen
        $accessStatus = 'red';
        $accessReason = 'Unbekannt';
        $paymentStatus = 'paid';

        if ($validated['member_type'] === 'guest') {
            $validated['member_number'] = null;
            if (!$existingReg) {
                $accessStatus = 'blue';
                $accessReason = 'Schnupperklettern';
            } else {
                $accessStatus = 'orange';
                $accessReason = 'Kulanz: ' . $existingReg->manual_exception_reason;
            }
        } elseif ($validated['member_type'] === 'member') {
            if (!$member) {
                $accessStatus  = 'orange';
                $accessReason  = 'Mitglied noch unbestätigt / nicht in Datenbank';
                $paymentStatus = 'overdue';
            } elseif (($member->membership_status ?? null) !== 'active') {
                $accessStatus  = 'red';
                $accessReason  = 'Mitgliedschaft inaktiv';
                $paymentStatus = 'overdue';
            } elseif (($member->payment_status ?? null) === 'open') {
                $accessStatus  = 'orange';
                $accessReason  = 'Beitrag offen';
                $paymentStatus = 'overdue';
            } else {
                $accessStatus = 'green';
                $accessReason = 'Mitgliedschaft aktiv & bezahlt';
            }
        }

        // Aufsicht-Logik
        if ($needsSupervision) {
            if ($validated['member_type'] === 'guest') {
                $accessReason .= ' · Unter 14 – Aufsicht erforderlich';
            } else {
                if ($request->boolean('supervision_confirmed')) {
                    $accessStatus = 'green';
                    $accessReason = 'Unter 14 – Aufsicht erforderlich';
                } else {
                    $accessStatus = 'orange';
                    $accessReason = 'Unter 14 – Aufsicht erforderlich';
                }
            }
        }

        if ($needsParentConsent) {
            if ($validated['member_type'] === 'guest') {
                $accessReason .= ' · Jugendlicher (14–17)';
            } else {
                $accessStatus = 'green';
                $accessReason = 'Jugendlicher (14–17)';
            }
        }

        // GAST-BLOCK / Upgrade-Logik
        if ($existingReg) {
            // FIX 2: Upgrade-Logik Gast → Mitglied erlauben
            $isUpgrade = $existingReg->member_type === 'guest' && $validated['member_type'] === 'member';

            if (!$isUpgrade) {
                if ($existingReg->member_type === 'guest') {
                    if (($existingReg->trial_visits_count ?? 0) >= 2) {
                        throw ValidationException::withMessages([
                            'first_name' => 'Du hast das Schnupper-Limit bereits vollständig ausgeschöpft. Eine weitere Registrierung als Gast ist nicht möglich.',
                        ]);
                    }
                    $hasKulanz = $existingReg->manual_exception_until &&
                                 $existingReg->manual_exception_until->isFuture();
                    if (!$hasKulanz) {
                        throw ValidationException::withMessages([
                            'first_name' => 'Du bist bereits als Schnuppergast registriert. Ein zweites Mal ist nur nach Absprache mit dem Hallendienst möglich.',
                        ]);
                    }
                } else {
                    return redirect('verify/' . $existingReg->qr_token)
                        ->with('success', 'Du warst bereits registriert! Hier ist dein aktueller Status.');
                }
            }

            // FIX 3: member_type & member_number mitschreiben
            $existingReg->update([
                'member_type'              => $validated['member_type'],
                'member_number'            => $validated['member_number'] ?? null,
                'waiver_accepted'          => true,
                'birth_date'               => $validated['birth_date'],
                'email'                   => $validated['email'] ?? null,
                'access_status'            => $accessStatus,
                'access_reason'            => $accessReason,
                'needs_supervision'        => $needsSupervision,
                'needs_parent_consent'      => $needsParentConsent,
                'parent_consent_received'   => $needsParentConsent ? $existingReg->parent_consent_received : false,
                'parent_consent_received_at' => $needsParentConsent ? $existingReg->parent_consent_received_at : null,
                'supervision_confirmed'    => $needsSupervision
                    ? $request->boolean('supervision_confirmed')
                    : false,
            ]);
            $registration = $existingReg;
        } else {
            $registration = Registration::create([
                'first_name'               => $validated['first_name'],
                'last_name'                => $validated['last_name'],
                'birth_date'               => $validated['birth_date'],
                'email'                   => $validated['email'] ?? null,
                'member_type'              => $validated['member_type'],
                'member_number'            => $validated['member_number'] ?? null,
                'waiver_accepted'          => true,
                'waiver_version'           => 'v1',
                'payment_status'           => $paymentStatus,
                'access_status'            => $accessStatus,
                'access_reason'            => $accessReason,
                'trial_visits_count'        => 0,
                'needs_supervision'        => $needsSupervision,
                'needs_parent_consent'      => $needsParentConsent,
                'parent_consent_received'   => false,
                'parent_consent_received_at' => null,
                'supervision_confirmed'    => $needsSupervision
                    ? $request->boolean('supervision_confirmed')
                    : false,
                'qr_token'                 => (string) Str::uuid(),
            ]);
        }

        return redirect('verify/' . $registration->qr_token)
            ->with('success', 'Registrierung erfolgreich!');
    }

    public function verify(string $token)
    {
        $registration = Registration::with('currentCheckin')
            ->where('qr_token', $token)
            ->firstOrFail();

        return view('verify', compact('registration'));
    }

    public function checkin(Request $request, string $token)
    {
        $registration = Registration::with('currentCheckin')
            ->where('qr_token', $token)
            ->first();

        if (!$registration) {
            $msg = 'QR-Code ungültig oder abgelaufen.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 404)
                : abort(404, $msg);
        }

        $hasActiveKulanz = $registration->manual_exception_until &&
                           $registration->manual_exception_until->isFuture();
        $needsKulanz = in_array($registration->access_status, ['red', 'orange']) && !$hasActiveKulanz;

        if ($needsKulanz) {
            $statusText = strtoupper($registration->access_status);
            $message    = "Check-in blockiert! Status ist {$statusText}. Kulanz erforderlich: "
                        . ($registration->access_reason ?? 'Unbekannt') . '.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
            return redirect()->route('verify', $registration->qr_token)->withErrors($message);
        }

        if ($registration->currentCheckin) {
            $message = $registration->first_name . ' ' . $registration->last_name
                . ' ist bereits seit '
                . $registration->currentCheckin->checked_in_at->format('H:i')
                . ' Uhr eingecheckt.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return redirect()->route('verify', $registration->qr_token)->withErrors($message);
        }

        // Nur green/blue dürfen direkt einchecken
        if (!in_array($registration->access_status, ['green', 'blue'])) {
            $message = $registration->access_status === 'red'
                ? 'Kein Zutritt erlaubt.'
                : 'Zutritt erfordert manuelle Freigabe durch den Hallendienst.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
            return redirect()->back()->withErrors('Check-in verweigert: ' . $message);
        }

        Checkin::create([
            'registration_id' => $registration->id,
            'checked_in_at'     => now(),
        ]);
        $registration->increment('trial_visits_count');

        $message = $registration->first_name . ' ' . $registration->last_name
                 . ' wurde erfolgreich eingecheckt.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }
        return redirect()->route('verify', $registration->qr_token)->with('success', $message);
    }
}
````
