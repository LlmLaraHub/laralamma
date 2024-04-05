<?php

namespace Tests\Feature;

use App\Events\ChatUiUpdateEvent;
use App\LlmDriver\Functions\SummarizeCollection;
use App\LlmDriver\LlmDriverFacade;
use App\LlmDriver\Orchestrate;
use App\LlmDriver\Requests\MessageInDto;
use App\LlmDriver\Responses\FunctionResponse;
use App\Models\Chat;
use App\Models\Collection;
use App\Models\User;
use Facades\App\Domains\Messages\SearchOrSummarizeChatRepo;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class OrchestrateTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_gets_summarize_function(): void
    {
        Event::fake();
        LlmDriverFacade::shouldReceive('driver->functionPromptChat')->once()->andReturn([
            [
                'name' => 'summarize_collection',
                'arguments' => [
                    'TLDR it for me',
                ],
            ],
        ]);

        LlmDriverFacade::shouldReceive('driver->chat')->never();

        SearchOrSummarizeChatRepo::shouldReceive('search')->never();

        $this->instance(
            'summarize_collection',
            Mockery::mock(SummarizeCollection::class, function ($mock) {
                $mock->shouldReceive('handle')
                    ->once()
                    ->andReturn(
                        FunctionResponse::from(['content' => 'This is the summary of the collection'])
                    );
            })
        );

        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $chat = Chat::factory()->create([
            'chatable_id' => $collection->id,
            'chatable_type' => Collection::class,
            'user_id' => $user->id,
        ]);

        $messageDto = MessageInDto::from([
            'content' => 'TLDR it for me',
            'role' => 'user',
        ]);

        $results = (new Orchestrate())->handle([$messageDto], $chat);

        Event::assertDispatched(ChatUiUpdateEvent::class);

        $this->assertEquals($results, 'This is the summary of the collection');

    }
}