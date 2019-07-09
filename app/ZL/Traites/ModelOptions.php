<?php

namespace App\ZL\Traites;

trait ModelOptions
{
    //$options_number=0 $options_array = []

    public $options_number = 0;

    public function getOptionsArry()
    {
        return $this->options_array;
    }
    public function appendOption($target)
    {
        $this->options_number |= pow(2,$target);
    }

    public function removeOption($target)
    {
        if($this->containOption($target)){
            $this->options_number = ($this->options_number-pow(2,$target));
        }
    }

    public function containOption($target)
    {
        return (($this->options_number | pow(2,$target)) == $this->options_number);
    }

    public function appendOptionByTitle($title)
    {
        $this->appendOption(array_search($title,$this->options_array));
    }

    public function removeOptionByTitle($title)
    {
        $this->removeOption(array_search($title,$this->options_array));
    }

    public function setOptionsByRequest()
    {
        foreach ($this->options_array as $k=>$v) {
            $req = $GLOBALS['request']->all();
            $_has = isset($req[$v])?$req[$v]:false;
            if($_has){
                $this->appendOption($k);
            }
        }
    }
    
    public function setOptionsAttribute($value)
    {
        $this->setOptionsByRequest();
        $this->attributes['options'] = $this->options_number;
    }

    public function getOptionsAttribute($value)
    {
        return $this->getOptionsArray($value);
    }

    protected function getOptionsArray($value)
    {
        $this->options_number = $value;
        $res = null;
        foreach ($this->options_array as $k=>$v) {
            if($this->containOption($k)){
                $_value = 1;
            }else{
                $_value = 0;
            }
            $res[$v] = $_value;
        }
        return $res;
    }
}