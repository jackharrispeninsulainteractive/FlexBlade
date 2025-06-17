<?php

namespace LucentBlade\View;

class View
{
    private static Bag $bag;

    public static function Bag(): Bag
    {
        if(!isset(self::$bag)){
            self::$bag = new Bag();
        }
        return self::$bag;
    }

}