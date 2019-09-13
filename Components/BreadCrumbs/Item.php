<?php
/**
 * Created by PhpStorm.
 * User: bnanna
 * Date: 06/11/2016
 * Time: 17:23
 */

namespace Leo\Components\BreadCrumbs;


class Item
{

    public $icon;
    protected $active = false;
    protected $text;
    protected $url;
    protected $template = <<<TEM
<li class="%s">
<i class="fa fa-%s"></i> %s
</li>
TEM;


    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf($this->template,
            $this->isActive() ? 'active' : '',
            $this->getIcon(),
            FALSE === boolval($this->isActive()) ?
                $this->getLink($this->getText())
                : $this->getText());
    }

    public function getLink($text)
    {
        return '<a href="'.$this->getUrl().'">'.$text.'</a>';
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return Item
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     * @return Item
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param mixed $icon
     * @return Item
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     * @return Item
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     * @return Item
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }



}