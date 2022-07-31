<?php

namespace App\Utils;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * CacheHelper class : Provides a set of methods to manage cache
 */
class CacheHelper
{
    public static function get(Model | Collection $data, int $cacheTime = null): JsonResource | AnonymousResourceCollection | null
    {
        $cacheTime = $cacheTime ?? now()->addDay();
        $resource = self::resource($data);
        if ($toReturn = Cache::remember(self::key($data), $cacheTime, function () use ($resource): JsonResource | AnonymousResourceCollection {
            return $resource;
        })) {
            return $toReturn;
        }

        return null;
    }

    public static function delete(Model | Collection $toForget): bool
    {
        if ($toForget instanceof Collection) {
            if (self::has($toForget)) {
                if (Cache::forget(self::key($toForget))) {
                    return true;
                }

                return false;
            }

            return true;
        } else {
            if (self::has($toForget->getTable())) {
                Cache::forget($toForget->getTable());
            }
            if (self::has($toForget)) {
                if (Cache::forget(self::key($toForget))) {
                    return true;
                }

                return false;
            }

            return true;
        }
    }

    public static function forget(Model | Collection $toForget): bool
    {
        return self::delete($toForget);
    }

    public static function update(Model | Collection $oldData, Model | Collection $updatedData, int $cacheTime = null): JsonResource | AnonymousResourceCollection | null
    {
        if (self::delete($oldData)) {
            return self::get($updatedData, $cacheTime);
        }

        return null;
    }

    public static function key(string | Model | Collection | array $data): string | null
    {
        if (is_string($data)) {
            return $data;
        } elseif ($data instanceof Model) {
            return $data->getTable().'_'.$data->id;
        } elseif ($data instanceof Collection) {
            return $data->first()->getTable();
        } elseif (is_array($data)) {
            return $data[0]->getTable();
        }

        return null;
    }

    public static function class(Model | Collection $data): string
    {
        if ($data instanceof Model) {
            return get_class($data);
        }

        return get_class($data->first());
    }

    public static function resource(Model | Collection $data): JsonResource | AnonymousResourceCollection
    {
        return DB::transaction(function () use ($data): JsonResource | AnonymousResourceCollection {
            return self::class($data)::resource($data);
        });
    }

    public static function has(string | Model | Collection | array $data): bool
    {
        return Cache::has(self::key($data));
    }
}
