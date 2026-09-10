<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_books(): void
    {
        Book::factory()->count(3)->create();

        $this->getJson('/api/books')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'isbn', 'author_id', 'author', 'genres'],
                ],
            ]);
    }

    public function test_can_create_book(): void
    {
        $author = Author::factory()->create();
        $genres = Genre::factory()->count(2)->create();

        $payload = [
            'author_id' => $author->id,
            'title' => 'Clean Code',
            'isbn' => '9780132350884',
            'published_at' => '2008-08-01',
            'genre_ids' => $genres->pluck('id')->all(),
        ];

        $this->postJson('/api/books', $payload)
            ->assertCreated()
            ->assertJsonPath('data.title', 'Clean Code')
            ->assertJsonPath('data.author.id', $author->id);

        $this->assertDatabaseHas('books', [
            'title' => 'Clean Code',
            'isbn' => '9780132350884',
        ]);
    }

    public function test_can_show_book(): void
    {
        $book = Book::factory()->create();

        $this->getJson("/api/books/{$book->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $book->id)
            ->assertJsonPath('data.title', $book->title);
    }

    public function test_can_update_book(): void
    {
        $book = Book::factory()->create(['title' => 'Old Title']);

        $this->putJson("/api/books/{$book->id}", ['title' => 'New Title'])
            ->assertOk()
            ->assertJsonPath('data.title', 'New Title');

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'New Title',
        ]);
    }

    public function test_can_delete_book(): void
    {
        $book = Book::factory()->create();

        $this->deleteJson("/api/books/{$book->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_store_book_validates_required_fields(): void
    {
        $this->postJson('/api/books', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['author_id', 'title', 'isbn']);
    }
}