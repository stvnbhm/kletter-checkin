<?php

use App\Models\Checkin;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Str;

test('admin can save registration notes and keep list filters in redirect', function () {
    $admin = User::factory()->create();
    $admin->is_admin = true;
    $admin->save();

    $registration = Registration::create([
        'first_name' => 'Anna',
        'last_name' => 'Kurs',
        'member_type' => 'guest',
        'access_status' => 'green',
        'qr_token' => (string) Str::uuid(),
        'trial_visits_count' => 0,
    ]);

    $response = $this->actingAs($admin)->patch(
        route('admin.registrations.notes', $registration),
        [
            'q' => 'KursA',
            'status' => 'guest',
            'notes' => 'Kurs Montag 18:00',
        ]
    );

    $response->assertRedirect(route('admin.index', ['q' => 'KursA', 'status' => 'guest'], absolute: false));
    expect($registration->fresh()->notes)->toBe('Kurs Montag 18:00');
});

test('staff check-in preserves search query in redirect', function () {
    $user = User::factory()->create();

    $registration = Registration::create([
        'first_name' => 'Ben',
        'last_name' => 'Teilnehmer',
        'member_type' => 'guest',
        'access_status' => 'green',
        'qr_token' => (string) Str::uuid(),
        'trial_visits_count' => 0,
    ]);

    $response = $this->actingAs($user)->post(
        route('staff.checkin', $registration),
        ['q' => 'KursMontag']
    );

    $response->assertRedirect(route('staff', ['q' => 'KursMontag'], absolute: false));
    expect(Checkin::where('registration_id', $registration->id)->count())->toBe(1);
});

test('staff list finds registrations by notes', function () {
    $user = User::factory()->create();

    Registration::create([
        'first_name' => 'X',
        'last_name' => 'Y',
        'member_type' => 'guest',
        'access_status' => 'green',
        'qr_token' => (string) Str::uuid(),
        'trial_visits_count' => 0,
        'notes' => 'Einsteiger Dienstag',
    ]);

    $response = $this->actingAs($user)->get(route('staff', ['q' => 'Einsteiger']));

    $response->assertOk();
    $response->assertSee('Einsteiger Dienstag');
});
