<?php
namespace Leo\Components;

/**
 * Notify is used to display notification messages to the user
 *
 * @author barnabasnanna
 * Date: 25/12/2015
 */

class Notify
{
    
    public function __construct($type,$message)
    {
        $this->setType($type);
        $this->setMessage($message);
    }
    protected $message;
    protected $type;
    protected $template = '<%s class="%s">%s</%s>';
    /**
     *
     * @var string html tag used to wrap notification 
     */
    protected $wrapperTag = 'div';
    
    /**
     * Return html tag used to wrap notification
     * @return string
     */
    public function getWrapperTag()
    {
        return $this->wrapperTag;
    }

    /**
     * Sets the html tag used to wrap notification
     * @param string $wrapperTag
     * @return \Leo\Components\Notify
     */
    public function setWrapperTag($wrapperTag)
    {
        $this->wrapperTag = $wrapperTag;
        return $this;
    }

        /*
     * @var string class used to style notification
     */
    protected $class = '';
    
    public function getClass()
    {
        return $this->class;
    }

    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }


    public function getMessage()
    {
        return $this->message;
    }

    public function getType()
    {
        return $this->type;
    }
    
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function __toString()
    {
        $template = $this->getTemplate();
        
        return sprintf($template, $this->getWrapperTag(),
                $this->getClass(), $this->getMessage(), $this->getWrapperTag());
    }
        
    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }


}
