<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;
    protected $table = 'photos';
    protected $fillable = [
        'title',
        'path',
        'extension',
        'status',
        
    ];

     /**
     * Get the users associated with the photo.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'photo_user', 'photo_id', 'user_id');
    }
}
