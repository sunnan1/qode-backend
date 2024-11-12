<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory(1)->create();
        $batchSize = 1000;
        $totalPosts = 200000;
        $faker = Faker::create();

        for ($i = 0; $i < $totalPosts / $batchSize; $i++) {
            $posts = [];
            for ($j = 0; $j < $batchSize; $j++) {
                $posts[] = [
                    'title' => $faker->sentence(),
                    'excerpt' => $faker->text(100),
                    'description' => $faker->paragraph(3, true),
                    'image' => '',
                    'keywords' => implode(',', $faker->words(5)),
                    'meta_title' => $faker->sentence,
                    'meta_description' => $faker->text(150),
                    'published_at' => now(),
                    'author_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Insert posts in bulk to the database
            Post::insert($posts);
            // Fetch the newly inserted posts and bulk index them in Elasticsearch
            $recentPosts = Post::latest()->take($batchSize)->get();
            $recentPosts->searchable();  // Bulk index in Elasticsearch

            echo "Inserted and indexed " . (($i + 1) * $batchSize) . " posts...\n";
        }
    }
}
