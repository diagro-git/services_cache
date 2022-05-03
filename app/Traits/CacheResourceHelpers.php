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
        return implode('.', explode('.', $usedResource, -1));
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
