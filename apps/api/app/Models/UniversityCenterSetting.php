<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class UniversityCenterSetting extends Model
{
    use HasTranslations;

    protected $fillable = ['is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getTranslationModelClass(): string
    {
        return UniversityCenterSettingTranslation::class;
    }

    public function getTranslationForeignKey(): string
    {
        return 'center_setting_id';
    }
}
