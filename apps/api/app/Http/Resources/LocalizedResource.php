<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class LocalizedResource extends JsonResource
{
    protected ?string $locale = null;

    public function __construct(mixed $resource, ?string $locale = null)
    {
        parent::__construct($resource);
        $this->locale = $locale ?: App::getLocale();
    }

    public function toArray(Request $request): array
    {
        if (is_null($this->resource)) {
            return [];
        }

        $locale = $this->locale;
        $direction = $locale === 'ar' ? 'rtl' : 'ltr';

        $baseAttributes = [];

        // Convert resource to array
        if (method_exists($this->resource, 'toArray')) {
            $baseAttributes = $this->resource->toArray();
        } else {
            $baseAttributes = (array) $this->resource;
        }

        $translatedAttributes = [];
        if (is_object($this->resource)) {
            if (method_exists($this->resource, 'translate') && method_exists($this->resource, 'getTranslationModelClass')) {
                $translationModelClass = $this->resource->getTranslationModelClass();
                if (class_exists($translationModelClass)) {
                    $tempInstance = new $translationModelClass;
                    $fillableFields = $tempInstance->getFillable();

                    $exclude = [$this->resource->getTranslationForeignKey(), 'locale', 'id'];

                    foreach ($fillableFields as $field) {
                        if (! in_array($field, $exclude)) {
                            $translatedAttributes[$field] = $this->resource->translate($field, $locale);
                        }
                    }
                }

                unset($baseAttributes['translations']);
            }
        }

        $mergedData = array_merge($baseAttributes, $translatedAttributes);

        return [
            'locale' => $locale,
            'direction' => $direction,
            'data' => $mergedData,
        ];
    }
}
