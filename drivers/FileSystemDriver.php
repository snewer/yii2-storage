<?php

namespace snewer\storage\drivers;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Component;
use yii\helpers\Url;
use snewer\storage\StorageInterface;

class FileSystemDriver extends Component implements StorageInterface
{

    // путь дирректории для загрузок
    public $uploadPath;
    // URL до директории для загрузок
    public $uploadUrl;
    public $depth = 3;

    public function init()
    {
        if (!isset($this->uploadPath, $this->uploadUrl)) {
            throw new InvalidConfigException;
        }
    }

    public function getUrl($path)
    {
        return Url::to(Yii::getAlias(rtrim($this->uploadUrl, '/')) . $path, true);
    }

    public function getSource($path)
    {
        $uploadPath = Yii::getAlias(rtrim($this->uploadPath, '/'));
        $path = ltrim($path, '/');
        return file_get_contents("$uploadPath/$path");
    }

    public function upload($source, $extension)
    {
        $uploadPath = Yii::getAlias($this->uploadPath);
        $uploadPath = rtrim($uploadPath, '/');
        do {
            $path = '';
            // используем древовидную структуру директорий,
            // что бы в одной директории не накапливалось большое кол-во файлов
            for ($i = 0; $i < $this->depth; $i++) {
                $path .= '/' . substr(md5(microtime()), 0, 2);
                // AdBlocker блокирует пути, в которых встречаются некоторые ключевые слова, такие как "ad", "adv"
                // поэтому убираем их из пути.
                $path = str_replace('ad', 'ww', $path);
                if (!is_dir($uploadPath . $path)) {
                    mkdir($uploadPath . $path);
                }
            }
            $path .= '/' . uniqid() . '.' . strtolower($extension);
        } while (is_file($uploadPath . $path));
        return file_put_contents($uploadPath . $path, $source) ? $path : false;
    }

    public function delete($path)
    {
        $filePath = Yii::getAlias(rtrim($this->uploadUrl, '/')) . $path;
        return unlink($filePath);
    }

}