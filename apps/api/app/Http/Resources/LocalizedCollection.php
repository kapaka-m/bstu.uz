<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\App;

class LocalizedCollection extends ResourceCollection
{
    protected ?string $locale = null;
    protected mixed $localizedItems = null;

    public function __construct(mixed $resource, ?string $locale = null)
    {
        $this->localizedItems = $resource;
        parent::__construct($resource);
        $this->locale = $locale ?: App::getLocale();
    }

    public function toArray(Request $request): array
    {
        $locale = $this->locale;
        $direction = $locale === 'ar' ? 'rtl' : 'ltr';

        return [
            'locale' => $locale,
            'direction' => $direction,
            'data' => collect($this->localizedItems)->map(function ($item) use ($locale) {
                if ($item instanceof LocalizedResource) {
                    $arr = $item->toArray(request());

                    return $arr['data'] ?? $arr;
                }
                $res = new LocalizedResource($item, $locale);
                $arr = $res->toArray(request());

                return $arr['data'] ?? $arr;
            })->all(),
        ];
    }
}
