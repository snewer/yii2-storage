<?php

namespace snewer\storage;

use Yii;
use yii\base\Component;
use yii\base\InvalidCallException;

class Storage extends Component
{

    public $drivers;
    private $_drivers;

    /**
     * @param $id
     * @return StorageInterface
     */
    private function getStorage($id)
    {
        if (!isset($this->drivers[$id])) {
            throw new InvalidCallException("Storage driver '$id' not found");
        }
        if (!isset($this->_drivers[$id])) {
            $this->_drivers[$id] = Yii::createObject($this->drivers[$id]);
        }
        return $this->_drivers[$id];
    }

    public function upload($storageId, $binary, $extension)
    {
        return $this->getStorage($storageId)->upload($binary, $extension);
    }

    public function getUrl($storageId, $path)
    {
        return $this->getStorage($storageId)->getUrl($path);
    }

    public function getSource($storageId, $path)
    {
        return $this->getStorage($storageId)->getSource($path);
    }

    public function delete($storageId, $path)
    {
        return $this->getStorage($storageId)->delete($path);
    }

}