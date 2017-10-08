Установка
---------
Компонент распространяется как [composer](http://getcomposer.org/download/) пакет
и устанавливается командой
```
php composer.phar require snewer/yii2-storage
```
или добавлением
```json
"snewer/yii2-storage": "*"
```
в composer.json файл проекта.

Настройка
---------
Задача компонента — предоставление интерфейса для реализации хранилищ и их использование.

Компонент имеет только одно свойство `storageList`, в котором
необходимо указать массив конфигураций хранилищ.

**Важно!** Ключами массива должны быть целые,
неотрицательные числа *(unsigned integer)*.

Данные ключи используются для хранения информации
о хранилище в численном виде *(например, в базах данных)*.
**Рекомендуем** указывать их явно.

Пример подключения компонента в проект:
```php
[
    // ...
    'components' => [
        //...
        'storage' => [
            'class' => 'snewer\storage\StorageManager',
            'storageList' => []
        ],
        //...
    ],
    // ...
]
```
\
Под хранилищем понимается реализация интерфейса
абстрактного класса `snewer\storage\AbstractStorage` позволяющая:

- загрузить файл
- получить содержимое файла
- удалить файл
- получить web ссылку на файл

из какой-либо системы *(например, файловой)*
или какого-либо сервиса *(например, Amazon AWS)*.

Таким образом, для реализации хранилища необходимо
унаследоваться от абстрактного класса
```php
snewer\storage\AbstractStorage
```

\
"Из коробки" доступен драйвер для локальной файловой системы
```php
snewer\storage\drivers\FileSystemDriver
```
который имеет следующие свойства:

<table>
        <tr>
            <th>Свойство</th>
            <th>Тип</th>
            <th>Обятательное</th>
            <th>Значение по-умолчанию</th>
            <th>Описание</th>
        </tr>
        <tr valign="top">
            <td>name</td>
            <td>string</td>
            <td>Да</td>
            <td>Нет</td>
            <td>Уникальное название хранилища.</td>
        </tr>
        <tr valign="top">
            <td>basePath</td>
            <td>string</td>
            <td>Да</td>
            <td>Нет</td>
            <td>Папка в файловой системе, куда будут загружаться файлы.</td>
        </tr>
        <tr valign="top">
             <td>baseUrl</td>
             <td>string</td>
             <td>Нет</td>
             <td>Нет</td>
             <td>Url до папки загрузок.</td>
        </tr>
        <tr valign="top">
             <td>depth</td>
             <td>int</td>
             <td>Нет</td>
             <td>3</td>
             <td>Количество подпапок, создаваемое в загрузочной директории.</td>
        </tr>
</table>

\
Пример настройки компонента с использованием хранилищ:
```php
[
    // ...
    'components' => [
        //...
        'storage' => [
            'class' => 'snewer\storage\StorageManager',
            'storageList' => [
                1 => [
                    'class' => 'snewer\storage\drivers\FileSystemDriver',
                    'name' => 'images',
                    'basePath' => '@frontend/web/uploads/images/',
                    'baseUrl' => '@web/uploads/images/',
                    'depth' => 4
                ],
                2 => [
                    'class' => 'snewer\storage\drivers\FileSystemDriver',
                    'name' => 'documents',
                    'basePath' => '@frontend/web/uploads/documents/',
                    'baseUrl' => '@web/uploads/documents/',
                    'depth' => 4
                ],
                // ...
            ]
        ],
        //...
    ],
    // ...
]
```

Использование
-------------
После настройки компонента использовать хранилище
можно как через методы компонента, так и обращаясь
непосредственно к объекту хранилища.

Пример реализации методов загрузки изображения
и получения URL ссылки на него в модели
изображения вашего проекта `app\models\Image`:
```php
public static function upload($imageBinary)
{
    // Название хранилища, в которое загружаем изображения.
    $storageName = 'images';
    $storageManager = \Yii::$app->storage;
    $path = $storageManager->upload($storageName, $imageBinary, 'jpg');
    $storageId = $storageManager->getStorageIdByName($storageName);
    $model = new self;
    $model->storage_id = $storageId;
    $model->path = $path;
    $model->save();
    return $model;
}
```
Далее, в той же модели, добавим метод получения ссылки на
изображение:
```php
public function getUrl()
{
    $storageManager = \Yii::$app->storage;
    $storageName = $storageManager->getStorageNameById($this->storage_id);
    return $storageManager->getUrl($storageName, $this->path);
}
```
После чего можно загружать изображения следующим образом:
```php
$image = app\models\Image::upload($imageBinary);
```
и выводить изображение в каком-либо представлении:
```php
<img src="<?= $image->url ?>">
```
\
Пример реализации тех же методов с использованием
объектов хранилищ:
```php
public static function upload($imageBinary)
{
    // Название хранилища, в которое загружаем изображения.
    $storageName = 'images';
    $storage = \Yii::$app->storage->$storageName;
    $path = $storage->upload($imageBinary, 'jpg');
    $model = new self;
    $model->storage_id = $storage->id;
    $model->path = $path;
    $model->save();
    return $model;
}

public function getUrl()
{
    $storage = \Yii::$app->storage->getStorageById($this->storage_id);
    return $storage->getUrl($this->path);
}
```
\
Стоит заметить, что реализация метода `getUrl`
не зависит от названия хранилища. То есть в рамках одной и той же
модели можно организовать сложную логику хранения файлов в различных
хранилищах и легким управлением ими.