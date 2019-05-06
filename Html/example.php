<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Page
{
    protected $title = 'About Us';

    protected $keywords;

    protected $meta_description;

    protected $layout = null;

    public function getKeyWords()
    {
        return $this->keywords;
    }

    public function setKeyWords($keywords)
    {
        $this->keywords = $keywords;
        return $this;
    }

    public function getMetaDescription()
    {
        return $this->meta_description;
    }

    public function setMetaDescription($description)
    {
        $this->meta_description = $description;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function setLayout(Layout $layout)
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * @return Layout
     */
    public function getLayout()
    {
        return $this->layout;
    }

    public function render()
    {
        if(!is_null($this->getLayout()))
        {
            return $this->getLayout()->render();
        }

    }

}


class Layout
{

    private $name 	= 'layout1';

    private $sections = array('section1','section2','section3', 'section4');

    private $template ='';

    public function getSections()
    {
        return $this->sections;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setSections(array $sections){
        $this->sections = $sections;
        return $this;
    }

    public function setTemplate($template){
        $this->template = $template;
        return $this;
    }

    private function getSectionContents()
    {
        $sections = array();

        foreach($this->getSections() as $sectionName)
        {
            $sections['{{'.$sectionName.'}}'] = (new Section($sectionName))->load();
        }

        return $sections;
    }

    public function render()
    {
        $contents = $this->getSectionContents();

        $template = $this->getTemplate();

        foreach($contents as $name => $content)
        {
            $template = str_replace($name, $content, $template);
        }

        return $template;
    }


}

class Section
{

    protected $sectionName = '';
    protected $sectionEvents = [];

    public function __construct($sectionName){
        $this->sectionName = strval($sectionName);
    }

    protected function preSectionLoad(){
        return $this;
    }

    protected function postSectionLoad(){
        return $this;
    }


    public function load()
    {

        try{

            $eventNames = $this->getSectionEvents();

            $this->preSectionLoad();

            if(is_array($eventNames)){

                foreach($eventNames as $eventName)
                {
                    $this->sectionEvents[$eventName]['event'] = new Event($eventName);
                    $this->sectionEvents[$eventName]['content'] =  $this->sectionEvents[$eventName]['event']->dispatch();
                }

            }

            $this->postSectionLoad();

        }catch (Exception $e){

        }

        return join('', array_column($this->sectionEvents,'content'));

    }

    public function getSectionEvents()
    {
        //get the events attached to a section
        return array('event1');
    }

}

class Event
{
    protected $eventName;

    public function __construct($eventName)
    {
        $this->setName($eventName);
    }

    public function setName($eventName)
    {
        $this->eventName = $eventName;
    }

    public function getName()
    {
        return $this->eventName;
    }

    public function dispatch()
    {
        return $this->findHandler()->run();
    }

    public function findHandler()
    {
        return new EventHandler(clone $this);
    }

}

class EventHandler
{
    protected $event = null;

    public function setEvent($event)
    {
        $this->event = $event;
    }

    public function __construct($event)
    {
        $this->setEvent($event);

        $this->log('event handler found for event '. $event->getName());
    }


    public function run()
    {
        $message = $this->getEvent()->getName(). ' has been handled successfully';
        //$this->log($message);
        return $message;
    }

    public function getEvent()
    {
        return $this->event;
    }

    protected function log($message)
    {
        echo $message; echo '<br/>';
    }
}


class CAction
{
    public function index()
    {

        $page 		= new Page();

        $page->setTitle('Profile page');

        $template =<<<TEMPLATE
	<div class="row">
		<div class="col-xd-6">{{section1}}</div><div class="col-xd-6">{{section2}}</div>
		<div class="col-xd-6">{{section3}}</div><div class="col-xd-6">{{section4}}</div>
	</div>
TEMPLATE;

        $page->setLayout((new Layout())->setTemplate($template));

        return $page->render();

    }
}

echo (new CAction())->index();