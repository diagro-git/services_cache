<?php
namespace App\Jobs\Traits;

use Exception;
use Illuminate\Support\Facades\Redis;
use Predis\Client;

/**
 * Removes the references after cache entries has been deleted for a given tag.
 * It searches the TK keys and removes the tags_key elements where the given
 * tag is matched in the tags.
 *
 * If everything is removed, the key is also removed.
 * Otherwise it's updated.
 */
trait RemoveReferences
{

    /**
     * Remove references that has a given tag.
     *
     * @param string $tag
     * @return void
     */
    protected function removeReferences(string $tag)
    {
        /** @var Client $redis */
        $redis = Redis::connection()->client();
        $redis->select(config('database.redis.cache.database'));

        //let's go RAW mode
        $next = 0;
        do {
            try {
                $result = $redis->executeRaw(['SCAN', $next, 'MATCH', '*tk']);
                $next = array_shift($result);
                $keys = array_shift($result);
                $this->processKeys($redis, $keys, $tag);
            } catch(Exception $e)
            {
                $next = 0;
            }
        } while($next > 0);
    }

    /**
     * Every key contains the references for a resource (<db>.<table>).
     *
     * @param Client $redis
     * @param array $keys
     * @param string $tag
     * @return void
     */
    private function processKeys(Client $redis, array $keys, string $tag)
    {
        foreach($keys as $key) {
            $value = $redis->executeRaw(['GET', $key]);
            if(! empty($value)) {
                $entries = $this->processReferenceEntries($redis, unserialize($value), $tag);
                $this->setEntriesInRedis($redis, $entries, $key);
            }
        }
    }

    /**
     * Each reference entry is the resource key linked with the cached tags and keys.
     * This is looped in search for given tag and the tags/key pair is removed
     * from these reference entry.
     *
     * Returns the reference entries with the removed entries that matched the tag.
     *
     * @param Client $redis
     * @param array $entries
     * @param string $tag
     * @return array
     */
    private function processReferenceEntries(Client $redis, array $entries, string $tag): array
    {
        foreach($entries as $key => $tags_keys) {
            $indexes = $this->getMatchedIndex($tags_keys, $tag);
            if($indexes !== null) {
                foreach($indexes as $index) {
                    unset($entries[$key][$index]);
                }
            }
        }

        //empty entries?
        $this->removeEmptyEntries($entries);
        return $entries;
    }

    /**
     * Get the index(es) back from the tags/key array where given tag is in the tags.
     *
     * @param array $tags_keys
     * @param string $tag
     * @return array|null
     */
    private function getMatchedIndex(array $tags_keys, string $tag): ?array
    {
        $indexes = [];
        foreach($tags_keys as $index => $tags_key) {
            foreach($tags_key['tags'] as $t) {
                if($t == $tag) {
                    $indexes[] = $index;
                }
            }
        }

        return (count($indexes) > 0 ? array_unique($indexes) : null);
    }

    /**
     * Remove reference entries that are empty.
     * We don't want resource key's to point
     * to nothing... sounds kinda dumb.
     *
     * @param array $entries
     * @return void
     */
    private function removeEmptyEntries(array &$entries)
    {
        foreach($entries as $id => $tags_keys) {
            if(count($tags_keys) === 0) {
                unset($entries[$id]);
            }
        }
    }

    /**
     * Put the new reference entries back to the key.
     * If the entries are empty, everything is deleted.
     *
     * @param Client $redis
     * @param array $entries
     * @param string $key
     * @return string|int
     */
    private function setEntriesInRedis(Client $redis, array $entries, string $key): string|int
    {
        if(count($entries) > 0) {
            return $redis->executeRaw(['SET', $key, serialize($entries)]);
        } else {
            return $redis->executeRaw(['DEL', $key]);
        }
    }

}
