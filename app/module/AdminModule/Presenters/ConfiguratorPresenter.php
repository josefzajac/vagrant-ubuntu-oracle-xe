<?php
namespace App\AdminModule\Presenters;

use App\Model\BaseModel;
use App\Model\Filter\ModelFilter;
use App\Model\ModelStorage;
use Nette\Utils\Strings as String;

class ConfiguratorPresenter extends AdminPresenter
{
    public function actionCreateGrid($model, $id)
    {
        $response = array('success' => true);
        $readParams = array();
        // is authenticated

        $object = ModelStorage::getModelByModelClass($model);

        $response    ['columns'] = array();
        $response    ['fields'] = array();
        $response    ['conditions'] = array();

        $panels = array();
        foreach ($object->getColumns() as $column) {
            if ($column->grid()) {
                $response['columns'][] = array
                (
                    'id' => $column->getName(),
                    'header' => $column->getLabel(),
                    'align' => 'left',
                    'dataIndex' => $column->getName(),
                    'width' => $column->getGridLenght(),
                    'sortable' => true,
                    'filterable' => true
                );

                $response['fields'][] = $column->getName();
            }

            if ($value = $this->getParameter($column->getName(), $this->getRequest()->getPost($column->getName()))) {
                $readParams[$column->getName()] = $value;
            }
        }

        $response['proxy']['readUrl'] = $this->link('Proxy:read', array_merge(array('model' => $model), $readParams));
        $response['proxy']['writeUrl'] = $this->link('Proxy:write', array('model' => $model));
        $response['proxy']['deleteUrl'] = $this->link('Proxy:delete', array('model' => $model));

        echo json_encode($response);
        $this->terminate();
    }

    public function actionCreateForm($model, $id)
    {
        // is authenticated

        $response = ['success' => true, 'columns' => [], 'grids' => []];
        $panels = [];


        $object = ModelStorage::getModelByModelClass($model);

        $data = false;
        if (!is_null($id)) {
            $data = $object->find((new ModelFilter(ModelFilter::FetchSingle))->addCondition($object->idColumn, '=', $id));
        }

        foreach ($object->getColumns() as $column) {
            /** @var \App\Model\Column\Column $column */
            if (!$column->getFieldType())
                continue;

            $item = array(
                'id' => $object->table . '_' . $column->getName() . '_' . $id,
                'name' => $object->table . '_' . $column->getName(),
                'fieldLabel' => $column->getLabel(),
                'allowBlank' => !$column->getIsRequired(),
                'value' => $data && array_key_exists($column->getName(), $data) ? $data[$column->getName(
                )] : $this->getParameter($column->getName(), $column->getDefault())
            );

            switch (String::lower($column->getFieldType())) {
                case 'grid'        :
                    if (!is_null($id)) {
                        $response['grids'][]
                            = array(
                            'text' => $column->getLabel(),
                            'id' => $object->table . '_' . $column->getName() . '_gridApp',
                            'app' => $column->getConstraint()->model,
                            'renderTo' => $object->table . '_' . $column->getName(),
                            'condition' => array($column->getConstraint()->id => $id)
                        );
                    }
                    break;

                case 'wysiwyg'     :
                    $item['xtype'] = 'htmleditor';
                    $item['height'] = 200;

                    break;

                case 'datepicker':
                    $item['xtype'] = 'datefield';
                    $item['format'] = BaseModel::DATE_FORMAT;

                    break;

                case 'longtext':
                    $item['xtype'] = 'textarea';
                    break;

                case 'checkbox':
                    $item['xtype'] = 'checkbox';
                    $item['checked'] = $data && array_key_exists($column->getName(), $data) ? $data[$column->getName(
                    )] == 1 ? true : false : false;
                    break;

                case 'select':
                    $values = array();
                    if (!is_null($constraint = $column->getConstraint())) {
                        $columnDataFilter = new ModelFilter();
                        if (($condition = @$column->getConstraint()->condition) && !is_null(
                                $constraintId = $this->getParam($condition->source->param)
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
                        if (@$constraint->groupby)
                            $columnDataFilter->addGroupBy($constraint->groupby);

                        $modelObject = ModelStorage::getModelByModelClass($constraint->model);
                        foreach ($modelObject->findForSelect(
                                     $constraint->key,
                                     $constraint->label,
                                     $columnDataFilter
                                 ) as $key => $value)
                            $values[] = array($key, $value . ' [' . $key . ']');

                    } else
                        foreach (explode(',', $column->getLenght()) as $value)
                            $values[] = array($value, $value);

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
                    $panels[String::webalize($column->getPanel())] = array(
                        'id' => $object->table . '_' . $column->getName() . '_panel',
                        'title' => $column->getPanel(),
                        'layout' => 'form',
                        'autoHeight' => true,
                        'items' => array()

                    );
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
            } else
                $response['columns'][] = $item;
        }

        foreach ($panels as $panel)
            $tabItems[] = $panel;

        if (count($panels) > 0)
            $response['columns'][]
                = array(
                'xtype' => 'tabpanel',
                'defaults' => array('bodyStyle' => 'padding:10px'),
                'enableTabScroll' => true,
                'activeTab' => 0,
                'layoutOnTabChange' => true,
                'items' => $tabItems
            );


        $response['proxy'] = $this->link('Proxy:write', array('model' => $model, 'id' => $id));

        echo json_encode($response);
        $this->terminate();
    }
}
