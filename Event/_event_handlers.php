<?php

/**
 * Merges all event handlers in application
 */

/**
 * user application event_handlers
 */
$app_event_handlers = APP_PATH . DS . 'Config' . DS . 'event_handlers.php';

$replacements = \file_exists($app_event_handlers) ? require $app_event_handlers : [];

$package_eventhandlers = \leo()->getPackageManager()->getPackagesEventHandlers();

return array_merge_recursive($package_eventhandlers,$replacements);
