<?php

/**
 * @author Josef Zajac <josef.zajac@gmail.dom>
 *
 * All Rights Reserved
 *
 */

namespace App\Model;

use App\Model\Column\Column;
use App\Model\Column\Factory;
use App\Model\Filter\IModelFilter;
use App\Model\Filter\ModelFilter;
use Arrays;
use Collections\ColumnCollection;
use Dibi\Fluent;

class BaseModel
{
    public $table;
    public $label;

    public $modelsPaths = [
        '../app/config/models/',
    ];

    /** @var \Dibi\Connection @inject */
    public $db;

    public $idColumn = 'id';

    /** @var \Collections\ColumnCollection */
    protected $columns;

    /** @var \App\Model\Filter\ModelFilter|null  */
    protected $generalFilter;

    /** @var array */
    protected    $serializable = [];

    /**
     * Constructor
     * @param string table - DB table name
     * @param bool baseModel -
     */
    public function __construct($table, $generalFilter = null)
    {
        $this->columns = new ColumnCollection();
        if ($generalFilter) {
            $this->generalFilter = $generalFilter;
        }
        $this->table = $table;

        foreach ($this->modelsPaths as $path) {
            if (@file_exists($file = $path . $table . '.xml')) {
                $this->configureSelf($file);
                break;
            }
        }
    }

    /**
     * Self configuration
     * @param string file - filename
     */
    public function configureSelf($file)
    {
        $xml = @simplexml_load_file($file);

        $this->label = (string) $xml->label;
        foreach ($xml->column as $column) {
            $baseColumn = $this->addColumn(
                Factory::create($column['type'])->setValues(
                    (string)$column['label'],
                    (string)$column['name'],
                    (string)$column['type'],
                    (string)$column['default'],
                    (string)$column['field'],
                    $column['required'] == 'true',
                    (string)$column['regexp'],
                    (string)$column['msg'],
                    $column['serialize'] == 'true',
                    (string)$column['renderer']
                )
            );

            if (isset($column['is_id']) && $column['is_id'] == 'true') {
                $this->idColumn = (string)$column['name'];
            }

            if ($column['serialize'] == "true") {
                $this->serializable[] = (string)$column['name'];
            }

            $baseColumn->grid($column['grid'] == 'true', (int)$column['gridlenght']);
            $baseColumn->setPanel((string)$column['panel']);

            if ($column['constraint']) {
                $baseColumn->setConstraint(json_decode(preg_replace('|[\']|', '"', (string)$column['constraint'])));
            }
            if ($column['extra']) {
                $baseColumn->setExtra($column['extra']);
            }
        }
    }

    // -------------------------------------------------------------------------

    /**                    STRUCTURE                        **/

    /**
     * @param Column $column
     * @return Column
     */
    public function addColumn(Column $column)
    {
        return $this->columns[$column->getName()] = $column;
    }

    // -------------------------------------------------------------------------

    /**
     * @return ColumnCollection
     */
    public function getColumns()
    {
        return $this->columns;
    }

    // -------------------------------------------------------------------------

    /**
     * @param $name
     * @return Column
     * @throws \Exception
     */
    public function getColumn($name)
    {
        if (!array_key_exists($name, $this->columns)) {
            throw new \Exception;
        }

        return $this->columns[$name];
    }

    // -------------------------------------------------------------------------

    /**                MANIPULATION                    **/

    public function insert($data)
    {
        $dataCopy = $data;
        // serialize values if needed
        foreach ($dataCopy as $k => $value) {
            if (in_array($k, $this->serializable)) {
                $dataCopy[$k] = serialize($value);
            }
        }
        if ($data instanceof ModelFilter) {
            return;
        }
        try {
            dibi::insert($this->table, $data)->execute();
            return dibi::insertId();
        } catch (Exception $e) {
            throw new DibiException($e->getMessage(), $e->getCode());
        }
    }

    // -------------------------------------------------------------------------

    public function getTotalRecords(IModelFilter $filter = null)
    {
        $select = $this->db->select('COUNT(*)')->from($this->table);

        $this->applyConditions($select, $filter);

        return (int)$select->fetchSingle();
    }

    // -------------------------------------------------------------------------

    public function findForSelect($keyColumn, $labelColumn, $filter = null)
    {
        $values = $this->find($filter, $keyColumn . ', ' . $labelColumn);
        return $this->interceptForSelect($keyColumn, $labelColumn, $values);
    }

    // -------------------------------------------------------------------------

    public function find(IModelFilter $filter, $results = '*')
    {
        $select = $this->db->select($results)->from($this->table);

        $this->applyConditions($select, $filter);
        foreach ($filter->getGroupBy() as $column => $value)
            $select->groupBy($column);
        foreach ($filter->getSortBy() as $column => $direction)
            $select->orderBy(($column ? '`' . $column . '`' : ''), $direction);
        if (Arrays::existsValueAtIndexName($filter->getLimit(), 'firstIndex') &&
            Arrays::existsValueAtIndexName($filter->getLimit(), 'limit')
        ) {
            $select->offset(Arrays::getValueByIndexName($filter->getLimit(), 'firstIndex'));
            $select->limit(Arrays::getValueByIndexName($filter->getLimit(), 'limit'));
        }

        if ($filter->getFetchType() == 'SINGLE') {
            if (!$result = $select->fetch())
                return false;
            // unserialize values if needed
            foreach ($this->serializable as $v) {
                if ($this->isSerialized($result->$v)) {
                    $result->$v = unserialize($result->$v);
                }
            }
            
            return $this->intercept($result);
        }

        // unserialize values if needed
        if ($result = $select->fetchAll()) {
            foreach ($result as $k => $v) {
                foreach ($v as $fieldName => $fieldValue) {
                    if (in_array($fieldName, $this->serializable) && $this->isSerialized($fieldValue)) {
                        $result[$k][$fieldName] = unserialize($fieldValue);
                    }
                }
            }
        }
        
        return $this->intercept($result);
    }

    // -------------------------------------------------------------------------

    public function update(IModelFilter $filter, $data)
    {
        $dataCopy = $data;
        // serialize values if needed
        foreach ($dataCopy as $k => $value) {
            if (in_array($k, $this->serializable)) {
                $dataCopy[$k] = serialize($value);
            }
        }
        $update = dibi::update($this->table, $dataCopy);
        $this->applyConditions($update, $filter);

        try {
            $update->execute();
        } catch (Exception $e) {
            throw new DibiException($e->getMessage(), $e->getCode());
        }

        return true;
    }

    // -------------------------------------------------------------------------

    /**
     * Delete data by filter
     *
     * @param $filter    IFilter
     *
     * @return bool
     */
    public function delete(IModelFilter $filter)
    {
        $delete = dibi::delete($this->table);
        $this->applyConditions($delete, $filter);
        try {
            $delete->execute();
        } catch (Exception $e) {
            throw new DibiException($e->getMessage(), $e->getCode());
        }

        return true;
    }

    // -------------------------------------------------------------------------

    /**    SUPPORT FUNCTIONS **/

    /**
     * sets ids as array keys
     *
     * @param    array [$data]
     * @return    array [indexedData]
     */
    protected function intercept($data)
    {
        $single = false;
        // pokud filtr je SINGLE, chci a vím, že bude 1 položka
        if (!is_array($data)) {
            $data = [$data];
            $single = true;
        }
        $indexedData = [];
        if (isset(Arrays::getValueByIndex($data, 0)->id))
            foreach ($data as $item) {
                $indexedData[$item['id']] = $item;
            }
        else
            $indexedData = $data;
        if ($single)
            return Arrays::getValueByIndex($indexedData, 0);

        return $indexedData;
    }

    // -------------------------------------------------------------------------

    protected function interceptForSelect($keyColumn, $labelColumn, $values)
    {
        $keyValues = [];

        $matches = preg_split('| as |', $labelColumn);
        if (count($matches) > 1)
            $labelColumn = $matches[count($matches) - 1];

        foreach ($values as $key => $value)
            $keyValues[$value[$keyColumn]] = $value[$labelColumn];

        return $keyValues;
    }

    // -------------------------------------------------------------------------

    /**
     * sets ids as array keys if key is not exists
     *
     * @param    array [$data]
     * @return    array [indexedData]
     */
    protected function interceptUnique($data)
    {
        $indexedData = [];
        foreach ($data as $item) {
            if (!array_key_exists($item['id'], $indexedData))
                $indexedData[$item['id']] = $item;
        }

        return $indexedData;
    }

    // -------------------------------------------------------------------------

    /**
     * apply conditions on command
     * @param DibiDataResource    [$command]
     * @param IModelFilter        [$filter]
     * @return void
     */
    protected function applyConditions(\Dibi\Fluent $command, IModelFilter $filter = null)
    {
        if ($this->generalFilter && !$filter->generalUsed()) {
            $filter->setGeneral($this->generalFilter);
        }

        foreach ($filter->getConditions() as $column => $condition) {
            // if column is serialized you cannot search in it
            if (in_array(\Arrays::getValueByIndexName($condition, 'column'), $this->serializable)) {
                $this->setComment(
                    sprintf(
                        "Warning: column `%s` is serialized you cannot search in it",
                        Arrays::getValueByIndexName($condition, 'column')
                    )
                );
                continue;
            }

            if (!is_null(Arrays::getValueByIndexName($condition, 'value'))) {
                if (Arrays::getValueByIndexName($condition, 'type') == ModelFilter::ConditionIn) {
                    $command->where(
                        '%n IN %in',
                        Arrays::getValueByIndexName($condition, 'column'),
                        (array)Arrays::getValueByIndexName($condition, 'value')
                    );
                } elseif (preg_match('|(DATE_FORMAT)|', Arrays::getValueByIndexName($condition, 'value'))) {
                    $command->where(
                        '%n %sql %sql',
                        Arrays::getValueByIndexName($condition, 'column'),
                        Arrays::getValueByIndexName($condition, 'type', '='),
                        Arrays::getValueByIndexName($condition, 'value')
                    );
                } else

                    $command->where(
                        '%n %sql %s',
                        Arrays::getValueByIndexName($condition, 'column'),
                        Arrays::getValueByIndexName($condition, 'type', '='),
                        Arrays::getValueByIndexName($condition, 'value')
                    );
            } else
                $command->where(
                    '%n%sql',
                    Arrays::getValueByIndexName($condition, 'column'),
                    Arrays::getValueByIndexName($condition, 'type', ' IS NULL')
                );
        }
    }

    // -------------------------------------------------------------------------

    /**
     * intercept and save data [post,files]
     * id        = $_get[id]
     * data        = $_post / $_get
     *
     * @return bool
     */
    protected function getRequestData($key = null)
    {
        $requestData = [
            'post' => Environment::getApplication()->getPresenter()->getRequest()->getPost(),
            'files' => Environment::getApplication()->getPresenter()->getRequest()->getFiles(),
            'id' => Environment::getApplication()->getPresenter()->getParam('id', false)
        ];
        if (!is_null($key) && in_array($key, array('post', 'files', 'id'))) {
            return $requestData[$key];
        }

        return $requestData;
    }

    // -------------------------------------------------------------------------
    /*
     * $notToSave = array('table_column'=>$value);  Ex. array('order_sent'=>1);
     * $addToSave = array('table_column'=>$value);  Ex. array('order_sent'=>1);
     *
     */

    public function interceptAndSave($notToSave = [], $addToSave = [], $callback = null, $inputFilter = null)
    {
        $post = Arrays::getValueByIndexName($this->getRequestData(), 'post');
        $files = Arrays::getValueByIndexName($this->getRequestData(), 'files');
        $id = is_null($inputFilter) ? Arrays::getValueByIndexName($this->getRequestData(), 'id') : $inputFilter->getId(
        );

        $data = [];

        if (count($post) || count($files)) {
            foreach ($this->getColumns() as $column) {
                $fieldType = $column->getFieldType();

                if (empty($fieldType)
                    || (!Arrays::existsValueAtIndexName($post, $this->table . '_' . $column->getName())
                        && (!Arrays::existsValueAtIndexName($files, $this->table . '_' . $column->getName())
                            && String::lower($fieldType) != 'checkbox'
                        ))
                ) {
                    continue;
                }
                $value = '';
                switch (String::lower($fieldType)) {
                    case 'checkbox':
                        $value = array_key_exists(
                            $key = $this->table . '_' . $column->getName(),
                            $post
                        ) ? ($post[$key] == 'on' ? 1 : 0) : 0;
                        break;

                    case 'select':
                        if (!array_key_exists($this->table . '_' . $column->getName(), $post)) {
                            break;
                        }
                        $postValue = $post[$this->table . '_' . $column->getName()];

                        if (String::lower($column->getType()) == 'enum') {
                            $value = $postValue;
                        } else {
                            $matches = [];
                            preg_match('/(\[([\w\-\/]+)\]$)/', $postValue, $matches);
                            $value = Arrays::getValueByIndex($matches, 1, null);
                            $value = preg_replace('|\[|', '', $value);
                            $value = preg_replace('|\]|', '', $value);
                        }

                        break;
                    default:
                        if (!array_key_exists($this->table . '_' . $column->getName(), $post)) {
                            break;
                        }
                        $value = $post[$this->table . '_' . $column->getName()];
                        break;
                }

                $data[$column->getName()] = (empty($value) && !preg_match('|\[([\w\-\/]+)\]|', $value)
                    ? (strlen($column->getDefault()) > 0
                        ? $column->getDefault()
                        : ($column->getIsNull() ? null : ''))
                    : $value);
            }
        }

        if ($this->generalFilter)
            foreach ($this->generalFilter->getConditions() as $fCondition)
                if ($fCondition['column'] && isset($fCondition['value']) && trim($fCondition['type']) == '=')
                    $data[$fCondition['column']] = $fCondition['value'];

        foreach ($notToSave as $column) {
            $columnLabel = ltrim($column, $this->table . '_');
            unset($data[$columnLabel]);
        }
        foreach ($addToSave as $column => $value) {
            $columnLabel = substr($column, strlen($this->table . '_'));
            $data[$columnLabel] = $value;
        }

        if (!$id) {
            $result = $this->insert($data);
            if (is_int($result))
                $data['id'] = $result;
            else
                $data['id'] = dibi::insertId();
        } else {
            $filter = new ModelFilter();
            $filter->addCondition('id', '=', $id);
            $result = $this->update($filter, $data);

            $data['id'] = $id;
        }

        if (!is_null($callback)) {
            $this->$callback($data);
        }

        return $data['id'];
    }

    /**
     * returns whether the string is serializedData
     */
    public function isSerialized($str)
    {
        return ($str == serialize(false) || @unserialize($str) !== false);
    }

    public function getGeneralFilter()
    {
        return $this->generalFilter;
    }

    public function objectValueChanged($originObject, $columnLabel, $changedObject)
    {
        if (isset($originObject->$columnLabel) && isset($changedObject->$columnLabel)) {
            if ($originObject->$columnLabel != $changedObject->$columnLabel) {
                
                return true;
            }
        }
        
        return false;
    }
}
