<?php
/**
 * Файл является частью проекта `yii2-categorized` https://validvalue.ru/yii2-categorized
 *
 * @copyright Copyright (c) 2017 Pavel Aleksandrov <inblank@yandex.ru>
 * @license https://validvalue.ru/yii2-categorized/license
 */

namespace inblank\categorized;

use yii;
use yii\base\Application;
use yii\i18n\PhpMessageSource;

class Bootstrap implements yii\base\BootstrapInterface
{

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        if (!isset($app->get('i18n')->translations['categorized*'])) {
            $app->get('i18n')->translations['categorized*'] = [
                'class' => PhpMessageSource::className(),
                'basePath' => __DIR__ . '/messages',
            ];
        }
    }
}
