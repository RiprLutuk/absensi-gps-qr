<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
    ];

    public static function getValue($key, $default = null)
    {
        return \Illuminate\Support\Facades\Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            return self::where('key', $key)->value('value') ?? $default;
        });
    }
}
