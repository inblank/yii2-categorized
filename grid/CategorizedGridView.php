<?php
/**
 * Файл является частью проекта `yii2-categorized` https://validvalue.ru/yii2-categorized
 *
 * @copyright Copyright (c) 2017 Pavel Aleksandrov <inblank@yandex.ru>
 * @license https://validvalue.ru/yii2-categorized/license
 */

namespace inblank\categorized\grid;

use inblank\categorized\data\CategorizedDataProvider;
use Yii;
use yii\base\InvalidParamException;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

/**
 * Class CategorizedGridView
 *
 * @property CategorizedDataProvider $dataProvider
 */
class CategorizedGridView extends GridView
{

    /**
     * @var string the layout that determines how different sections of the list view should be organized.
     * The following tokens will be replaced with the corresponding section contents:
     *
     * - `{summary}`: the summary section. See [[renderSummary()]].
     * - `{errors}`: the filter model error summary. See [[renderErrors()]].
     * - `{items}`: the list items. See [[renderItems()]].
     * - `{sorter}`: the sorter. See [[renderSorter()]].
     * - `{pager}`: the pager. See [[renderPager()]].
     * - `{breadcrumbs}`: the category breadcrumbs [[renderBreadcrumbs()]]
     */
    public $layout = "{breadcrumbs}\n{summary}\n{buttons}\n{items}\n{pager}";

    /**
     * URL for breadcrumbs
     * @var string|array
     */
    public $breadcrumbsURL;

    /**
     * Нзаавние самого верхнего уровня разделов для отображения в хлебных крошках
     * @var string
     */
    public $topCategoryName;

    /**
     * URL для действия создания раздела
     * Если не задано будет использовано Yii::$app->controller->id . '/category-create'
     * @var string|array
     */
    public $categoryCreateUrl;

    /**
     * URL для действия создания элемента
     * Если не задано будет использовано Yii::$app->controller->id . '/item-create'
     * @var string|array
     */
    public $itemCreateUrl;

    /**
     * @inheritdoc
     */
    public function renderSection($name)
    {
        switch ($name) {
            case '{breadcrumbs}':
                return $this->renderBreadcrumbs();
            case '{buttons}':
                return $this->renderButtons();
            default:
                return parent::renderSection($name);
        }
    }

    /**
     * Отрисовка хлебных крошек
     */
    public function renderBreadcrumbs()
    {
        $parentCategory = $this->dataProvider->parent;
        $topCategoryName = $this->topCategoryName ?: Yii::t('categorized_main', 'Top');
        if ($parentCategory) {
            if (empty($this->breadcrumbsURL)) {
                $url = [Yii::$app->controller->id . '/' . Yii::$app->controller->action->id];
            } elseif (is_string($this->breadcrumbsURL)) {
                $url = [$this->breadcrumbsURL];
            } else {
                $url = $this->breadcrumbsURL;
            }
            $breadcrumbs = [
                ['label' => $topCategoryName, 'url' => $url]
            ];
            foreach ($parentCategory->getParents() as $cat) {
                $url['parent'] = $cat->id;
                $breadcrumbs[] = ['label' => $cat->name, 'url' => $url];
            }
            $breadcrumbs[] = $parentCategory->name;
        } else {
            $breadcrumbs[] = $topCategoryName;
        }
        echo Breadcrumbs::widget([
            'links' => $breadcrumbs,
            'homeLink' => false
        ]);
    }

    /**
     * Отрисовка кнопок управления над списком
     */
    public function renderButtons()
    {
        $parent = $this->dataProvider->parent ? $this->dataProvider->parent->id : null;
        echo '<div class="btn-toolbar">';
        foreach (['category' => 'primary', 'item' => 'success'] as $name => $btn) {
            $fieldName = $name . 'CreateUrl';
            $url = $this->$fieldName;
            if (empty($url)) {
                $url = [Yii::$app->controller->id . '/' . $name . '-create'];
            } elseif (is_string($url)) {
                $url = [$url];
            }
            $url['parent'] = $parent;
            echo Html::a(
                Yii::t('categorized_main', 'Create ' . ucfirst($name)),
                $url,
                ['class' => 'btn btn-' . $btn]
            );
        }
        echo '</div>';
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!($this->dataProvider instanceof CategorizedDataProvider)) {
            throw new InvalidParamException(
                Yii::t('categorized_main', 'Data provider must be `CategorizedDataProvider`')
            );
        }
        echo '<div class="categorized-grid-view">';
        parent::run();
        echo '</div>';
    }

}
