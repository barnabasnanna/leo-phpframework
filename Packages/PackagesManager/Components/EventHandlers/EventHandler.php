<?php

//namespace app\Packages\***\Components\EventHandlers;

use Leo\Event\Event;
use Leo\Interfaces\I_EventHandler;

/**
 * Description of EventHandler
 *
 * @author barnabasnanna
 * 19/01/2016
 */
class EventHandler implements I_EventHandler
{

    protected $event;

    public function run()
    {
        //TODO look at catching and handling any exceptions thrown by handlers
        switch ($this->getEvent()->getEventName())
        {
//            case 'leo_get_all_members':
//                $this->handler();
//                break;
        }
    }


    /**
     * Function
     */
    public function handler(){}

    /**
     * 
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    public function setEvent(Event $event)
    {
        $this->event = $event;
    }

}
