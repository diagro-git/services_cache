<?php

namespace App\Jobs;

use App\Traits\CacheResourceHelpers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class CacheRemoveResource implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, CacheResourceHelpers;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        private array $resources,
        private ?int $user_id = null,
        private ?int $company_id = null,
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
        foreach($this->resources as $resource) {
            $key = self::resourceKey($resource);
            $tag = self::resourceTag($resource);
            $cache = Cache::tags([$tag]);
            $entries = $cache->get('tk'); //tags and keys

            if (empty($entries) || ! is_array($entries)) continue;

            if ($key == '*') {
                foreach ($entries as $tags_keys) {
                    if (is_array($tags_keys)) {
                        $this->handleTagsKeys($tags_keys);
                    }
                }

                $cache->flush(); //delete references
            } elseif (isset($entries[$key])) {
                $tags_keys = $entries[$key];
                if (is_array($tags_keys)) {
                    $this->handleTagsKeys($tags_keys);
                }

                //remove reference and update cache references
                unset($entries[$key]);
                logger()->debug("entries=" . print_r($entries, true));
                $cache->put('tk', $entries);
            }
        }
    }

    private function handleTagsKeys(array $tags_keys)
    {
        foreach($tags_keys as $tags_key) {
            if(is_array($tags_key) && $this->validateTagKey($tags_key)) {
                Cache::tags($tags_key['tags'])->forget($tags_key['key']);
            }
        }
    }

    private function getCompanyId(array $tags): ?int
    {
        $company_id = null;
        foreach($tags as $tag) {
            if(str_starts_with($tag, 'company_')) {
                $company_id = Arr::last(explode('_', $tag));
            }
        }

        return $company_id;
    }

    private function getUserId(array $tags): ?int
    {
        $user_id = null;
        foreach($tags as $tag) {
            if(str_starts_with($tag, 'user_')) {
                $user_id = Arr::last(explode('_', $tag));
            }
        }

        return $user_id;
    }

    private function validateTagKey(array $tag_key): bool
    {
        $validate = isset($tag_key['tags']) && isset($tag_key['key']);
        if(! empty($this->company_id)) {
            $validate = $this->getCompanyId($tag_key['tags']) === $this->company_id;
        }
        if(! empty($this->user_id)) {
            $validate = $this->getUserId($tag_key['tags']) === $this->user_id;
        }

        return $validate;
    }
}
