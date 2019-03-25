<?php

namespace App;

class UserType extends MbsBaseModel
{
    public static $CUSTOMER = 1;

    protected $table = 'user_type';
    protected $fillable = [
        'name',
    ];
    protected $hidden = [
        'status_id', 'created_at', 'updated_at',
    ];
    public function __construct()
    {
        parent::__construct($this->table, $this->fillable);
    }
}
