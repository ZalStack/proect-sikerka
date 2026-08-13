<?php
// app/Models/FhlConfig.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FhlConfig extends Model
{
    use HasFactory;

    protected $table = 'fhl_config';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get config value by key
     */
    public static function getValue($key, $default = null)
    {
        $config = self::where('key', $key)->first();
        return $config ? $config->value : $default;
    }

    /**
     * Set config value
     */
    public static function setValue($key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Get all config as array
     */
    public static function getAll()
    {
        return self::pluck('value', 'key')->toArray();
    }
}