<?php

namespace snewer\storage;

use Yii;
use yii\base\Component;

class StorageManager extends Component
{

    /**
     * Массив доступных хранилищ.
     * Ключи массива являются идентификаторами хранилищ, а их значения — конфигурациями.
     * @var array
     */
    private $_bucketsDefinitions = [];

    /**
     * Массив доступных хранилищ.
     * Ключи массива являются идентификаторами хранилищ, а их значения — объектами.
     * @var AbstractBucket[]
     */
    private $_bucketsObjects = [];

    /**
     * Сеттер списка хранилищ.
     * @param array $bucketsDefinitions
     */
    public function setBuckets(array $bucketsDefinitions)
    {
        $this->_bucketsDefinitions = $bucketsDefinitions;
    }


    /**
     * Получение объекта хранилища по его названию.
     * @param string $name
     * @return AbstractBucket
     */
    public function getBucket($name)
    {
        if (!isset($this->_bucketsObjects[$name])) {
            $configuration = $this->_bucketsDefinitions[$name];
            $configuration['name'] = $name;
            $driver = Yii::createObject($configuration);
            $this->_bucketsObjects[$name] = $driver;
        }
        return $this->_bucketsObjects[$name];
    }

    /**
     * Геттер для получения объекта хранилища по его названию через
     * магическое свойство компонента.
     * @param string $name
     * @return mixed|AbstractBucket
     */
    public function __get($name)
    {
        return $this->getBucket($name);
    }

    /**
     * Загрузка файла в хранилище по его названию.
     * @param string $bucketName
     * @param string $binary
     * @param string $extension
     * @return bool|string - путь до файла относительно хранилища.
     */
    public function upload($bucketName, $binary, $extension)
    {
        return $this->getBucket($bucketName)->upload($binary, $extension);
    }

    /**
     * Получение URL файла по названию хранилища и относительного пути в нем.
     * @param string $bucketName
     * @param string $path
     * @return string
     */
    public function getUrl($bucketName, $path)
    {
        return $this->getBucket($bucketName)->getUrl($path);
    }

    /**
     * Получение содержимого файла по названию хранилища и относительного пути в нем.
     * @param string $bucketName
     * @param string $path
     * @return string
     */
    public function getSource($bucketName, $path)
    {
        return $this->getBucket($bucketName)->getSource($path);
    }

    /**
     * Удаление файла по названию хранилища и относительного пути в нем.
     * @param string $bucketName
     * @param string $path
     * @return bool
     */
    public function delete($bucketName, $path)
    {
        return $this->getBucket($bucketName)->delete($path);
    }

}