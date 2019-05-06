<?php
namespace Leo\Components\Table\Column;

use Leo\Db\ActiveRecord;

/**
 * For generating links in Table columns
 *
 * @author barnabasnanna
 */
class Link
{
    public $links = [];
    public $options = [];
    public $model = null;


    public function run(ActiveRecord $model)
    {
        $this->model = $model;
        
        $l = '';
        
        foreach($this->links as $link)
        {
           $l.=' <a '.$this->getOptions($link).' href="'.$this->repaclePlaceHolders($link['href']).'">'.$link['text'].'</a>'; 
        }
        
        return $l;
    }
    
    public function repaclePlaceHolders($link)
    {
        $pattern = '/\{(.+?)\}/is';

        preg_match_all($pattern, $link, $match);
        
        if(count($match))
        {
            foreach ($match[1] as $key=>$model_property)
            {
                $value = $this->model->getPropertyValue($model_property);
                $link = str_replace($match[0][$key], $value, $link);
            }
        }
        
        return $link;

    }


    protected function getOptions($link)
    {
        $options = '';
        
        if(isset($link['options']) && is_array($link['options']))
        {
            foreach($link['options'] as $k=>$val)
            {
                $options.=$k.'="'.$val.'" ';
            }
        }
        
        return $options;
    }
}
