<?php

namespace snewer\storage\drivers;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use snewer\storage\AbstractBucket;

class FileSystemDriver extends AbstractBucket
{

    /**
     * Путь до папки, в которую будут загружены файлы.
     * @var string
     */
    public $basePath;

    /**
     * URL до папки, в которую загружаются файлы.
     * Можно указать массив из нескольких вариантов,
     * после этого каждому файлу будет равномерно присвоено
     * одно из значений basePath.
     * @var string|string[]
     */
    public $baseUrl;

    /**
     * Уровень вложенности, относительно self::$basePath
     * @var int
     */
    public $depth = 3;

    /**
     * Длина названия дирректорий, которые создаются при загрузке файла.
     * @var int
     */
    public $dirNameLength = 2;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (!isset($this->basePath)) {
            throw new InvalidConfigException('Необходимо указать свойство basePath.');
        }
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function getUrl($path)
    {
        if (isset($this->baseUrl)) {
            $baseUrl = $this->fetchBaseUrlByPath($this->baseUrl, $path);
            return Url::to(Yii::getAlias(rtrim($baseUrl, '/')) . $path, true);
        } else {
            throw new InvalidConfigException('Необходимо указать свойство baseUrl.');
        }
    }

    /**
     * @inheritdoc
     */
    public function getSource($path)
    {
        $basePath = Yii::getAlias(rtrim($this->basePath, '/'));
        $path = ltrim($path, '/');
        return file_get_contents("$basePath/$path");
    }

    /**
     * @inheritdoc
     */
    public function upload($source, $extension)
    {
        $basePath = Yii::getAlias($this->basePath);
        $basePath = rtrim($basePath, '/');
        do {
            $path = '';
            // используем древовидную структуру директорий,
            // что бы в одной директории не накапливалось большое кол-во файлов
            for ($i = 0; $i < $this->depth; $i++) {
                $path .= '/' . $this->generateRandomString($this->dirNameLength, true);
                if (!is_dir($basePath . $path)) {
                    mkdir($basePath . $path);
                }
            }
            $path .= '/' . uniqid() . '.' . strtolower($extension);
        } while (is_file($basePath . $path));
        return file_put_contents($basePath . $path, $source) ? $path : false;
    }

    /**
     * @inheritdoc
     */
    public function delete($path)
    {
        $filePath = Yii::getAlias(rtrim($this->basePath, '/')) . $path;
        return @unlink($filePath);
    }

}