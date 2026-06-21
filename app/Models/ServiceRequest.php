<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    protected $fillable = [
        'service_id',
        'service_title',
        'name',
        'phone',
        'email',
        'message',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
