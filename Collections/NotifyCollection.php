<?php

namespace Leo\Collections;

/**
 * Notifications is a collection of Notify objects
 *
 * @author barnabasnanna
 */
use Leo\Components\Notify;
use Leo\Utilities\myIterator;

class NotifyCollection extends myIterator
{

    protected static $key = '_notification_';
    public $header = 'Notification';
    protected $type = 'default';

    public function __construct()
    {
        parent::__construct();
        $this->clearNotifications();
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function setHeader($header)
    {
        $this->header = $header;
        return $this;
    }

    /**
     * Template used to wrap notification messages
     * @var string 
     */
    protected static $template = '<div class="panel panel-%s">
                            <div class="panel-heading">
                              <h3 class="panel-title">%s</h3>
                            </div>
                            <div class="panel-body">
                              %s
                            </div>
                          </div>';

    /**
     * Add a notify obj to collection
     * @param string $type
     * @param string $message
     */
    public function add($type, $message)
    {
        $notifications_obj = leo()->getSession()->get($this->getKey())? : $this;
        $notifications_obj->setType($type);

        $notify = new Notify($type, $message);
        $notifications_obj->addItem($notify);
        leo()->getSession()->set($this->getKey(), $notifications_obj);
    }

    /**
     * Set a notification message
     * @param string $type
     * @param string $message
     */
    public static function setMessage($type, $message)
    {
        $notification = new static();

        $notification->add($type, $message);
    }

    /**
     * Add validation errors
     * @param array $errors Validation errors
     */
    public function addVaidationErrors(array $errors)
    {
        $this->setType('error');

        foreach ($errors as $key => $error_messages)
        { 
            
            if (is_array($error_messages))
            {
                foreach ($error_messages as $error_message)
                {
                    $this->add('error', $error_message);
                }
            }
            elseif(is_string($error_messages))
            {
                $this->add('error', $error_messages);
            }
        }
    }

    /**
     * Show all notification messages
     * @param bool $clear should the notification be deleted after access
     * @return string
     */
    public static function show($clear = true)
    {
        if (!leo()->getSession()->exists(static::$key))
            return;

        $notifications = leo()->getSession()->get(static::$key);

        ob_start();

        foreach ($notifications->getItems() as $notify)
        {
            echo '<li>' . $notify . '</li>';
        }

        $messages = '<ul>' . ob_get_clean() . '</ul>';

        if ($clear)
        {
            static::clearNotifications();
        }

        return sprintf(static::$template, $notifications->getTypeClass(), $notifications->getHeader(), $messages);
    }

    /**
     * Clear notification from session
     */
    public static function clearNotifications()
    {
        leo()->getSession()->remove(static::$key);
    }



    public function getTemplate()
    {
        return static::$template;
    }

    public function setTemplate($template)
    {
        static::$template = (string) $template;
    }

    public function getKey()
    {
        return static::$key;
    }

    public function setKey($key = '')
    {
        static::$key = (string) $key;
    }

    protected function getTypeClass()
    {
        switch ($this->getType())
        {
            case 'error':
                return 'danger';
                break;
            default :
                return $this->getType();
        }
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type of notification. This is used to style the notification
     * @param string $type
     * @return \Leo\Collections\NotifyCollection
     */
    public function setType($type)
    {//
        $this->type = $type;
        return $this;
    }

    public static function hasItems()
    {
        $notifications = leo()->getSession()->get(static::$key);

        return $notifications && count($notifications->getItems());
    }

}
