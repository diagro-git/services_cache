<?php

namespace App\Http\Controllers;

use App\Jobs\CacheRemoveResource;
use App\Jobs\CacheStoreResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CacheController extends Controller
{

    public function get(Request $request)
    {
        $key = $request->header('x-diagro-cache-key');
        $tags = explode(' ', $request->header('x-diagro-cache-tags'));

        logger()->debug("GET");
        logger()->debug("key: " . $key);
        logger()->debug("tags: " . print_r($tags, true));

        $cachedValue = Cache::tags($tags)->get($key);
        if($cachedValue == null) {
            return response(status: 404);
        } else {
            return response()->json($cachedValue);
        }
    }

    public function store(Request $request)
    {
        $key = $request->header('x-diagro-cache-key');
        $tags = explode(' ', $request->header('x-diagro-cache-tags'));
        $body = $request->validate([
            'data' => 'required|array', //json array
            'usedResources' => 'required|array'
        ]);

        logger()->debug("STORE");
        logger()->debug("key: " . $key);
        logger()->debug("tags: " . print_r($tags, true));
        logger()->debug("body: " . print_r($body, true));

        CacheStoreResource::dispatch($key, $tags, $body['data'], $body['usedResources']);
    }

    public function remove(Request $request, ?int $user_id = null, ?int $company_id = null)
    {
        $body = $request->validate([
            'resources' => 'required|array'
        ]);

        CacheRemoveResource::dispatch($body['resources'], $user_id, $company_id);
    }

}
