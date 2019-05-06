<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Leo\Components\Menu;

use Leo\ObjectBase;

/**
 * Description of Link
 *
 * @author barnabasnanna
 */
class Link extends ObjectBase
{

    protected $wrap = 'li';
    protected $wrap_options =[];
    protected $url = [];
    protected $text;
    protected $visibility = true;
    protected $text_wrapper;
    protected $text_wrapper_options=[];
    protected $icon;
    protected $sub_menu;

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return Link
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return string
     */
    public function getTextWrapper()
    {
        return $this->text_wrapper;
    }

    /**
     * @param string $text_wrapper
     * @return Link
     */
    public function setTextWrapper($text_wrapper)
    {
        $this->text_wrapper = $text_wrapper;
        return $this;
    }
    
    public function getVisibility()
    {
        return $this->visibility;
    }

    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
        return $this;
    }

            
    public function getWrap()
    {
        return $this->wrap;
    }

    public function __toString()
    {
        return sprintf('<%s %s>%s%s</%s>',
                $this->getWrap(),
                $this->getWrapOptions(),
                $this->getLink(),
                $this->getSubMenu(),
                $this->getWrap());
    }

    /**
     * @return null
     */
    public function getSubMenu()
    {
        return $this->sub_menu;
    }

    /**
     * @param Nav $sub_menu
     * @return Link
     */
    public function setSubMenu(Nav $sub_menu)
    {
        $this->sub_menu = $sub_menu;
        return $this;
    }

    public function getOpenTextWrapper()
    {

        return $this->text_wrapper ? sprintf('<%s %s>',$this->text_wrapper,$this->getTextWrapperOptions()) : '';

    }

    public function getClosingTextWrapper()
    {
        return $this->text_wrapper ? sprintf('</%s>',$this->text_wrapper) : '';
    }
    
    
    public function getLink()
    {
        return sprintf('<a href="%s">%s%s%s%s</a>',
                $this->getHref(),
                $this->getIconText(),
                $this->getOpenTextWrapper(),
                $this->text,
                $this->getClosingTextWrapper()
                );
    }

    protected function getIconText()
    {
        return $this->icon ? '<i class="'.$this->icon.'"></i>' : '';
    }

    
    
    protected function getHref()
    {
        if(!is_array($this->url)) return '';
        $href = array_shift($this->url);
        return $href.( !empty($this->url) ? '?'. http_build_query($this->url) : '');

    }
    
    public function setUrl(array $url)
    {
        $this->url = $url;
        $href = current($this->url);
        if(trim($href,'/') == leo()->getRequest()->getUrl())
        {
            isset($this->wrap_options['class']) ? 
                $this->wrap_options['class'] .= ' active' :  
                $this->wrap_options['class'] = 'active';
        }
        
    }


    protected function getTextWrapperOptions()
    {
        $options = '';
            
            foreach($this->text_wrapper_options as $attr=>$value)
            {
                $options.= " $attr = '$value'";
            }
            
            return $options;
    }
    
    protected function getWrapOptions()
    {
        $options = '';
        
        foreach($this->wrap_options as $attr=>$value)
        {
            $options.= " $attr = '$value'";
        }
            
            return $options;
    }

    /**
     * @param string $wrap
     * @return Link
     */
    public function setWrap($wrap)
    {
        $this->wrap = $wrap;
        return $this;
    }

    /**
     * @param array $wrap_options
     * @return Link
     */
    public function setWrapOptions(array $wrap_options)
    {
        $this->wrap_options = $wrap_options;
        return $this;
    }

    /**
     * @param array $text_wrapper_options
     * @return Link
     */
    public function setTextWrapperOptions($text_wrapper_options)
    {
        $this->text_wrapper_options = $text_wrapper_options;
        return $this;
    }


    /**
     * @return null
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param null $icon
     * @return Link
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }


    
}
