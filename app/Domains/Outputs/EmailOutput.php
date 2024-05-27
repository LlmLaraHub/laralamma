<?php

namespace App\Domains\Outputs;

use App\Jobs\SendOutputEmailJob;
use App\Models\Output;

class EmailOutput extends BaseOutput
{
    public function handle(Output $output): array
    {
        SendOutputEmailJob::dispatch($output);

        return [];
    }
}
