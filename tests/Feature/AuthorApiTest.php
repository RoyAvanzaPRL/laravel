<?php

namespace Tests\Feature;

use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_authors(): void
    {
        Author::factory()->count(3)->create();

        $this->getJson('/api/authors')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'created_at', 'updated_at'],
                ],
            ]);
    }

    public function test_can_create_author(): void
    {
        $this->postJson('/api/authors', ['name' => 'Ada Lovelace'])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Ada Lovelace');

        $this->assertDatabaseHas('authors', ['name' => 'Ada Lovelace']);
    }

    public function test_can_show_author(): void
    {
        $author = Author::factory()->create();

        $this->getJson("/api/authors/{$author->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $author->id)
            ->assertJsonPath('data.name', $author->name);
    }

    public function test_can_update_author(): void
    {
        $author = Author::factory()->create(['name' => 'Old Name']);

        $this->putJson("/api/authors/{$author->id}", ['name' => 'New Name'])
            ->assertOk()
            ->assertJsonPath('data.name', 'New Name');

        $this->assertDatabaseHas('authors', [
            'id' => $author->id,
            'name' => 'New Name',
        ]);
    }

    public function test_can_delete_author(): void
    {
        $author = Author::factory()->create();

        $this->deleteJson("/api/authors/{$author->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('authors', ['id' => $author->id]);
    }

    public function test_store_author_requires_name(): void
    {
        $this->postJson('/api/authors', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }
}