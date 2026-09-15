<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreBookRequest;
use App\Http\Requests\Api\UpdateBookRequest;
use App\Models\Book;
use App\Http\Resources\BookResource;
use App\Http\Requests\Api\IndexBookRequest;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexBookRequest $request)
    {

        $sort = $request->validated('sort', 'title');
        $direction = $request->validated('direction', 'asc');
        
        $books = Book::query()
            ->with(['author', 'genres'])
            ->orderBy($sort, $direction)
            ->paginate($request->integer('per_page', 15));
        return BookResource::collection($books);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request)
    {
        $data = $request->validated();

        $book = Book::create($data);

        if (! empty($data['genre_ids'])) {
            $book->genres()->sync($data['genre_ids']);
        }

        $book->load(['author', 'genres']);
        
        return (new BookResource($book))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        $book->load(['author', 'genres']);

        return new BookResource($book);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        $data = $request->validated();

        $book->update($data);

        if (array_key_exists('genre_ids', $data)) {
            $book->genres()->sync($data['genre_ids'] ?? []);
        }

        $book->load(['author', 'genres']);

        return new BookResource($book);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        $book->delete();
        
        return response()->json(null, 204);
    }
}
