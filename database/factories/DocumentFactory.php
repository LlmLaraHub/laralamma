<?php

namespace Database\Factories;

use App\Domains\Documents\StatusEnum;
use App\Domains\Documents\TypesEnum;
use App\Models\Collection;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => TypesEnum::random(),
            'status' => StatusEnum::random(),
            'source_id' => Source::factory(),
            'meta_data' => [],
            'status_summary' => StatusEnum::random(),
            'link' => $this->faker->url(),
            'summary' => $this->faker->text(),
            'subject' => $this->faker->text(),
            'file_path' => $this->faker->url(),
            'document_chunk_count' => $this->faker->numberBetween(1, 10),
            'collection_id' => Collection::factory(),
        ];
    }

    public function pdf(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => TypesEnum::PDF,
            ];
        });
    }

    public function pptx(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => TypesEnum::Pptx,
            ];
        });
    }
}
