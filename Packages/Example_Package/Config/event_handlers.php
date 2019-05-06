<?php
/**
 * @file
 * Event handlers configurations
 * Register your event names and handler classes.
 *
 * @code
 * 'leo_website_user_resetting_password' => array(
 * array('_class_' => '\app\Packages\{Package_Name}\Components\EventHandlers\AccessEventHandler')
 * ),
 * 
 * @endcode
 *
 * The handler class is instantiated and the run() is called. In the run() method, use a switch statement to
 * handle different event names.
 *
 * @code
 *  public function run(){
 *
 *   switch($event_name){
 *      case 'event_name':
 *        $this->handler();
 *      break;
 *    }
 *
 *  }
 * @endcode
 */
return array(
//    'event_name' => array(
//        array('_class_' => '\app\Packages\{Package_Name}\EventHandlers\{Event_Handler_File_Name}')
//    )
);