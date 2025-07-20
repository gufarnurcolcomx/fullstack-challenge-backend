<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Departement extends Model
{
    use HasFactory;

    protected $table = 'departements';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'departement_name',
        'max_clock_in_time',
        'max_clock_out_time',
        'user_id',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'departement_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = \Str::uuid()->toString();
        });
    }
}
