<?php
namespace Leo\Form\Input;

/**
 * Interface for all input elements
 * @author barnabasnanna
 * Date 15/01/15
 */
interface InputInterface
{
    public function getName();
    public function setName($name);
    public function getType();
    public function setType($type);
    public function getValue();
    public function setValue($value);
}
