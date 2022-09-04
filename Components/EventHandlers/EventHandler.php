<?php

namespace Leo\Event;

use Leo\Interfaces\I_EventHandler;
use Leo\Models\Me;
use Leo\ObjectBase;

/**
 * EventManager manages all event process including
 * dispatching an event, finding and passing event to event handlers
 *
 * @author barnabasnanna
 */
class EventManager extends ObjectBase
{

    /**
     * Event handlers
     * @var array
     */
    private static $eventHandlers = [];

    /**
     * Get errors generated when dispatching event
     * @var array
     */
    public static $errors = [];

    /**
     * @var bool Throw exception if a one occurs when handling event
     */
    protected static $throwException = true;

    protected $event;

    public static function cleanName($eventName)
    {
        if (!empty($eventName)) {
            return preg_replace('/\W/', '_', strtolower($eventName));
        }
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Dispatch an event which can be handled by associated event handlers
     * You can pass a target package to handle the event
     * @param string $event_name event name
     * @param array $event_params parameters passed to event handlers
     * @param string $targetPackageNameSpace target package namespace
     * @return mixed
     */
    public function dispatch($event_name, array $event_params = [], $targetPackageNameSpace = null)
    {
        $event = new Event();
        $event->setEventName(self::cleanName($event_name));
        $event->setEventParams($event_params);
        $this->event = $event;
        $this->throw($event, $targetPackageNameSpace);
    }

    /**
     * Checks if the handler is allowed to handle event for it may have been disabled.
     * If a targetPackageNamespace is given, only run the handler that matches
     * Else run all that are not disabled
     * @param array $handler_config
     * @param string $targetPackageNameSpace optional namespace of the targeted handler
     * @return false|array configuration array used to handle event
     */
    private static function inspectHandler(array $handler_config, $targetPackageNameSpace = null)
    {

        if (!isset($handler_config['_class_'])) {
            return false;
        }

        if (!is_null($targetPackageNameSpace) && $handler_config['_class_'] !== $targetPackageNameSpace) {
            return false;
        }

        if (isset($handler_config['_disabled_'])) {
            if (!$handler_config['_disabled_']) {
                return false;
            }

            unset($handler_config['_disabled_']);//unset as most likely not a property of _class_ to be autoloaded
        }

        //enable toggling of the exception handling per event
        if (isset($handler_config['_throwException_'])) {
            self::setThrowException(boolval($handler_config['_throwException_']));
        } else {
            self::setThrowException();
        }

        return $handler_config;

    }

    /**
     * Run all the handlers attached to a event
     * @param \Leo\Event\Event $event
     * @param string|null $targetPackageNameSpace full namespace path of target class
     */
    public function throw(Event $event, string $targetPackageNameSpace = null)
    {

        leo()->getLogger()->write('Dispatching event ' . $event->getEventName(), LOG_TYPE_DEBUG);

        //check local storage
        if (array_key_exists($event->getEventName(), self::getEventHandlers())) {
            $handler_configs = self::$eventHandlers[$event->getEventName()];
            foreach ($handler_configs as $handler_config) {
                $handler_config = self::inspectHandler($handler_config, $targetPackageNameSpace);
                if (false !== $handler_config) {
                    try {
                        $eventHandler = static::loadClass($handler_config);
                        if ($eventHandler instanceof I_EventHandler) {
                            leo()->getLogger()->write('Passing event to handler ' . get_class($eventHandler), LOG_TYPE_DEBUG);
                            $clonedEvent = clone $event; //pass a cloned event so handlers cant manipulate object
                            $eventHandler->setEvent($clonedEvent);
                            $eventHandler->run();
                            if ($clonedEvent->getResult()) {//has error
                                //copy result from cloned Event to the main event thrown
                                $event->addResult($event->getEventName(), $clonedEvent->getResult());
                            }
                        }
                    } catch (\Exception $e) {
                        leo()->getLogger()->write('Event '.$event->getEventName().' dispatch error ' . $e->getMessage(), LOG_TYPE_APP_ERROR);
                        if (self::$throwException) {
                            throw $e;
                        }
                    }
                }
            }
        } else {
            leo()->getLogger()->write('Event ' . $event->getEventName() . ' not found in event handlers map', LOG_TYPE_DEBUG);
        }
    }

    /**
     * Add an event handler config to the event manager map.
     * The event handler config array must have _class_ key
     * which the namespace path to class that will handle the event.
     * @param string $event_name
     * @param array $eventHandlerConfig
     */
    public function addEventHandler(string $event_name, array $eventHandlerConfig)
    {

        if (isset($eventHandlerConfig['_class_'])) {
            self::$eventHandlers[$event_name][] = $eventHandlerConfig;
        }

    }

    /**
     * Array map matching event names to handling classes
     * <pre>
     *  'leo_collect_user_profile_attributes' => array(
     * array('_class_' => "\app\Packages\Package1\Components\EventHandlers\EventHandler"),
     *          array('_class_' => "\app\Packages\Package2\Components\EventHandlers\EventHandler")
     * ),
     * </pre>
     * @return array event handler configuration
     */
    public static function getEventHandlers()
    {
        if (empty(self::$eventHandlers)) {
            self::$eventHandlers = require __DIR__ . DS . '_event_handlers.php';
        }

        return self::$eventHandlers;
    }

    /**
     * Should an exception be thrown during event handling
     * @param bool $throwException
     */
    public static function setThrowException(bool $throwException = true)
    {
        self::$throwException = $throwException;
    }

}
