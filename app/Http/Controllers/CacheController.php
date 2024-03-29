<?php

namespace App\Http\Controllers;

use App\Jobs\CacheRemoveCompany;
use App\Jobs\CacheRemoveResource;
use App\Jobs\CacheRemoveUser;
use App\Jobs\CacheStoreResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CacheController extends Controller
{

    public function get(Request $request)
    {
        $key = $request->header('x-diagro-cache-key');
        $tags = explode(',', $request->header('x-diagro-cache-tags'));

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
        $tags = explode(',', $request->header('x-diagro-cache-tags'));
        $body = $request->validate([
            'data' => 'required|array', //json array
            'usedResources' => 'required|array'
        ]);

        $refs = [['key' => $key, 'tags' => $tags]];
        //andere key en tags die gelinkt zijn met de gebruikte resources.
        //gebeurt bij sub API requests in de backend.
        //wordt automatisch in de lib_api gedaan.
        if($value = $request->header('X-Diagro-Cache-Refs')) {
            foreach(explode(';', $value) as $ref) {
                $parts = explode(':', $ref);
                $refs[] = ['key' => $parts[0], 'tags' => explode(',', $parts[1])];
            }
        }
        $refs = array_unique($refs, SORT_REGULAR);

        CacheStoreResource::dispatch($key, $tags, $refs, $body['data'], $body['usedResources']);
    }

    public function remove(Request $request, ?int $user_id = null, ?int $company_id = null)
    {
        $body = $request->validate([
            'resources' => 'sometimes|array'
        ]);

        if(isset($body['resources'])) {
            CacheRemoveResource::dispatchSync($body['resources'], $user_id, $company_id);
        } elseif(! empty($user_id)) { //delete all user cache
            CacheRemoveUser::dispatch($user_id);
        } elseif(! empty($company_id)) { //delete all company cache
            CacheRemoveCompany::dispatch($company_id);
        }
    }

}
