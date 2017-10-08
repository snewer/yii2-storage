<?php

namespace snewer\storage\tools;

use Yii;
use Closure;
use yii\db\ActiveRecord;
use yii\base\Object;

/**
 * Класс используется для миграции файлов из одного хранилища в другое
 * для указанных ActiveRecord моделей.
 *
 * Class MigrateTool
 * @package snewer\storage\tools
 */
class MigrateTool extends Object
{

    /**
     * Модели, файлы которых необходимо переместить.
     * @var ActiveRecord[]
     */
    public $models;

    /**
     * Название атрибута модели, в котором хранится относительный путь до файла.
     * @var string
     */
    public $pathAttributeName;

    /**
     * Название атрибута модели, в котором хранится идентификатор хранилища.
     * @var int
     */
    public $storageIdAttributeName;

    /**
     * Название хранилища, в которое будут перемещены файлы.
     * @var string
     */
    public $destinationStorageName;

    /**
     * Нужно ли удалять исходные файлы.
     * @var bool
     */
    public $deleteFromOrigin = true;

    /**
     * Замыкание для фильтрации моделей, файлы которых перемещать не нужно.
     * @var null|Closure
     */
    public $filterCallback;

    /**
     * Название компонента управления хранилищами в рамках проекта.
     * @var string
     */
    public $componentName = 'storage';

    /**
     * Нужно ли валидировать модели при обновлении данных о файле.
     * @var bool
     */
    public $validateModel = true;

    /**
     * Нужно ли выводить информацию о процессе на экран.
     * @var bool
     */
    public $showProcess = true;

    /**
     * Путь до файла, в который будет записываться процесс миграции, если необходимо.
     * @var null|string
     */
    public $logFile;

    /**
     * Метод для логирования и/или вывода сообщений.
     * @param $message
     */
    private function log($message)
    {
        if ($this->showProcess) {
            echo $message;
        }
        if ($this->logFile && is_writable($this->logFile)) {
            file_put_contents($this->logFile, $message, FILE_APPEND);
        }
    }

    /**
     * Метод вызываемый для запуска миграции файлов.
     */
    public function migrate()
    {
        self::log("Начало миграции файловв хранилище '{$this->destinationStorageName}'.\n");
        if ($this->deleteFromOrigin) {
            self::log("Файлы из предыдущего хранилища БУДУТ удаляться.\n");
        } else {
            self::log("Файлы из предыдущего хранилища НЕ будут удаляться.\n");
        }
        if ($this->logFile && is_writable($this->logFile)) {
            self::log("Процесс миграции БУДЕТ записываться в файл '{$this->logFile}'.\n");
        } else {
            self::log("Процесс миграции НЕ будет записываться в файл.\n");
        }
        if ($this->filterCallback && $this->filterCallback instanceof Closure) {
            self::log("Имеется фильтр моделей.\n");
        }
        /* @var \snewer\storage\StorageManager $storageManager */
        /* @var ActiveRecord $model */
        $storageManager = Yii::$app->{$this->componentName};
        $destinationStorage = $storageManager->getStorageByName($this->destinationStorageName);
        foreach ($this->models as $model) {
            if (!$model instanceof ActiveRecord) {
                self::log("Передана не ActiveRecord модель.\n\n");
                continue;
            }
            $filterCallback = $this->filterCallback;
            if ($filterCallback && $filterCallback instanceof Closure && !$filterCallback($model)) {
                self::log("Модель с id = '{$model->primaryKey}' отфильтрована.\n\n");
                continue;
            }
            self::log("Начата обработка модели с id = '{$model->id}'\n");
            $path = $model->{$this->pathAttributeName};
            $storageId = $model->{$this->storageIdAttributeName};
            if ($path && $storageId) {
                $storage = $storageManager->getStorageById($storageId);
                $fileSource = $storage->getSource($path);
                $fileExtension = array_pop(explode('.', $path));
                $newPath = $destinationStorage->upload($fileSource, $fileExtension);
                self::log("Найдет файл '$path' в хранилище '{$storage->name}'.\n");
                if ($newPath) {
                    self::log("Файл успешно перемещен в хранилище '{$this->destinationStorageName}' по адресу '$newPath'\n");
                    $model->{$this->storageIdAttributeName} = $destinationStorage->id;
                    $model->{$this->pathAttributeName} = $newPath;
                    if ($model->save($this->validateModel)) {
                        self::log("Модель успешно обновлена.\n");
                        if ($this->deleteFromOrigin) {
                            if ($storage->delete($path)) {
                                self::log("Файл '$path' успешно удален из предыдущего хранилища '{$storage->name}'.\n");
                            } else {
                                self::log("Не удалось удалить файл '$path' из предыдущего хранилища '{$storage->name}'.\n");
                            }
                        }
                    } else {
                        self::log("Не удалось обновить модель.\n");
                    }
                } else {
                    self::log("Переместить файл не удалось.\n");
                }
            } else {
                self::log("У модели не установлен идентификатор хранилища или путь до файла в нем.\n");
            }
            self::log("\n");
        }
        self::log("Миграция файлов завершена.\n");
        self::log("\n");
    }

}