<?php

use App\Models\SearchQuery;
use App\Models\User;

function logQueryFor(User $user, string $value, string $type = 'phone'): SearchQuery
{
    return SearchQuery::create([
        'user_id' => $user->id,
        'query' => $value,
        'type' => $type,
        'ip_address' => '127.0.0.1',
    ]);
}

test('queries can be filtered by user name', function () {
    $alice = User::factory()->create(['name' => 'Alice Kapoor']);
    $bob = User::factory()->create(['name' => 'Bob Mehta']);

    logQueryFor($alice, '919782855655');
    logQueryFor($bob, '919999999999');

    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson('/api/admin/queries?user=alice')
        ->assertOk()
        ->assertJsonPath('total', 1);

    expect($response->json('data.0.query'))->toBe('919782855655');
});

test('the user filter also matches the email address', function () {
    $alice = User::factory()->create([
        'name' => 'Alice Kapoor',
        'email' => 'alice@example.test',
    ]);
    $bob = User::factory()->create(['name' => 'Bob Mehta']);

    logQueryFor($alice, '919782855655');
    logQueryFor($bob, '919999999999');

    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/admin/queries?user=alice@example.test')
        ->assertOk()
        ->assertJsonPath('total', 1);
});

test('the user filter combines with the value and type filters', function () {
    $alice = User::factory()->create(['name' => 'Alice Kapoor']);
    $bob = User::factory()->create(['name' => 'Bob Mehta']);

    logQueryFor($alice, '919782855655', 'phone');
    logQueryFor($alice, 'alice@target.test', 'email');
    logQueryFor($bob, '919782855655', 'phone');

    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/admin/queries?user=Alice&type=phone')
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.query', '919782855655');
});

test('an unmatched user filter returns no rows', function () {
    $alice = User::factory()->create(['name' => 'Alice Kapoor']);
    logQueryFor($alice, '919782855655');

    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin, 'sanctum')
        ->getJson('/api/admin/queries?user=nobody')
        ->assertOk()
        ->assertJsonPath('total', 0);
});
