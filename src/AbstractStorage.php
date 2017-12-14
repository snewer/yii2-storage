<?php

namespace snewer\storage;

use yii\base\Object;

/**
 * Class AbstractStorage
 * @package snewer\storage
 * @property $name - Название хранилища
 */
abstract class AbstractStorage extends Object
{

    /**
     * Название хранилища.
     * @var string
     */
    private $_name;

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

    /**
     * Генерирует строку из случайных символов [a-z0-9] при $multiCase = false
     * и из символов [a-zA-Z0-9] при $multiCase = true длиной $length символов.
     * @param $length - длина необходимой случайной строки.
     * @param bool $multiCase - нужно-ли использовать символы в разном регистре.
     * @return string
     */
    protected function generateRandomString($length, $multiCase = true)
    {
        $resultString = '';
        for ($i = 0; $i < $length; ++$i) {
            $randomChar = base_convert(rand(0, 35), 10, 36);
            if ($multiCase) {
                $uppercase = rand(0, 1) == 1;
                if ($uppercase) {
                    $randomChar = strtoupper($randomChar);
                }
            }
            $resultString .= $randomChar;
        }
        // AdBlocker блокирует пути, в которых встречаются некоторые ключевые слова,
        // такие как "ad", "adv" поэтому переименовываем их.
        $resultString = preg_replace('/^ad/i', 'ww', $resultString);
        return $resultString;
    }

    /**
     * Возвращает название хранилища.
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Устанавливает название хранилища.
     * @param $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

}