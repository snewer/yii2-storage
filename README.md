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

Компонент имеет только одно свойство `buckets`, в котором
необходимо указать массив конфигураций хранилищ.

**Важно!** Ключами массива являются названия хранилищ, по которым
в дальнейшем получается экземпляр хранилища.

Пример подключения компонента в проект:
```php
[
    // ...
    'components' => [
        //...
        'storage' => [
            'class' => 'snewer\storage\StorageManager',
            'buckets' => []
        ],
        //...
    ],
    // ...
]
```
\
Под хранилищем понимается реализация интерфейса
абстрактного класса `snewer\storage\AbstractBucket` позволяющая:

- загрузить файл
- получить содержимое файла
- удалить файл
- получить web ссылку на файл

из какой-либо системы *(например, файловой)*
или какого-либо сервиса *(например, Amazon AWS)*.

Таким образом, для реализации хранилища необходимо
унаследоваться от абстрактного класса
```php
snewer\storage\AbstractBucket
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
            <td>basePath</td>
            <td>string</td>
            <td>Да</td>
            <td>Нет</td>
            <td>Папка в файловой системе, куда будут загружаться файлы.</td>
        </tr>
        <tr valign="top">
             <td>baseUrl</td>
             <td>string&nbsp;|&nbsp;array</td>
             <td>Нет</td>
             <td>Нет</td>
             <td>
             Url до папки загрузок. Можно указать массив из нескольких путей.
             Тогда для каждого файла будет равномерно и однозачно выбран
             один из путей. *
             </td>
        </tr>
        <tr valign="top">
             <td>depth</td>
             <td>int</td>
             <td>Нет</td>
             <td>3</td>
             <td>Количество подпапок, создаваемое в загрузочной директории.</td>
        </tr>
</table>

*\* Браузеры имеют лимит на одновременное подключение к серверу.
Для преодоления лимита можно использовать различные домены,
указывающие на один и тот же каталог.*

\
Пример настройки компонента с использованием хранилищ:
```php
[
    // ...
    'components' => [
        //...
        'storage' => [
            'class' => 'snewer\storage\StorageManager',
            'buckets' => [
                'images' => [
                    'class' => 'snewer\storage\drivers\FileSystemDriver',
                    'basePath' => '@frontend/web/uploads/images/',
                    'baseUrl' => '@web/uploads/images/',
                    'depth' => 4
                ],
                'documents' => [
                    'class' => 'snewer\storage\drivers\FileSystemDriver',
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
    $path =  Yii::$app->storage->upload('images', $imageBinary, 'jpg');
    $model = new self;
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
    return Yii::$app->storage->getUrl('images', $this->path);
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
    $path = Yii::$app->storage->images->upload($imageBinary, 'jpg');
    $model = new self;
    $model->path = $path;
    $model->save();
    return $model;
}

public function getUrl()
{
    return Yii::$app->storage->images->getUrl($this->path);
}
```
\
Стоит заметить, что реализация метода `getUrl`
не зависит от названия хранилища. То есть в рамках одной и той же
модели можно организовать сложную логику хранения файлов в различных
хранилищах и легким управлением ими.