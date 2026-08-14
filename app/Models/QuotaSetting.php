<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotaSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'description'];

    /**
     * Get setting value with proper type casting
     */
    public static function getValue($key, $default = null)
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    /**
     * Set setting value with type casting
     */
    public static function setValue($key, $value, $type = 'string', $description = null)
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_bool($value) ? ($value ? 'true' : 'false') : (string) $value,
                'type' => $type,
                'description' => $description,
            ]
        );

        return $setting;
    }
}
