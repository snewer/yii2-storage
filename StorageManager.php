<?php

namespace snewer\storage;

use Yii;
use yii\base\Component;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;

class StorageManager extends Component
{

    /**
     * Массив доступных хранилищ.
     * Ключи массива являются идентификаторами хранилищ, а их значения — конфигурациями.
     * @var array
     */
    private $_storageList = [];

    /**
     * Массив экземляров хранилищ.
     * @var AbstractStorage[]
     */
    private $_storageObjects = [];

    /**
     * Массив вида "название хранилища" => "его идентификатор".
     * @var array
     */
    private $_nameToIdMap = [];

    /**
     * Сеттер списка хранилищ.
     * @param array $storageList
     */
    public function setStorageList(array $storageList)
    {
        $this->_storageList = $storageList;
        foreach ($storageList as $id => $name) {
            $this->_nameToIdMap[$name] = $id;
        }
    }

    /**
     * Получение объекта хранилища по его идентификатору.
     * Существование хранилища на данном этапе не проверяется,
     * его должны гарантировать методы, вызывающие данный метод.
     * @param $id
     * @return AbstractStorage
     * @throws InvalidConfigException
     */
    private function getStorageObjectById($id)
    {
        if (!isset($this->_storageObjects[$id])) {
            $configuration = $this->_storageList[$id];
            if (!isset($this->name) || empty($this->name)) {
                throw new InvalidConfigException('Необходимо указать название хранилища.');
            }
            $driver = Yii::createObject($configuration);
            $driver->id = $id;
            $this->_storageObjects[$id] = $driver;
        }
        return $this->_storageObjects[$id];
    }

    /**
     * Получение объекта хранилища по его идентификатору.
     * @param int $id
     * @return AbstractStorage
     */
    public function getStorageById($id)
    {
        if (!isset($this->_storageList[$id])) {
            throw new InvalidCallException("Хранилище '$id' не найдено.");
        }
        return $this->getStorageObjectById($id);
    }

    /**
     * Получение объекта хранилища по его названию.
     * @param string $name
     * @return AbstractStorage
     */
    public function getStorageByName($name)
    {
        if (!isset($this->_nameToIdMap[$name])) {
            throw new InvalidCallException("Хранилище '$name' не найдено.");
        }
        return $this->getStorageObjectById($this->_nameToIdMap[$name]);
    }

    /**
     * Геттер для получения объекта хранилища по его названию через
     * магическое свойство компонента.
     * @param string $name
     * @return mixed|AbstractStorage
     */
    public function __get($name)
    {
        if (isset($this->_nameToIdMap[$name])) {
            return $this->getStorageByName($name);
        } else {
            return parent::__get($name);
        }
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
        return $this->getStorageByName($storageName)->upload($binary, $extension);
    }

    /**
     * Получение URL файла по названию хранилища и относительного пути в нем.
     * @param $storageName
     * @param $path
     * @return string
     */
    public function getUrl($storageName, $path)
    {
        return $this->getStorageByName($storageName)->getUrl($path);
    }

    /**
     * Получение содержимого файла по названию хранилища и относительного пути в нем.
     * @param $storageName
     * @param $path
     * @return string
     */
    public function getSource($storageName, $path)
    {
        return $this->getStorageByName($storageName)->getSource($path);
    }

    /**
     * Удаление файла по названию хранилища и относительного пути в нем.
     * @param $storageName
     * @param $path
     * @return bool
     */
    public function delete($storageName, $path)
    {
        return $this->getStorageByName($storageName)->delete($path);
    }

}