<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationStatusHistory extends Model
{
    protected $table = 'application_status_histories';

    protected $fillable = [
        'application_id',
        'old_status',
        'new_status',
        'status',
        'comment',
        'note',
        'changed_by',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
