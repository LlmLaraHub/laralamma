<?php

namespace App\Domains\Documents;

use App\Helpers\EnumHelperTrait;

enum StatusEnum: string
{
    use EnumHelperTrait;

    case Pending = 'pending';
    case Running = 'running';
    case SummaryBuilding = 'summary_building';
    case Complete = 'complete';
    case Failed = 'failed';
}
