<?php
namespace Leo\Interfaces;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Interface for all event handlers
 * @author barnabasnanna
 * Date: 19/01/2016
 */
interface I_EventHandler
{
    public function run();
    public function setEvent(\Leo\Event\Event $event);
}
