<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\withoutVite;

beforeEach(function (): void {
    withoutVite();
});

test('confirm password screen can be rendered', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('password.confirm'));

    $response->assertStatus(200);
});
