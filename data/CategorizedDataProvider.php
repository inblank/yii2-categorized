<?php
/**
 * Файл является частью проекта `yii2-categorized` https://validvalue.ru/yii2-categorized
 *
 * @copyright Copyright (c) 2017 Pavel Aleksandrov <inblank@yandex.ru>
 * @license https://validvalue.ru/yii2-categorized/license
 */

namespace inblank\categorized\data;

use inblank\categorized\interfaces\CategoriesInterface;
use Yii;
use yii\base\InvalidParamException;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;

class CategorizedDataProvider extends \yii\data\BaseDataProvider
{

    /**
     * Задание класса модели разделов.
     * Если строка, то это имя класса модели
     * Массив должен содержать как минимум ключ 'class' с именем модели
     * @var string|array
     */
    public $categoryModel;

    /**
     * Задание класса модели элемента
     * Если строка, то это имя класса модели
     * Массив должен содержать как минимум ключ 'class' с именем модели
     * @var string|array
     */
    public $itemModel;

    /**
     * Раздел из которой выбираем.
     * Если null выбор самого верзнего уровня
     * @var CategoriesInterface|null
     */
    public $parent;

    /**
     * Признак выбора только разделов.
     * true - выбтрать только разделов, false - выбор разделов и элементов
     * @var bool
     */
    public $onlyCategories = false;

    /**
     * Имя поля используемого как ключ модели
     * @var string|\Closure
     */
    public $key;

    /**
     * Сформированный запрос
     * @var Query
     */
    private $query;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        foreach (['categories' => 'categoryModel', 'items' => 'itemModel'] as $type => $attr) {
            if (is_string($this->$attr)) {
                $this->$attr = ['class' => $this->$attr];
            }
            if (!array_key_exists('class', (array)$this->$attr)) {
                throw new InvalidParamException(
                    Yii::t('categorized_main', "Not defined `{model}` class", [
                        'model' => $attr
                    ])
                );
            } else {
                $interface = '\inblank\categorized\interfaces\\' . ucfirst($type) . 'Interface';
                if (!((new $this->$attr['class']) instanceof $interface)) {
                    throw new InvalidParamException(
                        Yii::t('categorized_main', "Class `{class}` must implements `{interface}`", [
                            'class' => $this->$attr['class'],
                            'interface' => $interface
                        ])
                    );
                }
            }
        }
    }

    /**
     * Prepares the data models that will be made available in the current page.
     * @return array the available data models
     */
    protected function prepareModels(): array
    {
        // Разделы
        $this->query = $this->categoryModel['class']::descendantsCondition($this->parent)
            ->from($this->categoryModel['class']::tableName() . ' c')
            ->select([new Expression("'category' as [[type]]"), 'id']);

        /** @var Query $itemQuery */
        if (!$this->onlyCategories) {
            // выбор не только разделов, но и объектов
            $this->query->union(
                $this->itemModel['class']::descendantsCondition($this->parent)
                    ->from($this->itemModel['class']::tableName() . ' i')
                    ->select([new Expression("'item' as [[type]]"), 'id'])
            );
        }

        $pagination = $this->getPagination();
        if ($pagination !== false) {
            // read only a single page
            $this->query = (new Query())->from(['q' => $this->query]);
            $pagination->totalCount = $this->getTotalCount();
            $this->query->offset($pagination->getOffset())->limit($pagination->getLimit());
        }
        $modelsKeys = [
            'category' => [],
            'item' => [],
        ];
        foreach ($this->query->all($this->categoryModel['class']::getDb()) as $data) {
            $modelsKeys[$data['type']][$data['id']] = $data['id'];
        }
        /** @var ActiveQuery $categoryQuery */
        $categoryQuery = $this->categoryModel['class']::find()->where(['id' => $modelsKeys['category']]);
        if (!empty($this->categoryModel['with'])) {
            $categoryQuery->with($this->categoryModel['with']);
        }
        /** @var ActiveQuery $itemQuery */
        $itemQuery = $this->itemModel['class']::find()->where(['id' => $modelsKeys['item']]);
        if (!empty($this->itemModel['with'])) {
            $itemQuery->with($this->itemModel['with']);
        }
        foreach ([
                     'category' => empty($modelsKeys['category']) ? [] : $categoryQuery->all(),
                     'item' => empty($modelsKeys['item']) ? [] : $itemQuery->all()
                 ] as $name => $models) {
            foreach ($models as $model) {
                $modelsKeys[$name][$model->id] = $model;
            }
        }
        return array_merge($modelsKeys['category'], $modelsKeys['item']);
    }

    /**
     * Prepares the keys associated with the currently available data models.
     * @param array $models the available data models
     * @return array the keys
     */
    protected function prepareKeys($models)
    {
        if ($this->key !== null) {
            $keys = [];
            foreach ($models as $model) {
                $keys[] = is_string($this->key) ? $model[$this->key] : call_user_func($this->key, $model);
            }
            return $keys;
        }
        return array_keys($models);
    }

    /**
     * Returns a value indicating the total number of data models in this data provider.
     * @return int total number of data models in this data provider.
     */
    protected function prepareTotalCount()
    {
        return (new Query())->from(['c' => $this->query])->count('*', $this->categoryModel['class']::getDb());
    }
}
