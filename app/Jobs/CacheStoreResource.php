<?php

namespace App\Jobs;

use App\Traits\CacheResourceHelpers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class CacheStoreResource implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, CacheResourceHelpers;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private string $key,
        private array $tags,
        private array $data,
        private array $usedResources
    )
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //store the data in cache first
        Cache::tags($this->tags)->put($this->key, $this->data);
        $value = ['key' => $this->key, 'tags' => $this->tags];

        //for each used resource, link the resource with tags and key.
        foreach($this->usedResources as $usedResource) {
            $key = self::resourceKey($usedResource);
            $tags = [self::resourceTag($usedResource)];
            $cached = Cache::tags($tags)->get('tk');

            if(empty($cached)) {
                $cached = [$key => [$value]];
            } elseif(is_array($cached)) {
                if(isset($cached[$key]) && is_array($cached[$key])) {
                    $cached[$key][] = $value;
                } else {
                    $cached[$key] = [$value];
                }
            }

            //tk = tags and keys
            Cache::tags($tags)->put('tk', $cached);
        }
    }
}
