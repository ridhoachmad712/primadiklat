<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InhouseRequest extends Model
{
    public const STATUSES = [
        'new' => 'Baru',
        'contacted' => 'Sudah Dihubungi',
        'deal' => 'Deal',
        'closed' => 'Tidak Lanjut',
    ];

    protected $fillable = [
        'institution', 'contact_name', 'position', 'email', 'phone', 'topic', 'participants',
        'preferred_date', 'location', 'message', 'status',
    ];

    protected $casts = ['preferred_date' => 'date'];
}
