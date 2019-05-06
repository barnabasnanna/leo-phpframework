<?php
namespace Leo\Packages\PackagesManager\Controllers;

use Leo\Controller\BaseController;

/**
 * Created by PhpStorm.
 * User: bnanna
 * Date: 06/12/2018
 * Time: 01:41
 */

class DbController extends BaseController
{

    /**
     * @throws \Exception
     */
    public function pathInstall(){
        $eventManager = leo()->getEventManager();
        $eventManager->dispatch('_db_install_');
        $eventManager->dispatch('_create_base_class_');
    }

}