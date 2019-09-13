<?php
/**
 * Created by PhpStorm.
 * User: bnanna
 * Date: 24/11/2016
 * Time: 08:59
 */

namespace Leo\Form\File;


use Leo\ObjectBase;

class UploadMultipleFiles extends ObjectBase
{

    protected $storageFolder;
    protected $files = [];
    protected $fileName;
    protected $inputName;
    protected $howManyFiles = 0;
    protected $uploaded =[], $failed =[];


    protected function convertFiles($inputName)
    {
        $this->howManyFiles = count($_FILES[$inputName]['name']);

        for($i = 0; $i < $this->howManyFiles; $i++)
        {
            $name = basename($_FILES[$inputName]['name'][$i]);
            $type = $_FILES[$inputName]['type'][$i];
            $error = $_FILES[$inputName]['error'][$i];
            $size = $_FILES[$inputName]['size'][$i];
            $tmp_name = $_FILES[$inputName]['tmp_name'][$i];

            $this->files[] = compact('name', 'type', 'error', 'size', 'tmp_name');
        }

    }

    public function uploadFile()
    {

        $this->uploaded = $this->failed = [];

        $storageFolder = $this->storageFolder;

        $this->convertFiles($this->inputName);

        $destination = WEB_ROOT . DS . $storageFolder;

        if (is_dir($destination) && is_writable($destination))
        {
            foreach ($this->files as $file) {

                if ($file["error"] !== UPLOAD_ERR_OK) {
                    return false;
                }
                $file_parts = explode('.', $file['name']);

                $ext = end($file_parts);

                if ($this->fileName) {//if a custom name was provided use that
                    $filename = sanitize($this->fileName, true) . '.' . $ext;
                    $filePath = $destination . DIRECTORY_SEPARATOR . $filename;
                } else {
                    $filename = self::getFileName($file['name'], $ext);
                    $filePath = $destination . DIRECTORY_SEPARATOR . $filename;
                }

                while (file_exists($filePath)) {
                    $filename = self::getFileName($this->fileName ?: $file['name'], $ext);

                    $filePath = $destination . DIRECTORY_SEPARATOR . $filename;
                }

                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                    $this->uploaded[] = array(
                        'name' => $filename,
                        'location' => empty($this->storageFolder) ? $filename : $this->storageFolder . DS . $filename,
                        'size' => $file['size'],
                        'type' => $file['type']
                    );
                } else {
                    $this->failed[] = ['name' => $file['name'], 'location' => $filePath];
                }
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

    public function getUploaded()
    {
        return $this->uploaded;
    }

    public function getFailed()
    {
        return $this->failed;
    }

}