<?php

use App\Domains\Collections\CollectionStatusEnum;
use App\Events\ChatUiUpdateEvent;
use App\Events\CollectionStatusEvent;
use App\Models\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use LlmLaraHub\LlmDriver\HasDrivers;
use LlmLaraHub\LlmDriver\Helpers\TrimText;
use SundanceSolutions\LarachainTokenCount\Facades\LarachainTokenCount;

if (! function_exists('put_fixture')) {
    function put_fixture($file_name, $content = [], $json = true)
    {
        if (! File::exists(base_path('tests/fixtures'))) {
            File::makeDirectory(base_path('tests/fixtures'));
        }

        if ($json) {
            $content = json_encode($content, 128);
        }
        File::put(
            base_path(sprintf('tests/fixtures/%s', $file_name)),
            $content
        );

        return true;
    }
}

if (! function_exists('calculate_dynamic_threshold')) {
    function calculate_dynamic_threshold(array $distances, int $percentile = 90): float
    {
        // Sort the distances in ascending order
        sort($distances);

        // Get the total number of distances
        $count = count($distances);

        // Calculate the index for the given percentile
        $index = ceil($count * ($percentile / 100)) - 1;

        // Ensure the index is within the bounds of the array
        if ($index >= $count) {
            $index = $count - 1;
        }

        // Calculate the average of distances up to the percentile index
        $threshold = array_sum(array_slice($distances, 0, $index + 1)) / ($index + 1);

        return $threshold;

    }
}

if (! function_exists('chunk_string')) {
    function chunk_string(string $string, int $maxTokenSize): array
    {
        $tokenCountWithBuffer = token_counter($string) * 1.25; // buffer for the response of the llm

        $chunksToMake = ceil($tokenCountWithBuffer / $maxTokenSize) + 2; //still needs a ton of work

        /**
         * @TDOO remove this ignore and fix
         */
        /** @phpstan-ignore-next-line */
        $chunks = str_split($string, round(strlen($string) / $chunksToMake));

        return $chunks;
    }
}

if (! function_exists('too_large_for_json')) {
    function too_large_for_json(int $count): int
    {
        $size = config('llmdriver.chunking.default_size');
        $totalPotentialSize = $size * $count;

        if ($totalPotentialSize >= 1048576) {
            return ceil($totalPotentialSize / 1048576);
        }

        return 1;
    }
}

if (! function_exists('token_counter')) {
    function token_counter(string $message)
    {
        return LarachainTokenCount::count($message);
    }
}

if (! function_exists('token_counter_v2')) {
    function token_counter_v2(string $text)
    {
        $words = preg_split('/\s+/', $text);
        $wordCount = count($words);
        $spaceCount = preg_match_all('/\s+/', $text, $matches);
        $punctuationCount = preg_match_all('/[.,;:\?\!]/', $text, $matches);

        return $wordCount + $spaceCount + $punctuationCount;
    }
}

if (! function_exists('notify_ui')) {
    function notify_ui(HasDrivers $model, string $message)
    {
        try {
            ChatUiUpdateEvent::dispatch(
                $model->getChatable(),
                $model->getChat(),
                $message
            );
        } catch (\Exception $e) {
            Log::error('Error notifying UI', ['error' => $e->getMessage()]);
        }
    }
}

if (! function_exists('notify_collection_ui')) {
    function notify_collection_ui(Collection $collection, CollectionStatusEnum $status, string $message = '')
    {
        try {
            CollectionStatusEvent::dispatch($collection, $status, $message);
        } catch (\Exception $e) {
            Log::error('Error notifying collection UI', ['error' => $e->getMessage()]);
        }
    }
}

if (! function_exists('remove_ascii')) {
    function remove_ascii($string): string
    {
        return str_replace("\u2019", ' ', preg_replace('/[^\x00-\x7F]+/', '', $string));
    }
}

if (! function_exists('get_fixture')) {
    function get_fixture($file_name, $decode = true)
    {
        $results = File::get(base_path(sprintf(
            'tests/fixtures/%s',
            $file_name
        )));

        if (! $decode) {
            return $results;
        }

        return json_decode($results, true);
    }
}

if (! function_exists('reduce_text_size')) {
    function reduce_text_size(string $text): string
    {
        return (new TrimText())->handle($text);
    }
}

if (! function_exists('driverHelper')) {
    function driverHelper(string $driver, string $key): string
    {
        Log::info("driverHelper: {$driver} {$key}");

        return config("llmdriver.drivers.{$driver}.{$key}");
    }
}

if (! function_exists('get_embedding_size')) {
    function get_embedding_size(string $ebmedding_driver): string
    {
        $embeddingModel = driverHelper($ebmedding_driver, 'models.embedding_model');

        $size = config('llmdriver.embedding_sizes.'.$embeddingModel);

        if ($size) {
            return 'embedding_'.$size;
        }

        return 'embeding_3072';
    }
}
