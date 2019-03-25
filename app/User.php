<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends MbsBaseModel implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'user';
    protected $fillable = [
        'username', 'fullname', 'email', 'profile_image', 'birthdate', 'phone_number', 'gender_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'status_id', 'created_at', 'updated_at',
    ];

    public function __construct()
    {
        parent::__construct($this->table, $this->fillable);
    }
}
