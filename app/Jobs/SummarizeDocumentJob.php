<?php

namespace App\Jobs;

use App\Domains\Collections\CollectionStatusEnum;
use App\Events\CollectionStatusEvent;
use App\Models\Document;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SummarizeDocumentJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Document $document)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $content = [];
        foreach($this->document->document_chunks as $chunk){
            $content[] = $chunk->content;
        }

        $prompt = <<<EOD
The following content is part of a larger document. I would like you to summarize it so 
I can show a summary view of all the other pages and this ones related to the same document.
Just return the summary, 1-2 lines if possible and no extra surrounding text.
The content to summarize follows:

{$content}
EOD;

        
        CollectionStatusEvent::dispatch(
            $this->document->collection,
            CollectionStatusEnum::PROCESSED
        );
    }
}
