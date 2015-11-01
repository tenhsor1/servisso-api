<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Call extends Model
{

    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'calls';

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [   'service_id'
                        ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['service_id', 'updated_at', 'created_at', 'deleted_at'];

    public function service()
    {
        // 1 call is related to one service
        return $this->belongsTo('App\Service');
    }
}
