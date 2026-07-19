<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

trait HasTranslations
{
    /**
     * Get the translations relation.
     */
    public function translations()
    {
        $translationModel = $this->getTranslationModelClass();

        if (class_exists($translationModel)) {
            return $this->hasMany($translationModel, $this->getTranslationForeignKey());
        }

        return null;
    }

    /**
     * Get the translation model class name.
     */
    public function getTranslationModelClass(): string
    {
        return static::class.'Translation';
    }

    /**
     * Get the translation foreign key name.
     */
    public function getTranslationForeignKey(): string
    {
        return Str::snake(class_basename(static::class)).'_id';
    }

    /**
     * Get a translation value with English fallback.
     */
    public function translate(string $field, ?string $locale = null): mixed
    {
        $locale = $locale ?: App::getLocale();
        $translations = $this->loadedTranslations();

        $translation = $translations->where('locale', $locale)->first();

        if ($translation && ! empty($translation->{$field})) {
            return $translation->{$field};
        }

        if ($locale !== 'en') {
            $fallbackTranslation = $translations->where('locale', 'en')->first();

            if ($fallbackTranslation && ! empty($fallbackTranslation->{$field})) {
                return $fallbackTranslation->{$field};
            }
        }

        return null;
    }

    /**
     * Return loaded translations or load them through the relationship.
     */
    protected function loadedTranslations(): Collection
    {
        if (method_exists($this, 'relationLoaded') && $this->relationLoaded('translations')) {
            return collect($this->getRelation('translations'));
        }

        $relation = $this->translations();

        if ($relation === null) {
            return collect();
        }

        return $relation->get();
    }
}
