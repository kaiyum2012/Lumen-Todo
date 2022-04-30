<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Todo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'note',
        'complete_at'
    ];

    protected $casts = [
        'complete_at' => 'datetime'
    ];

    protected $table = 'todos';

//    public function getCompleteAtAttribute($value)
//    {
//        $this->attributes['complete_at'] = Carbon::createFromTimestamp($value)->
//    }

    /**
     * @return HasOne
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class,'id','user_id');
    }

}
