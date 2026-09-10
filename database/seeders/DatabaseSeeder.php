<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $genres = Genre::factory(12)->create();
        $authors = Author::factory(20)->create();
        $members = Member::factory(30)->create();

        $books = collect();

        foreach ($authors as $author) {
            $authorBooks = Book::factory(5)->for($author)->create();

            foreach ($authorBooks as $book) {
                $book->genres()->attach(
                    $genres->random(fake()->numberBetween(1, 3))->pluck('id')
                );
            }

            $books = $books->merge($authorBooks);
        }

        // Historial: varios préstamos ya devueltos (pueden repetir libro)
        Loan::factory(80)
            ->returned()
            ->recycle($members)
            ->recycle($books)
            ->create();

        // Activos: como máximo uno por libro (libros distintos)
        foreach ($books->random(40) as $book) {
            Loan::factory()
                ->active()
                ->for($book)
                ->for($members->random())
                ->create();
        }
    }
}