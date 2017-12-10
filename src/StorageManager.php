<?php

namespace snewer\storage;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class StorageManager extends Component
{

    /**
     * Массив доступных хранилищ.
     * Ключи массива являются идентификаторами хранилищ, а их значения — конфигурациями.
     * @var array
     */
    private $_storageDefinitions = [];

    /**
     * Массив экземляров хранилищ.
     * @var AbstractStorage[]
     */
    private $_storageObjects = [];

    /**
     * Сеттер списка хранилищ.
     * @param array $storageDefinitions
     * @throws InvalidConfigException
     */
    public function setList(array $storageDefinitions)
    {
        $this->_storageDefinitions = $storageDefinitions;
    }


    /**
     * Получение объекта хранилища по его названию.
     * @param string $name
     * @return AbstractStorage
     */
    public function getStorage($name)
    {
        if (!isset($this->_storageObjects[$name])) {
            $configuration = $this->_storageDefinitions[$name];
            $configuration['name'] = $name;
            $driver = Yii::createObject($configuration);
            $this->_storageObjects[$name] = $driver;
        }
        return $this->_storageObjects[$name];
    }

    /**
     * Геттер для получения объекта хранилища по его названию через
     * магическое свойство компонента.
     * @param string $name
     * @return mixed|AbstractStorage
     */
    public function __get($name)
    {
        return $this->getStorage($name);
    }

    /**
     * Загрузка файла в хранилище по его названию.
     * @param $storageName
     * @param $binary
     * @param $extension
     * @return bool|string - путь до файла относительно хранилища.
     */
    public function upload($storageName, $binary, $extension)
    {
        return $this->getStorage($storageName)->upload($binary, $extension);
    }

    /**
     * Получение URL файла по названию хранилища и относительного пути в нем.
     * @param $storageName
     * @param $path
     * @return string
     */
    public function getUrl($storageName, $path)
    {
        return $this->getStorage($storageName)->getUrl($path);
    }

    /**
     * Получение содержимого файла по названию хранилища и относительного пути в нем.
     * @param $storageName
     * @param $path
     * @return string
     */
    public function getSource($storageName, $path)
    {
        return $this->getStorage($storageName)->getSource($path);
    }

    /**
     * Удаление файла по названию хранилища и относительного пути в нем.
     * @param $storageName
     * @param $path
     * @return bool
     */
    public function delete($storageName, $path)
    {
        return $this->getStorage($storageName)->delete($path);
    }

}