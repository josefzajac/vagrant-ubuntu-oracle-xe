<?php
namespace App\AdminModule\Presenters;

use App\Model\BaseModel;
use App\Model\Column\DateColumn;
use App\Model\Filter\ModelFilter;
use App\Model\ModelStorage;
use Nette\Utils\Strings as String;

class ConfiguratorPresenter extends AdminPresenter
{
    public function actionCreateGrid($model, $id)
    {
        $modelObject = ModelStorage::getModelByModelClass($model);

        $responseData = ['success' => true, 'modelObject' => $modelObject, 'model' => $model];
        $responseData['columns']    = [];
        $responseData['fields']     = [];
        $responseData['conditions'] = [];
        $readParams = [];

        foreach ($modelObject->getColumns() as $column) {
            if ($column->grid()) {
                $responseData['columns'][] = array
                (
                    'id' => $column->getName(),
                    'header' => $column->getLabel(),
                    'align' => 'left',
                    'dataIndex' => $column->getName(),
                    'width' => $column->getGridLenght(),
                    'sortable' => true,
                    'filterable' => true
                );

                $responseData['fields'][] = $column->getName();
            }

            if ($value = $this->getParameter($column->getName(), $this->getRequest()->getPost($column->getName()))) {
                $readParams[$column->getName()] = $value;
            }
        }

        $responseData['proxy']['readUrl'] = $this->link('Proxy:read', array_merge(['model' => $model], $readParams));
        $responseData['proxy']['writeUrl'] = $this->link('Proxy:write', ['model' => $model]);
        $responseData['proxy']['deleteUrl'] = $this->link('Proxy:delete', ['model' => $model]);

        echo json_encode($responseData);
        $this->terminate();
    }

    public function actionCreateForm($model, $id, $returnType = 'JSON')
    {
        $modelObject = ModelStorage::getModelByModelClass($model);

        $responseData = ['success' => true, 'columns' => [], 'grids' => [], 'modelObject' => $modelObject, 'model' => $model];
        $panels = [];


        $data = false;
        if (!is_null($id)) {
            $data = $modelObject->find(
                (new ModelFilter(ModelFilter::FetchSingle))->addCondition($modelObject->idColumn, '=', $id)
            );
        }

        foreach ($modelObject->getColumns() as $column) {
            /** @var \App\Model\Column\Column $column */
            if (!$column->getFieldType()) {
                continue;
            }

            $item = [
                'id' => $modelObject->table . '_' . $column->getName() . '_' . $id,
                'name' => $modelObject->table . '_' . $column->getName(),
                'fieldLabel' => $column->getLabel(),
                'allowBlank' => !$column->getIsRequired(),
                'xtype' => 'text',
                'value' => $data && array_key_exists($column->getName(), $data) ? $data[$column->getName()] : $this->getParameter($column->getName(), $column->getDefault())
            ];

            switch (String::lower($column->getFieldType())) {
                case 'grid'        :
                    if (!is_null($id)) {
                        $responseData['grids'][] = [
                            'text' => $column->getLabel(),
                            'id' => $modelObject->table . '_' . $column->getName() . '_gridApp',
                            'app' => $column->getConstraint()->model,
                            'renderTo' => $modelObject->table . '_' . $column->getName(),
                            'condition' => [$column->getConstraint()->id => $id],
                        ];
                    }
                    break;

                case 'wysiwyg'     :
                    $item['xtype'] = 'htmleditor';
                    $item['height'] = 200;
                    break;

                case 'datepicker':
                    $item['xtype'] = 'datefield';
                    $item['format'] = DateColumn::DATE_FORMAT;
                    break;

                case 'longtext':
                    $item['xtype'] = 'textarea';
                    break;

                case 'checkbox':
                    $item['xtype'] = 'checkbox';
                    $item['checked'] = $data && array_key_exists($column->getName(), $data) ? $data[$column->getName()] == 1 ? true : false : false;
                    break;

                case 'select':
                    $values = [];
                    if (!is_null($constraint = $column->getConstraint())) {
                        $columnDataFilter = new ModelFilter();
                        if (($condition = @$column->getConstraint()->condition) && !is_null(
                                $constraintId = $this->getParameter($condition->source->param)
                            )
                        ) {
                            $conObject = ModelStorage::getModelByModelClass($condition->source->model);
                            $conFilter = new ModelFilter();
                            $conFilter->addCondition(
                                $condition->source->wherecolumn ? $condition->source->wherecolumn : 'id',
                                '=',
                                $constraintId
                            );
                            $conFilter->setFetchType('SINGLE');
                            $conData = $conObject->find($conFilter);

                            $columnDataFilter->addCondition(
                                $condition->target,
                                '=',
                                Arrays::getValueByIndexName($conData, $condition->source->column, 0)
                            );

                        }
                        if (@$constraint->groupby) {
                            $columnDataFilter->addGroupBy($constraint->groupby);
                        }

                        $modelObject = ModelStorage::getModelByModelClass($constraint->model);
                        foreach ($modelObject->findForSelect(
                                     $constraint->key,
                                     $constraint->label,
                                     $columnDataFilter
                                 ) as $key => $value) {
                            $values[] = [$key, $value . ' [' . $key . ']'];
                        }

                    } else {
                        foreach (explode(',', $column->getLenght()) as $value) {
                            $values[] = [$value, $value];
                        }
                    }

                    $item['typeAhead'] = true;
                    $item['xtype'] = 'combo';
                    $item['triggerAction'] = 'all';
                    //    $item['readOnly']            = true;
                    $item['store'] = $values;

                    /** when query set to filter this
                    if($item['value'])
                    {
                    $item['hideTrigger']        = true;
                    $item['triggerAction']        = 'query';
                    $item['editable']            = false;
                    }
                     **/

                    break;

                default:
                    $item['xtype'] = 'textfield';
                    break;
            }

            if ($column->getPanel()) {
                if (!array_key_exists(String::webalize($column->getPanel()), $panels)) {
                    $panels[String::webalize($column->getPanel())] = [
                        'id' => $modelObject->table . '_' . $column->getName() . '_panel',
                        'title' => $column->getPanel(),
                        'layout' => 'form',
                        'autoHeight' => true,
                        'items' => []
                    ];
                }

                if (String::lower($column->getFieldType()) == 'grid') {
                    $panels[String::webalize($column->getPanel())]['layout'] = 'fit';
                    $panels[String::webalize($column->getPanel())]['forceLayout'] = true;
                    $panels[String::webalize($column->getPanel())]['bodyStyle'] = 'padding: 0px; margin: 0px;';
                }

                if (!is_null($item)) {
                    $item['anchor'] = '95%';
                    $panels[String::webalize($column->getPanel())]['items'][]
                        = $item;
                }
            } else {
                $responseData['columns'][] = $item;
            }
        }

        foreach ($panels as $panel) {
            $tabItems[] = $panel;
        }

//        if (count($panels) > 0) {
//            $responseData['columns'][] = [
//                'fieldLabel' => 'Tab1::',
//                'xtype' => 'tabpanel',
//                'defaults' => ['bodyStyle' => 'padding:10px'],
//                'enableTabScroll' => true,
//                'activeTab' => 0,
//                'layoutOnTabChange' => true,
//                'items' => $tabItems,
//            ];
//        }

        return $responseData;
    }

    protected function returnArray($returnType)
    {
        return $returnType == 'ARRAY';
    }
}
