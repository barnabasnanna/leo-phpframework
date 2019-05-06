<?php
namespace Leo\Event;

/**
 * The event dispatched by the EventManager
 *
 * @author barnabasnanna
 */
class Event
{
    /**
     *
     * @var string 
     */
    protected $event_name;
    protected $result = [];
    
    /**
     *
     * @var array 
     */
    protected $event_params = [];
    
    public function getEventName()
    {
        return $this->event_name;
    }

    public function getEventParams()
    {
        return $this->event_params;
    }
    
    public function setEventName($event_name)
    {
        $this->event_name = (string)$event_name;
        return $this;
    }

    public function setEventParams(array $event_params)
    {
        $this->event_params = $event_params;
        return $this;
    }
    
    public function addResult($name, array $result){
        $this->result = array_merge_recursive($this->result, [strval($name) => $result]);
        return $this;
    }
    
    public function getResult(){
        return $this->result;
    }


    
}
