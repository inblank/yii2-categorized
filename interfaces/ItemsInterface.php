<?php
/**
 * Файл является частью проекта `yii2-categorized` https://validvalue.ru/yii2-categorized
 *
 * @copyright Copyright (c) 2017 Pavel Aleksandrov <inblank@yandex.ru>
 * @license https://validvalue.ru/yii2-categorized/license
 */

namespace inblank\categorized\interfaces;

use yii\db\Query;

interface ItemsInterface
{

    /**
     * Установка условия выборки объектов входящих в раздел
     * @param CategoriesInterface $category раздел из которого выбираем
     * Если null, то выбор объектов не вхдящих в разделы
     * @param Query $query запрос для установки условия
     * @return Query
     */
    public static function descendantsCondition(CategoriesInterface $category = null, Query $query = null): Query;

    /**
     * Проверка, что модель является объектом
     * @return mixed
     */
    public function isItem();

}
