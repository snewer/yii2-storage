<?php

namespace snewer\storage;

use yii\base\Object;

abstract class AbstractStorage extends Object
{

    /**
     * Идентификатор хранилища.
     * @var int
     */
    public $id;

    /**
     * Название хранилища.
     * @var string
     */
    public $name;

    /**
     * Записывает содержимое переменной $binary в файл хранилища с расширением $extension
     *
     * @param $binary
     * @param $extension
     * @return string|boolean - возвращает путь к файлу относительно хранилища
     */
    abstract public function upload($binary, $extension);

    /**
     * Возвращает веб-доступный путь к файлу хранилища
     *
     * @param $path - путь к файлу в хранилище, на основании которого можно однозначно определить файл
     * @return string|bool
     */
    abstract public function getUrl($path);

    /**
     * Возвращает содержимое файла.
     * @param $path - путь к файлу в хранилище
     * @return string|bool
     */
    abstract public function getSource($path);

    /**
     * Удаляет файл из хранилища
     *
     * @param $path
     * @return boolean
     */
    abstract public function delete($path);

    /**
     * Равномерно и однозначно извлекает один из $baseUrls по переданному $path.
     * @param array|string $baseUrls - массив
     * @param string $path - путь к файлу в хранилище.
     * @return string
     */
    protected function fetchBaseUrlByPath($baseUrls, $path)
    {
        if (is_array($baseUrls)) {
            $keys = array_keys($baseUrls);
            $keyIndex = abs(crc32($path)) % count($keys);
            $key = $keys[$keyIndex];
            return $baseUrls[$key];
        }
        return $baseUrls;
    }

}