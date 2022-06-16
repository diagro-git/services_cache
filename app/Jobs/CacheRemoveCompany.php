<?php

namespace App\Jobs;

use App\Jobs\Traits\RemoveReferences;
use App\Traits\CacheResourceHelpers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class CacheRemoveCompany implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, CacheResourceHelpers, RemoveReferences;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private int $company_id
    )
    {
        $this->onQueue('remove_company');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Cache::tags(['company_' . $this->company_id])->flush();
        $this->removeReferences('company_' . $this->company_id);
    }
}
