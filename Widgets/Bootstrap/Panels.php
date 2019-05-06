<?php

namespace Leo\Widgets\Bootstrap;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Bootstrap Panels
 *
 * @author barnabasnanna
 */
class Panels extends \Leo\ObjectBase
{

    protected $headerText = '';
    protected $class = 'default';
    protected $options = [];
    protected $HeaderTitle;
    protected $footerText = '';
    protected $HeaderTitleTag = 'h3';
    protected $showFooterText = false;
    protected $showHeaderTitle = false;
    protected $showHeaderText = false;
    protected $body;

    

    public function getTemplate()
    {
        $template = "<div class='panel panel-{$this->getClass()}'>";
        $template .= '<div class="panel-heading">';
        $template .= $this->getShowHeaderText() ? '{{header}}' : '';
        $template .= $this->getShowHeaderTitle() ? '<' . $this->getHeaderTitleTag() . ' class="panel-title">{{title}}</' . $this->getHeaderTitleTag() . '>' : '';
        $template .= '</div>';
        $template .='<div class="panel-body">{{body}}</div>';
        $template .= $this->getShowFooterText() ? '<div class="panel-footer">{{footer}}</div>' : '';
        $template .='</div>';

        return $template;
    }

    public function __toString()
    {
        $template = $this->getTemplate();
        $template = \str_ireplace('{{header}}', $this->getHeaderText(), $template);
        $template = \str_ireplace('{{title}}', $this->getHeaderTitle(), $template);
        $template = \str_ireplace('{{body}}', $this->getBody(), $template);
        $string = \str_ireplace('{{footer}}', $this->getFooterText(), $template);

        return $string;
    }

    public function getHeaderText()
    {
        return $this->headerText;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getFooterText()
    {
        return $this->footerText;
    }

    /**
     * Should footer text be shown
     * @return bool
     */
    public function getShowFooterText()
    {
        return $this->showFooterText;
    }

    /**
     * Should header text be shown
     * @return bool
     */
    public function getShowHeaderText()
    {
        return $this->showHeaderText;
    }

    public function setHeaderText($headerText)
    {
        $this->headerText = $headerText;
        return $this;
    }

    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    

    public function setFooterText($footerText)
    {
        $this->footerText = $footerText;
        return $this;
    }

    public function setShowFooter($showFooter)
    {
        $this->showFooterText = $showFooter;
        return $this;
    }

    public function setShowHeader($showHeader)
    {
        $this->showHeaderText = $showHeader;
        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }
    
    /**
     * Wrapper tag for panel header ttitle.
     * @return string
     */
    public function getHeaderTitle()
    {
        return $this->HeaderTitleTag;
    }

    public function setHeaderTitle($HeaderTitle)
    {
        $this->HeaderTitleTag = $HeaderTitle;
        return $this;
    }
    
    /**
     * Wrapper tag for panel header ttitle.
     * @return string
     */
    public function getHeaderTitleTag()
    {
        return $this->HeaderTitleTag;
    }

    public function setHeaderTitleTag($HeaderTitleTag)
    {
        $this->HeaderTitleTag = $HeaderTitleTag;
        return $this;
    }

    /**
     * Return bool indicating if panel title should be shown.
     * @return string
     */
    public function getShowHeaderTitle()
    {
        return $this->showHeaderTitle;
    }

    /**
     * Should header title be displayed
     * @param bool $showHeaderTitle
     * @return \Leo\Widgets\Bootstrap\Panels
     */
    public function setShowHeaderTitle($showHeaderTitle)
    {
        $this->showHeaderTitle = $showHeaderTitle;
        return $this;
    }

}
