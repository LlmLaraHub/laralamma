<?php

namespace Tests\Feature;

use App\Domains\EmailParser\MailDto;
use App\Domains\Outputs\EmailReplyOutput;
use App\Domains\Outputs\OutputTypeEnum;
use App\Models\Output;
use Facades\App\Domains\EmailParser\EmailClient;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class EmailReplyOutputTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_batch(): void
    {

        Bus::fake();

        $output = Output::factory()
            ->emailSecrets()
            ->create([
                'type' => OutputTypeEnum::EmailReplyOutput,
            ]);

        EmailClient::shouldReceive('handle')
            ->once()->andReturn([
                MailDto::from([
                    'to' => 'info+12345@llmassistant.io',
                    'from' => 'foo@var.com',
                    'subject' => 'This is it',
                    'header' => 'This is header',
                    'body' => 'This is the body',
                ]),
                MailDto::from([
                    'to' => 'info+12345@llmassistant.io',
                    'from' => 'foo@var.com',
                    'subject' => 'This is it',
                    'header' => 'This is header',
                    'body' => 'This is the body',
                ]),
            ]);

        (new EmailReplyOutput())->handle($output);

        Bus::assertBatchCount(1);
    }
}
