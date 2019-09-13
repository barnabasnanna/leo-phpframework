<?php
/**
 * Created by PhpStorm.
 * User: bnanna
 * Date: 24/11/2016
 * Time: 08:59
 */

namespace Leo\Form\File;


use Leo\ObjectBase;

class UploadFiles extends ObjectBase
{

    protected $storageFolder;
    protected $files = [];
    protected $fileName;
    protected $inputName;
    public $uploaded, $failed;


    protected function convertFiles($inputName)
    {

        $name = basename($_FILES[$inputName]['name']);
        $type = $_FILES[$inputName]['type'];
        $error = $_FILES[$inputName]['error'];
        $size = $_FILES[$inputName]['size'];
        $tmp_name = $_FILES[$inputName]['tmp_name'];

        $this->files = compact('name', 'type', 'error', 'size', 'tmp_name');
    }

    public function uploadFile()
    {

        $this->uploaded = $this->failed = [];

        $storageFolder = $this->storageFolder;

        $this->convertFiles($this->inputName);

        $destination = WEB_ROOT . DS . $storageFolder;

        if (is_dir($destination) && is_writable($destination))
        {

            if ($this->files["error"] !== UPLOAD_ERR_OK)
            {
                return false;
            }
            $file_parts = explode('.', $this->files['name']);

            $ext = end($file_parts);

            if($this->fileName) {//if a custom name was provided use that
                $filename = sanitize($this->fileName,true).'.'.$ext;
                $filePath = $destination . DIRECTORY_SEPARATOR . $filename;
            }
            else
            {
                $filename = self::getFileName($this->files['name'], $ext);
                $filePath = $destination . DIRECTORY_SEPARATOR . $filename;
            }

            while(file_exists($filePath))
            {
                $filename = self::getFileName($this->fileName ?: $this->files['name'], $ext);

                $filePath = $destination . DIRECTORY_SEPARATOR . $filename;
            }

            if (move_uploaded_file($this->files['tmp_name'], $filePath))
            {
                $this->uploaded = array(
                    'name'=> $filename,
                    'location'=> $this->storageFolder.DS.$filename,
                    'size'=>$this->files['size'],
                    'type'=>$this->files['type']
                );
            }
            else
            {
                $this->failed = ['name' => $this->files['name'], 'location' => $filePath];
            }
        }
        else
        {
            throw new \Exception($destination . ' neither exist nor is writable.');
        }


        return !$this->hasError();

    }

    protected static function getFileName($name, $ext)
    {
        $name = sanitize($name,true);

        $filename = substr(md5( $name . rand(2, 2999) . time()), rand(2, 8), rand(15, 30));

        $filename = str_shuffle($filename) . '.' . strtolower($ext);

        return $filename;
    }

    /**
     * Delete files
     * @param array $files
     */
    public function unlinkFiles(array $files)
    {

        foreach ($files as $file)
        {
            if (!file_exists($file['location']))
                continue;
            unlink($file['location']);
        }
    }

    public function hasError()
    {
        return boolval(count($this->failed));
    }

}