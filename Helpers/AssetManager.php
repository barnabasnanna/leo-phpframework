<?php

namespace Leo\Helpers;


use Leo\ObjectBase;

class AssetManager extends ObjectBase
{
    protected $css = [];
    protected $js = [];

    public function getJs(){
        return $this->js;
    }

    public function getCss(){
        return $this->css;
    }

    public function addCss($name, $filePath){
        $this->css[$name] = $filePath;
    }

    public function addJs($name, $filePath){
        $this->js[$name] = $filePath;
    }

    public function renderCss($name=''){
        $files = $this->css;

        if(!empty($this->css[$name])){
            $files = [$this->css[$name]];
        }

        foreach ($files as $file) {
            if (is_readable($file)) {
                echo '<style>'.file_get_contents($file).'</style>';
            }
        }
    }

    public function renderJs($name=''){
        $files = $this->js;

        if(!empty($this->js[$name])){
            $files = [$this->js[$name]];
        }

        foreach ($files as $file) {
            if (is_readable($file)) {
                echo '<script>'.file_get_contents($file).'</script>';
            }
        }
    }
}