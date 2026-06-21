<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Author;
use App\Models\Book;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
           $author = Author::inRandomOrder()->first();
        if (!$author) {
            $author = Author::factory()->create();
        }
      
        return [
          'title'  => $this->faker->sentence(3),
          'isbn'  => $this->faker->unique()->isbn13(),
          'description'  => $this->faker->paragraph(),
          'author_id' => $author->id,
        //   'author_id' => Author::inRandomOrder()->first()?->id ?? Author::factory,
          'genre'  => $this->faker->randomElement(['Fiction', 'Non-Fiction', 'Sci-Fi', 'Fantasy', 'Mystery', 'Romance']),
          'published_at'  => $this->faker->date,
          'total_copies'  => $this->faker->numberBetween(1, 50),
          'available_copies'  => $this->faker->numberBetween(1, 50),
          'cover_image'  => $this->faker->imageUrl(200, 300, 'books', true, 'Book Cover'),
          'status'  => $this->faker->randomElement(['active', 'inactive']),
          'price'  => $this->faker->randomFloat(2, 5, 200),
        ];
    }
}
