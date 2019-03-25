<?php

namespace App;

class UserUserType extends MbsBaseModel
{
    protected $table = 'user_user_type';
    protected $fillable = [
        'user_id', 'user_type_id',
    ];
    protected $hidden = [
        'status_id', 'created_at', 'updated_at'
    ];
    public function __construct()
    {
        parent::__construct($this->table, $this->fillable);
    }
}
