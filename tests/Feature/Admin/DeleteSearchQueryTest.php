<?php

use App\Models\SearchQuery;
use App\Models\SearchResult;
use App\Models\User;
use App\Services\SearchCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

function makeQuery(array $attributes = []): SearchQuery
{
    return SearchQuery::create(array_merge([
        'user_id' => null,
        'query' => '919782855655',
        'type' => 'phone',
        'ip_address' => '127.0.0.1',
    ], $attributes));
}

function makeCached(array $attributes = [], array $results = ['data' => ['ok' => true]]): SearchQuery
{
    $query = makeQuery($attributes);

    SearchResult::create([
        'search_query_id' => $query->id,
        'results' => $results,
    ]);

    return $query->refresh();
}

test('guests cannot delete a search query', function () {
    $query = makeQuery();

    $this->deleteJson("/api/admin/queries/{$query->public_id}")
        ->assertUnauthorized();

    expect(SearchQuery::find($query->id))->not->toBeNull();
});

test('non-admin users cannot delete a search query', function () {
    $query = makeQuery();
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/admin/queries/{$query->public_id}")
        ->assertForbidden();

    expect(SearchQuery::find($query->id))->not->toBeNull();
});

test('deleting an unknown query returns 404', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin, 'sanctum')
        ->deleteJson('/api/admin/queries/'.\Illuminate\Support\Str::uuid())
        ->assertNotFound();
});

test('deleting a query cascade-deletes its cached result', function () {
    $query = makeCached();
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/admin/queries/{$query->public_id}")
        ->assertOk()
        ->assertJsonPath('deleted_count', 1);

    $this->assertDatabaseMissing('search_queries', ['id' => $query->id]);
    $this->assertDatabaseMissing('search_results', ['search_query_id' => $query->id]);
});

test('deleting a query also purges every sibling for the same value', function () {
    $target = makeCached();
    $sibling = makeCached(['query' => '+91 97828 55655']);   // same value, different formatting
    $otherValue = makeCached(['query' => '919999999999']);
    $otherType = makeCached(['query' => '919782855655', 'type' => 'email']);

    expect($sibling->query_hash)->toBe($target->query_hash);

    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/admin/queries/{$target->public_id}")
        ->assertOk()
        ->assertJsonPath('deleted_count', 2);

    $this->assertDatabaseMissing('search_queries', ['id' => $target->id]);
    $this->assertDatabaseMissing('search_queries', ['id' => $sibling->id]);
    $this->assertDatabaseMissing('search_results', ['search_query_id' => $sibling->id]);

    $this->assertDatabaseHas('search_queries', ['id' => $otherValue->id]);
    $this->assertDatabaseHas('search_queries', ['id' => $otherType->id]);
});

test('rows with a null query hash do not purge each other', function () {
    // where('query_hash', null) compiles to IS NULL, which would otherwise
    // match and delete every hashless row of the same type.
    $target = makeCached();
    $sibling = makeCached();

    DB::table('search_queries')
        ->whereIn('id', [$target->id, $sibling->id])
        ->update(['query_hash' => null]);

    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/admin/queries/{$target->public_id}")
        ->assertOk()
        ->assertJsonPath('deleted_count', 1);

    $this->assertDatabaseMissing('search_queries', ['id' => $target->id]);
    $this->assertDatabaseHas('search_queries', ['id' => $sibling->id]);
});

test('the cache lookup misses after the query is deleted', function () {
    $query = makeCached();

    expect(SearchCache::findHit('phone', '919782855655'))->not->toBeNull();

    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/admin/queries/{$query->public_id}")
        ->assertOk();

    // findHit() is the single gate every cached search type goes through, so a
    // null here means the next search performs a fresh upstream lookup.
    expect(SearchCache::findHit('phone', '919782855655'))->toBeNull();
});

test('a phone search is served from cache until an admin deletes the query', function () {
    makeCached(results: ['tcData' => ['name' => 'Cached Person']]);

    $user = User::factory()->create(['is_admin' => false, 'credits' => 100]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/tel?number=919782855655')
        ->assertOk()
        ->assertJsonPath('cached', true)
        ->assertJsonPath('tcData.name', 'Cached Person');

    // The cache hit must not have logged a second row or charged credits.
    expect(SearchQuery::where('type', 'phone')->count())->toBe(1);
    expect((float) $user->fresh()->credits)->toBe(100.0);

    $target = SearchQuery::where('type', 'phone')->firstOrFail();
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/admin/queries/{$target->public_id}")
        ->assertOk();

    expect(SearchCache::findHit('phone', '919782855655'))->toBeNull();
});

test('deleting a query writes an admin audit log entry', function () {
    $query = makeCached();
    $admin = User::factory()->create(['is_admin' => true]);

    Log::shouldReceive('channel')->with('stack')->andReturnSelf();
    Log::shouldReceive('info')
        ->once()
        ->withArgs(function ($message, $context) use ($query) {
            return str_contains($message, 'SEARCH_QUERY_DELETED')
                && $context['target_id'] === $query->id
                && $context['deleted_count'] === 1;
        });

    $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/admin/queries/{$query->public_id}")
        ->assertOk();
});

test('the audit log truncates the query value', function () {
    $query = makeCached(['query' => str_repeat('9', 5000), 'type' => 'corporate']);
    $admin = User::factory()->create(['is_admin' => true]);

    Log::shouldReceive('channel')->with('stack')->andReturnSelf();
    Log::shouldReceive('info')
        ->once()
        ->withArgs(fn ($message, $context) => strlen($context['query']) === 203  // 200 + the ellipsis
            && str_ends_with($context['query'], '...'));

    $this->actingAs($admin, 'sanctum')
        ->deleteJson("/api/admin/queries/{$query->public_id}")
        ->assertOk();
});

test('the impact endpoint reports how many entries a delete would remove', function () {
    $target = makeCached();
    makeCached(['query' => '+91 97828 55655']);
    makeCached(['query' => '919999999999']);

    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin, 'sanctum')
        ->getJson("/api/admin/queries/{$target->public_id}/impact")
        ->assertOk()
        ->assertJsonPath('total_count', 2)
        ->assertJsonPath('sibling_count', 1)
        ->assertJsonPath('case_count', 0);
});
