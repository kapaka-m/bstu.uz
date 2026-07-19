<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageBlockTranslation extends Model
{
    protected $fillable = ['page_block_id', 'locale', 'title', 'subtitle', 'content', 'button_text'];

    public function pageBlock()
    {
        return $this->belongsTo(PageBlock::class);
    }
}
