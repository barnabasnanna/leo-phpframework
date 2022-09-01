<?php
/**
 * Created by PhpStorm.
 * User: bnanna
 * Date: 06/11/2016
 * Time: 17:10
 */

namespace Leo\Components\BreadCrumbs;


class BreadCrumbs
{

    protected static $items = [];
    protected static $separator = '/';
    protected static $template = <<<TEM
<ol class="breadcrumb">
%s
</ol>
TEM;

    public static function getItems()
    {
        return static::$items;
    }

    public static function addItem(Item $item)
    {
        static::$items[] = $item;
    }

    public static function display()
    {
        if(!empty(static::getItems()))
        return sprintf(static::$template, join(static::$separator, static::getItems()));
    }

    public function reset()
    {
        static::$items = [];
    }

    public static function setSeparator($separator){
        self::$separator = $separator;
    }
}
