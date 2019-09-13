<?php
namespace Leo\Components\Menu;

/**
 * Description of Nav
 *
 * @author barnabasnanna
 * Date: 5/1/16
 */
class Nav
{

    protected $items = [];
    protected $wrap = 'ul';
    protected $options = [];

    /**
     * Add a nav menu item
     * @param Link $link
     * @return $this
     * @internal param array $item
     */
    public function addItem(Link $link)
    {
        $this->items[] = $link;
        return $this;
    }

    public function __toString()
    {
        $links = '';

        foreach ($this->getItems() as $link)
        {
            if(false == $link->getVisibility()) continue;
            
            $links.= $link;
        }

        return sprintf('<%s %s>%s</%s>', $this->getWrap(), $this->getOptions(), $links, $this->getWrap());
    }

    /**
     * Wrapper html attributes converted to string
     * @return string
     */
    public function getOptions()
    {
        $options = '';

        foreach ($this->options as $attr => $value)
        {
            $options.= " $attr = '$value'";
        }

        return $options;
    }

    /**
     * Menu html attributes
     * @param array $options wrapper options
     * @return \Nav
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Array of menu Items 
     * @return array menu items
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Nav wrapper tag
     * @return string
     */
    public function getWrap()
    {
        return $this->wrap;
    }

    /**
     * Set menu items
     * @param array $items
     * @return \Nav
     */
    public function setItems(array $items)
    {
        $this->items = $items;
        return $this;
    }

    /**
     * set wrapper tag
     * @param string $wrap
     * @return \Nav
     */
    public function setWrap($wrap)
    {
        $this->wrap = $wrap;
        return $this;
    }

}
