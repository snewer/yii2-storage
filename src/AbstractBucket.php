<?php

namespace snewer\storage;

use yii\base\BaseObject;

/**
 * Class AbstractStorage
 * @package snewer\storage
 * @property $name - Название хранилища
 */
abstract class AbstractBucket extends BaseObject
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
     * Дописывает содержимое $binary в конец файла $path.
     *
     * @param $path
     * @param $binary
     * @return boolean
     */
    abstract public function append($path, $binary);

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
     * @see https://gist.github.com/snewer/013bc268828454cc784c857eb695c434
     * @param null $length
     * @return array
     */
    private function getStopWords($length = null)
    {
        $stopWords = file_get_contents(__DIR__ . '/stopWords.txt');
        $stopWords = explode(PHP_EOL, $stopWords);
        if ($length) {
            return array_filter($stopWords, function ($value) use ($length) {
                return strlen($value) == $length;
            });
        } else {
            return $stopWords;
        }
    }

    /**
     * Генерирует случайную строку для именования директорий.
     * Выходная строка удовлетворяет регулярному выражению [0-9A-Za-z]+.
     * @param int $length - Длина генерируемой строки.
     * @param bool $toLower - Нужно ли, что бы выходная строка была в нижнем регистре.
     * @return mixed|string
     */
    protected function generateRandomString($length = 2, $toLower = true)
    {
        if ($toLower) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        } else {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        // AdBlocker блокирует пути, в которых встречаются некоторые ключевые слова,
        // такие как "ad", "bn" поэтому убираем их из пути.
        $stopWords = $this->getStopWords($length);
        foreach ($stopWords as $stopWord) {
            if (strcasecmp($randomString, $stopWord) === 0) {
                return str_repeat('s', $length);
            }
        }
        return $randomString;
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