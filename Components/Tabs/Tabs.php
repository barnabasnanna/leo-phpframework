<?php

namespace Leo\Components\Tabs;
/**
 * Created by PhpStorm.
 * User: bnanna
 * Date: 06/12/2016
 * Time: 18:50
 */
class Tabs
{
    protected $tabs = [];
    public $tab_id = 'myTab';


    /**
     * @return string
     */
    public function run()
    {
        if(count($this->tabs))
        return sprintf('%s%s', $this->getTabHeader(), $this->getTabBody());
    }

    public function getTabHeader()
    {
        $header = ['<ul class="nav nav-tabs responsive-tabs m-t-20" id="'.$this->tab_id.'">'];

        foreach ($this->tabs as $tab) {
            if($tab->visible)
            $header[] = '<li class="'.($tab->active ? 'active' : '').'"><a data-toggle="tab" href="#'.$tab->id.'"><i class="'.$tab->icon.'"></i> '.$tab->label.'</a></li>';
        }

        $header[] = '</ul>';

        return join('', $header);
    }

    public function getTabBody()
    {
        $body = ['<div class="tab-content clearfix" id="'.$this->tab_id.'Content">'];

        foreach ($this->tabs as $tab) {
            $body[]= $tab;
        }

        $body[] = '</div>';

        return join('',$body);
    }

    public function addTab(Item $tab)
    {
        $this->tabs[] = $tab;
    }



}