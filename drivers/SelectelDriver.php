<?php

namespace snewer\storage\drivers;

use Yii;
use yii\base\InvalidConfigException;
use yii\httpclient\Client;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;
use snewer\storage\AbstractStorage;

class SelectelDriver extends AbstractStorage
{

    public $user;
    public $key;
    public $container;
    public $url;
    public $depth = 3;

    private function getAuth()
    {
        $cache_key = __METHOD__;
        $auth_data = Yii::$app->cache->get($cache_key);
        if ($auth_data) {
            return $auth_data;
        } else {
            $client = new Client();
            $response = $client->createRequest()
                ->setMethod('get')
                ->setUrl('https://auth.selcdn.ru/')
                ->setHeaders([
                    'X-Auth-User' => $this->user,
                    'X-Auth-Key' => $this->key
                ])
                ->send();
            if (!$response->getIsOk()) {
                throw new HttpException($response->getStatusCode());
            }
            if ($response->getStatusCode() == 204) {
                $response_headers = $response->getHeaders();
                $auth_data = [];
                $auth_data['storage_url'] = $response_headers['X-Storage-Url'];
                $auth_data['auth_token'] = $response_headers['X-Auth-Token'];
                $auth_data['expire_auth_token'] = $response_headers['X-Expire-Auth-Token'];
                Yii::$app->cache->set($cache_key, $auth_data, min($auth_data['expire_auth_token'], 3600 * 24));
                return $auth_data;
            } else {
                throw new ForbiddenHttpException;
            }
        }
    }

    public function init()
    {
        parent::init();
        if (!isset($this->user, $this->key)) {
            throw new InvalidConfigException;
        }
        if (!isset($this->container)) {
            throw new InvalidConfigException;
        }
    }

    public function getUrl($path)
    {
        return rtrim($this->url, '/') . $path;
    }

    public function getSource($path)
    {
        $url = $this->getUrl($path);
        return file_get_contents($url);
    }

    public function upload($source, $extension)
    {
        $client = new Client();
        $path = '/' . $this->container;
        // используем древовидную структуру директорий,
        // что бы в одной директории не накапливалось большое кол-во файлов
        for ($i = 0; $i < $this->depth; $i++) {
            // почему название папки 2 символа:
            $path .= '/' . substr(md5(microtime()), 0, 2);
        }
        $path .= '/' . uniqid() . '.' . strtolower($extension);
        // AdBlocker блокирует пути, в которых встречаются некоторые ключевые слова, такие как "ad", "adv"
        // поэтому убираем их из пути.
        $path = str_replace('ad', 'ww', $path);
        $info = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_buffer($info, $source);
        finfo_close($info);
        $auth = $this->getAuth();
        $response = $client->createRequest()
            ->setMethod('put')
            ->setUrl(rtrim($auth['storage_url'], '/') . $path)
            ->setHeaders([
                'X-Auth-Token' => $auth['auth_token'],
                'Content-Type' => $mime_type,
                'ETag' => md5($source)
            ])
            ->setContent($source)
            ->send();
        if (!$response->getIsOk()) {
            throw new HttpException($response->getStatusCode());
        }
        return $response->getStatusCode() === '201' ? $path : false;
    }

    public function delete($path)
    {
        // TODO: Implement delete() method.
    }

}