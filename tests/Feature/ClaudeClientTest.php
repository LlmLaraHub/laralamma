<?php

namespace Tests\Feature;

use App\LlmDriver\ClaudeClient;
use App\LlmDriver\Requests\MessageInDto;
use App\LlmDriver\Responses\CompletionResponse;
use App\LlmDriver\Responses\EmbeddingsResponseDto;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ClaudeClientTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_embeddings(): void
    {

        $this->markTestSkipped('@TODO: Requires another server');

        $client = new ClaudeClient();

        $results = $client->embedData('test');

        $this->assertInstanceOf(EmbeddingsResponseDto::class, $results);

    }

    public function test_completion(): void
    {
        $client = new ClaudeClient();

        $data = get_fixture('claude_completion.json');

        Http::fake([
            'api.anthropic.com/*' => Http::response($data, 200),
        ]);

        $results = $client->completion('test');

        $this->assertInstanceOf(CompletionResponse::class, $results);

    }

    public function test_chat(): void
    {
        $client = new ClaudeClient();

        $data = get_fixture('claude_completion.json');

        Http::fake([
            'api.anthropic.com/*' => Http::response($data, 200),
        ]);

        $results = $client->chat([
            MessageInDto::from([
                'content' => 'test',
                'role' => 'system',
            ]),
            MessageInDto::from([
                'content' => 'test',
                'role' => 'user',
            ]),
        ]);

        $this->assertInstanceOf(CompletionResponse::class, $results);

        Http::assertSent(function ($request) {
            $message1 = $request->data()['messages'][0]['role'];
            $message2 = $request->data()['messages'][1]['role'];

            return $message2 === 'assistant' &&
                $message1 === 'user';
        });

    }

    public function test_chat_with_multiple_assistant_messages(): void
    {
        $client = new ClaudeClient();

        $data = get_fixture('claude_completion.json');

        Http::fake([
            'api.anthropic.com/*' => Http::response($data, 200),
        ]);

        $results = $client->chat([
            MessageInDto::from([
                'content' => 'test 3',
                'role' => 'assistant',
            ]),
            MessageInDto::from([
                'content' => 'test 2',
                'role' => 'assistant',
            ]),
            MessageInDto::from([
                'content' => 'test 1',
                'role' => 'assistant',
            ]),
            MessageInDto::from([
                'content' => 'test',
                'role' => 'user',
            ]),
        ]);

        $this->assertInstanceOf(CompletionResponse::class, $results);

        Http::assertSent(function ($request) {
            $message1 = $request->data()['messages'][0]['role'];
            $message2 = $request->data()['messages'][1]['role'];
            $message3 = $request->data()['messages'][1]['role'];

            return $message2 === 'assistant' &&
                $message1 === 'user' && $message3 === 'assistant';
        });

    }
}