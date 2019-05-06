<?php
namespace Leo\Interfaces;

/**
 * Interface for classes that handle class behavioural functionality
 * @author Barnabas
 */
interface I_Behaviour
{
    
    public function addBehaviour($behaviour_name, array $configArray);
    
    public function getPriorityIndex();
    
    public function getPriority();
    
    public function sortPriority();
    
    public function hasBehaviour();
    
    public function setBehaviourProperties($property_name, $property_value);
    
    public function checkBehaviourProperties($property_name);
    
    public function checkBehaviourMethods($methodname, $args);
    
    public function removeBehaviour($behaviour_name);
    
    public function getBehaviour($behaviour_name);
    
}
