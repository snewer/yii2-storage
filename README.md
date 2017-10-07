<p align="center">
    <h1 align="center">Storage компонент для Yii2</h1>
</p>

Компонент используется для работы с различными хранилищами файлов.

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
Задача компонента — предоставление интерфейса для реализации хранилища и их использование.

Под хранилищем понимается реализация интерфейса,
которую будем называть _драйвером_, позволяющая:

- загрузить файл
- получить содержимое файла
- удалить файл
- получить web ссылку на файл

из какой-либо системы _(например, файловой)_
или какого-либо сервиса _(например, Amazon AWS)_.

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
которое имеет следующие свойства:
<table>
        <tr>
            <th>Свойство</th>
            <th>Тип</th>
            <th>Описание</th>
        </tr>
        <tr>
            <td>basePath</td>
            <td>string</td>
            <td>Папка в файловой системе, куда будут загружаться файлы.</td>
        </tr>
        <tr>
             <td>baseUrl</td>
             <td>string</td>
             <td>Url до папки загрузок.</td>
        </tr>
        <tr>
             <td>depth</td>
             <td>int</td>
             <td>Количество подпапок, создаваемое в загрузочной директории.</td>
        </tr>
</table>

\
Пример настройки компонента:
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