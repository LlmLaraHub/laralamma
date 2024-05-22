<?php

namespace App\Http\Controllers;

use App\Domains\Outputs\OutputTypeEnum;
use App\Models\Output;
use Facades\LlmLaraHub\LlmDriver\NonFunctionSearchOrSummarize;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\Responses\NonFunctionResponseDto;

class ApiOutputController extends OutputController
{
    protected OutputTypeEnum $outputTypeEnum = OutputTypeEnum::ApiOutput;

    protected string $edit_path = 'Outputs/ApiOutput/Edit';

    protected string $show_path = 'Outputs/ApiOutput/Show';

    protected string $create_path = 'Outputs/ApiOutput/Create';

    public function api(Output $output)
    {
        $validate = request()->validate([
            'messages' => ['array', 'required'],
        ]);

        Log::info('[LaraChain] - Message Coming in', [
            'message' => $validate['messages']]
        );

        $input = data_get($validate, 'messages.0.content');

        /** @var NonFunctionResponseDto $results */
        $results = NonFunctionSearchOrSummarize::handle($input, $output->collection);

        return response()->json([
            'choices' => [
                [
                    'message' => [
                        'content' => $results->response,
                    ],
                ],
            ],
        ]);
    }
}
