<?php
namespace App\Traits;

use Illuminate\Support\Arr;

trait CacheResourceHelpers
{

    /**
     * Tags are the db and table part of the resource string.
     *
     * @param string $usedResource
     * @return string
     */
    protected static function resourceTag(string $usedResource): string
    {
        $parts = explode('.', $usedResource);
        return $parts[0] . '.' . $parts[1];
    }

    /**
     * Key from the resource string.
     *
     * @param string $usedResource
     * @return string
     */
    protected static function resourceKey(string $usedResource): string
    {
        return Arr::last(explode('.', $usedResource, 3));
    }

}
