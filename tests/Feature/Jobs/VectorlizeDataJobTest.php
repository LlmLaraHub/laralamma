<?php

namespace Tests\Feature\Jobs;

use App\Jobs\VectorlizeDataJob;
use App\Models\DocumentChunk;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use Tests\TestCase;

class VectorlizeDataJobTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_gets_data(): void
    {
        $embedding = get_fixture('embedding_response.json');

        $dto = \LlmLaraHub\LlmDriver\Responses\EmbeddingsResponseDto::from([
            'embedding' => data_get($embedding, 'data.0.embedding'),
            'token_count' => 1000,
        ]);

        LlmDriverFacade::shouldReceive('driver->embedData')
            ->once()
            ->andReturn($dto);

        $documentChunk = DocumentChunk::factory()
            ->openAi()
            ->create();

        $job = new VectorlizeDataJob($documentChunk);
        $job->handle();

        $this->assertNotEmpty($documentChunk->embedding_3072);
    }

    public function test_middleware()
    {
        $documentChunk = DocumentChunk::factory()
            ->ollama()
            ->create();

        $job = new VectorlizeDataJob($documentChunk);

        $this->assertNotEmpty($job->middleware());

        $documentChunk = DocumentChunk::factory()
            ->openAi()
            ->create();

        $job = new VectorlizeDataJob($documentChunk);

        $this->assertEmpty($job->middleware());
    }
}
