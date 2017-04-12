<?php
/**
 * Файл является частью проекта `yii2-categorized` https://validvalue.ru/yii2-categorized
 *
 * @copyright Copyright (c) 2017 Pavel Aleksandrov <inblank@yandex.ru>
 * @license https://validvalue.ru/yii2-categorized/license
 */

namespace inblank\categorized\grid;

use inblank\categorized\interfaces\CategoriesInterface;
use inblank\categorized\interfaces\ItemsInterface;
use Yii;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Class CategorizedActionColumn
 *
 * @property array|string $controller
 */
class CategorizedActionColumn extends ActionColumn
{
    /**
     * @inheritdoc
     */
    public $template = '{transfer} {update} {delete}';

    /**
     * Initializes the default button rendering callbacks.
     */
    protected function initDefaultButtons()
    {
        $this->initDefaultButton('transfer', 'transfer');
        $this->initDefaultButton('update', 'pencil');
        $this->initDefaultButton('delete', 'trash', [
            'data-method' => 'post',
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function initDefaultButton($name, $iconName, $additionalOptions = [])
    {
        if (!isset($this->buttons[$name]) && strpos($this->template, '{' . $name . '}') !== false) {
            $this->buttons[$name] = function ($url, $model) use ($name, $iconName, $additionalOptions) {
                /** @var CategoriesInterface|ItemsInterface $model */
                switch ($name) {
                    case 'transfer':
                        $title = Yii::t('categorized_main', 'Transfer');
                        break;
                    case 'update':
                        $title = Yii::t('categorized_main', 'Update');
                        break;
                    case 'delete':
                        $title = Yii::t('categorized_main', 'Delete');
                        $additionalOptions['data-confirm'] =
                            Yii::t(
                                'categorized_main',
                                'Are you sure you want to delete this ' .
                                ($model->isItem() ? 'item' : 'category') . '?'
                            );
                        break;
                    default:
                        $title = ucfirst($name);
                }
                $options = array_merge([
                    'title' => $title,
                    'aria-label' => $title,
                    'data-pjax' => '0',
                ], $additionalOptions, $this->buttonOptions);
                $icon = Html::tag('span', '', ['class' => "glyphicon glyphicon-$iconName"]);
                return Html::a($icon, $url, $options);
            };
        }
    }

    /**
     * Creates a URL for the given action and model.
     * This method is called for each button and each row.
     * @param string $action the button name (or action ID)
     * @param \yii\db\ActiveRecord|ItemsInterface|CategoriesInterface $model the data model
     * @param mixed $key the key associated with the data model
     * @param int $index the current row index
     * @return string the created URL
     */
    public function createUrl($action, $model, $key, $index)
    {
        if (is_callable($this->urlCreator)) {
            return call_user_func($this->urlCreator, $action, $model, $key, $index, $this);
        }
        $params = is_array($key) ? $key : ['id' => (string)$model->id];
        $params['model'] = $model->isItem() ? 'item' : 'category';
        if ($action == 'transfer'
            && $this->grid->dataProvider instanceof \inblank\categorized\data\CategorizedDataProvider
            && $this->grid->dataProvider->parent
        ) {
            $params['from'] = $this->grid->dataProvider->parent->id;
        }
        $params[0] = '';
        if (!empty($this->controller)) {
            if (is_string($this->controller)) {
                $params[0] = $this->controller;
            } elseif (array_key_exists($params['model'], (array)$this->controller)) {
                $params[0] = $this->controller[$params['model']];
                unset($params['model']);
            }
            $params[0] .= '/';
        }
        $params[0] .= $action;
        return Url::toRoute($params);
    }
}
