<?php

namespace App\Utils;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class CacheHelper
{
    public static function get(Model | Collection $data, int $cacheTime = null): JsonResource | AnonymousResourceCollection
    {
        $cacheTime = $cacheTime ?? now()->addDay(1);
        $resource = self::resource($data);
        return Cache::remember(self::key($data), $cacheTime, function () use ($resource) {
            return $resource;
        });
    }

    public static function delete(string | Model | Collection | array $toForget): bool
    {
        // TODO : implement cache delete for collection when an item is deleted or update this item
        Cache::lock(self::key($toForget))->get(function () use ($toForget) {
            if (Cache::forget(self::key($toForget))) {
                return true;
            } else {
                return false;
            }
        });
        return false;
    }

    public static function forget(string | Model | Collection | array $toForget): bool
    {
        return self::delete($toForget);
    }

    public static function update(Model | Collection $data, int $cacheTime = null): JsonResource | AnonymousResourceCollection | null
    {
        // TODO : implement cache delete for collection when an item is deleted or update this item
        Cache::lock(self::key($data))->get(function () use ($data, $cacheTime) {
            if (self::delete($data)) {
                return self::get($data, $cacheTime);
            }
            return null;
        });
        return null;
    }

    public static function key(string | Model | Collection | array $data): string | null
    {
        if (is_string($data)) {
            return $data;
        } elseif ($data instanceof Model) {
            return $data->getTable() . '_' . $data->id;
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
        return self::class($data)::resource($data);
    }
}
