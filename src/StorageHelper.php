<?php

namespace snewer\storage;

use Yii;
use Closure;
use yii\db\ActiveRecord;

class StorageHelper
{

    public static function log($message, $showProcess, $logFile)
    {
        if ($showProcess) {
            echo $message;
        }
        if ($logFile && is_writable($logFile)) {
            file_put_contents($logFile, $message, FILE_APPEND);
        }
    }

    public static function migrate(
        array $models,
        $pathAttributeName,
        $storageIdAttributeName,
        $destinationStorageName,
        $deleteFromOrigin = true,
        Closure $filterCallback = null,
        $componentName = 'storage',
        $showProcess = true,
        $logFile = null
    )
    {
        self::log("Начало миграции файловв хранилище '$destinationStorageName'.\n", $showProcess, $logFile);
        if ($deleteFromOrigin) {
            self::log("Файлы из предыдущего хранилища БУДУТ удаляться.\n", $showProcess, $logFile);
        } else {
            self::log("Файлы из предыдущего хранилища НЕ будут удаляться.\n", $showProcess, $logFile);
        }
        if ($logFile && is_writable($logFile)) {
            self::log("Процесс миграции БУДЕТ записываться в файл '$logFile'.\n", $showProcess, $logFile);
        } else {
            self::log("Процесс миграции НЕ будет записываться в файл.\n", $showProcess, $logFile);
        }
        if ($filterCallback) {
            self::log("Имеется фильтр моделей.\n", $showProcess, $logFile);
        }
        /* @var \snewer\storage\StorageManager $storageManager */
        /* @var ActiveRecord $model */
        $storageManager = Yii::$app->$componentName;
        $destinationStorage = $storageManager->getStorageByName($destinationStorageName);
        foreach ($models as $model) {
            if (!$model instanceof ActiveRecord) {
                self::log("Передана не ActiveRecord модель.\n\n", $showProcess, $logFile);
                continue;
            }
            if ($filterCallback && !$filterCallback($model)) {
                self::log("Модель с id = '{$model->id}' отфильтрована.\n\n", $showProcess, $logFile);
                continue;
            }
            self::log("Начата обработка модели с id = '{$model->id}'\n", $showProcess, $logFile);
            $path = $model->$pathAttributeName;
            $storageId = $model->$storageIdAttributeName;
            if ($path && $storageId) {
                $storage = $storageManager->getStorageById($storageId);
                $fileSource = $storage->getSource($path);
                $fileExtension = array_pop(explode('.', $path));
                $newPath = $destinationStorage->upload($fileSource, $fileExtension);
                self::log("Найдет файл '$path' в хранилище '{$storage->name}'.\n", $showProcess, $logFile);
                if ($newPath) {
                    self::log("Файл успешно перемещен в хранилище '$destinationStorageName' по адресу '$newPath'\n", $showProcess, $logFile);
                    $model->$storageIdAttributeName = $destinationStorage->id;
                    $model->$pathAttributeName = $newPath;
                    if ($model->save()) {
                        self::log("Модель успешно обновлена.\n", $showProcess, $logFile);
                        if ($deleteFromOrigin) {
                            if ($storage->delete($path)) {
                                self::log("Файл '$path' успешно удален из предыдущего хранилища '{$storage->name}'.\n", $showProcess, $logFile);
                            } else {
                                self::log("Не удалось удалить файл '$path' из предыдущего хранилища '{$storage->name}'.\n", $showProcess, $logFile);
                            }
                        }
                    } else {
                        self::log("Не удалось обновить модель.\n", $showProcess, $logFile);
                    }
                } else {
                    self::log("Переместить файл не удалось.\n", $showProcess, $logFile);
                }
            } else {
                self::log("У модели не установлен идентификатор хранилища или путь до файла в нем.\n", $showProcess, $logFile);
            }
            self::log("\n", $showProcess, $logFile);
        }
        self::log("Миграция файлов завершена.\n", $showProcess, $logFile);
        self::log("\n", $showProcess, $logFile);
    }

}