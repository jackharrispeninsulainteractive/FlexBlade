<?php

namespace LucentBlade\View;

class Bag
{

    private array $data;


    public function __construct(){
        $this->data = [];
    }


    public function put(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function putArray(array $array,string $prefix = ""): void
    {
        foreach ($array as $key=>$value){
            $this->data[$prefix.$key] = $value;
        }
    }

    public function get($key,$default = "undefined"){
        if(array_key_exists($key,$this->data)){
            return $this->data[$key];
        }

        return $default;
    }

    public function all(): array
    {
        return $this->data;
    }

}