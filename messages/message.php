<?php
/**
 * Файл является частью проекта `yii2-categorized` https://validvalue.ru/yii2-categorized
 *
 * @copyright Copyright (c) 2017 Pavel Aleksandrov <inblank@yandex.ru>
 * @license https://validvalue.ru/yii2-categorized/license
 */

return [
    'sourcePath' => __DIR__ . '/../',
    'messagePath' => __DIR__,
    'languages' => [
        'ru',
    ],
    'translator' => 'Yii::t',
    'sort' => false,
    'overwrite' => true,
    'removeUnused' => false,
    'only' => ['*.php'],
    'except' => [
        '.svn',
        '.git',
        '.gitignore',
        '.gitkeep',
        '.hgignore',
        '.hgkeep',
        '/messages',
        '/tests',
        '/vendor',
    ],
    'format' => 'php',
];
