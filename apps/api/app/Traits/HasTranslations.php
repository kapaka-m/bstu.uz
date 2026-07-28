<?php

namespace App\Traits;

use App\Models\Locale;
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
     * Get a translation value with the database-configured fallback locale.
     */
    public function translate(string $field, ?string $locale = null): mixed
    {
        $locale = $locale ?: App::getLocale();
        $translations = $this->loadedTranslations();

        $translation = $translations->where('locale', $locale)->first();

        if ($translation && ! empty($translation->{$field})) {
            return $translation->{$field};
        }

        $fallbackLocale = $this->fallbackLocaleCode();
        if ($fallbackLocale && $locale !== $fallbackLocale) {
            $fallbackTranslation = $translations->where('locale', $fallbackLocale)->first();

            if ($fallbackTranslation && ! empty($fallbackTranslation->{$field})) {
                return $fallbackTranslation->{$field};
            }
        }

        return null;
    }

    protected function fallbackLocaleCode(): ?string
    {
        return Locale::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->value('code')
            ?: config('app.fallback_locale');
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
