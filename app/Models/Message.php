<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['sender_id','receiver_id','message','file_name','file_original_name','folder_path','file_type','is_read'];

    public function sender()
    {
        return $this->belongsTo(User::class,'sender_id','id');
    }
    public function receiver()
    {
        return $this->belongsTo(User::class,'receiver_id','id');
    }

    //override created-at timestamp
    protected static function boot(){
        parent::boot();
        static::creating(function($model){
            $model->created_at = Carbon::now();
        });
    }

    protected $appends =[
        'formatted_date'
    ];

    public function getFormattedDateAttribute(){
        $date = Carbon::parse($this->created_date);
        return $date->isToday() ? 'Today' : ($date->isYesterday() ? 'Yesterday' : $date->format('d-m-Y'));
    }
}
