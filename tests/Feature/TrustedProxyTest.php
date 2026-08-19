<?php

use App\Models\User;
use App\Models\UserIp;

test('the client IP is read from the forwarded chain, not the proxy', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'last_otp_verified_at' => now(),
    ]);

    // Mirrors production: nginx (a trusted private address) forwards the
    // request and appends its own hop to X-Forwarded-For.
    $this->withServerVariables(['REMOTE_ADDR' => '172.18.0.1'])
        ->withHeaders(['X-Forwarded-For' => '203.0.113.45, 172.18.0.1'])
        ->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
        ->assertOk();

    expect(UserIp::where('user_id', $user->id)->value('ip'))->toBe('203.0.113.45');
});

test('a forwarded header from an untrusted peer is ignored', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'last_otp_verified_at' => now(),
    ]);

    // A client reaching the app directly cannot fake its address: its own peer
    // address is public, so it is not in the trusted-proxy list.
    $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.7'])
        ->withHeaders(['X-Forwarded-For' => '1.2.3.4'])
        ->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
        ->assertOk();

    expect(UserIp::where('user_id', $user->id)->value('ip'))->toBe('198.51.100.7');
});
