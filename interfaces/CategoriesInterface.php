<?php
/**
 * Файл является частью проекта `yii2-categorized` https://validvalue.ru/yii2-categorized
 *
 * @copyright Copyright (c) 2017 Pavel Aleksandrov <inblank@yandex.ru>
 * @license https://validvalue.ru/yii2-categorized/license
 */

namespace inblank\categorized\interfaces;

interface CategoriesInterface extends ItemsInterface
{
    /**
     * Получение предков раздела
     * @return array
     */
    public function getParents();
}
