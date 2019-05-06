<?php

/**
 * Created by PhpStorm.
 * User: bnanna
 * Date: 06/12/2016
 * Time: 18:51
 */

namespace Leo\Components\Tabs;


use Leo\ObjectBase;

class Item extends ObjectBase
{
    public $id = null;
    public $visible = true;
    public $active = false;
    public $class = 'tab-pane counties-pane animated fadeIn';
    public $content = null;
    public $icon = null;
    public $label = null;


    /**
     * @return string
     */
    public function __toString()
    {
        if($this->content && $this->visible) {
            return join('', array(
                '<div id="' . $this->id . '" class="' . $this->getClass() . '">',
                $this->content,
                '</div>'
            ));
        }
        else
        {
            return '';
        }
    }

    public function getClass()
    {
        return $this->isActive() ? $this->class . ' active ' : $this->class;
    }

    public function isActive()
    {
        return boolval($this->active);
    }

}