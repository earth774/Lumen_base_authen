<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MbsBaseModel extends Model
{
    private $_table;
    private $_fillable;

    public function __construct($table, $fillable)
    {
        $this->_table = $table;
        $this->_fillable = $fillable;
    }

    public function getFillable()
    {
        return $this->_fillable;
    }

    public function getTable()
    {
        return $this->_table;
    }

    public function setData($array)
    {
        foreach ($array as $key => $value) {
            foreach ($this->_fillable as $valueF) {
                if ($key == $valueF) {
                    $this->$key = $value;
                }
            }
        }
    }

}
