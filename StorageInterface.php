<?php

namespace snewer\storage;

interface StorageInterface
{

    /**
     * Записывает содержимое переменной $binary в файл хранилища с расширением $extension
     *
     * @param $binary
     * @param $extension
     * @return string|boolean - возвращает путь к файлу относительно хранилища
     */
    public function upload($binary, $extension);

    /**
     * Возвращает веб-доступный путь к файлу хранилища
     *
     * @param $path - путь к файлу в хранилище, на основании которого можно однозначно определить файл
     * @return string
     */
    public function getUrl($path);

    /**
     * Возвращает содержимое файла.
     * @param $path - путь к файлу в хранилище
     * @return string
     */
    public function getSource($path);

    /**
     * Удаляет файл из хранилища
     *
     * @param $path
     * @return boolean
     */
    public function delete($path);

}