<?php
namespace App\AdminModule\Presenters;

use App\Model\BaseModel;
use App\Model\Filter\ModelFilter;
use App\Model\ModelStorage;
use Arrays;
use Nette\Utils\Strings as String;

class ProxyPresenter extends ConfiguratorPresenter
{
    public function actionWrite($model)
    {
        $modelObject = ModelStorage::getModelByModelClass($model);
        try {
            echo json_encode(['success' => $modelObject->interceptAndSave()]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        $this->terminate();
    }

    public function actionDelete($model, $id)
    {
        $modelObject = ModelStorage::getModelByModelClass($model);

        $modelFilter = new ModelFilter();
        $modelFilter->id($id);
        try {
            $modelObject->delete($modelFilter);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage(), 'e' => $e->getMessage()]);
        }
        $this->terminate();
    }

    public function actionDetail($model, $id)
    {
        $responseData = $this->actionCreateForm($model, $id, 'ARRAY');

        foreach($responseData['grids'] as $k => $c) {
            $responseData['grids'][$k] = $this->actionRead($c['app'], 'ARRAY', $c['condition']);
        }
//dump($responseData['grids']);die();
        foreach ($responseData as $k => $v)
            $this->template->{$k} = $v;
    }

    public function actionRead($model, $returnType = 'JSON', $condition = [])
    {
        try {
            $this->template->modelObject = $modelObject = ModelStorage::getModelByModelClass($model);
            $modelFilter = new ModelFilter();

            $defaults = new \stdClass;
            $defaults->sort   = $modelObject->idColumn;
            $defaults->dir    = 'DESC';
            $defaults->offset = 0;
            $defaults->limit  = 30;


            $sort   = $this->getParameter('sort', $defaults->sort);
            $dir    = $this->getParameter('dir', $defaults->dir);
            $offset = $this->getParameter('start', $defaults->offset);
            $limit  = $this->getParameter('limit', $defaults->limit);
            if ($page = $this->getParameter('page', 0))
                $offset = ($page -1) * $limit;
            $modelFilter->addOrderBy($sort, $dir);

            $columnData = $jsonData = [];
            foreach ($modelObject->getColumns() as $column) {
                if (($value = $this->getParameter( $column->getName(), $this->getRequest()->getPost($column->getName())))
                    || $value = (isset($condition[$column->getName()]) ? $condition[$column->getName()] : null) ) {
                    $modelFilter->addCondition($column->getName(), '=', $value);
                }
                // load data to translage for grid
                if (!is_null($constraint = $column->getConstraint()) && $column->grid() && !array_key_exists($column->getName(), $columnData)) {
                    $smallFilter = new ModelFilter();
                    if (@$constraint->groupby) {
                        $smallFilter->addGroupBy($constraint->groupby);
                    }
                    $columnData[$column->getName()] =
                        ModelStorage::getModelByModelClass($constraint->model)
                            ->findForSelect($constraint->key, $constraint->label, $smallFilter);
                }
            }

            if ($q = $this->getParameter('query', null)) {
                $modelFilter->addCondition($modelObject->idColumn, 'LIKE', '%' . String::lower($q) . '%');
            }
            $responseData = [
                'totalCount' => $modelObject->getTotalRecords($modelFilter),
                'keys'       => [],
                'items'      => [],
                'model'      => $model,
                'modelObject' => $modelObject,
                'gridParams' => [
                    'sort' => $sort,
                    'dir' => $dir,
                    'limit' => $limit,
                    'start' => $offset,
                    'q' => $q,
                    'totalCount' => 0,
                    'page' => floor($offset / $limit),
                    'pages' => 0,
                ]
            ];
            $responseData['gridParams']['totalCount'] = $responseData['totalCount'];
            $responseData['pages'] = $responseData['gridParams']['pages'] = ceil($responseData['totalCount']/$limit);

            foreach ($modelObject->find($modelFilter->setLimit($offset, $limit)) as $item) {
                foreach ($columnData as $key => $translationRow) {
                    $item[$key] = Arrays::getValueByIndexName($translationRow, $item[$key], $item[$key]);
                }

                $responseData['keys'] = [];
                foreach ($item as $k => $v) {
                    $responseData['keys'][] = $k;
                    if ($v instanceof \DateTime) {
                        $item[$k] = $v->format(BaseModel::DATE_FORMAT);
                    }
                }

                if ($this->returnJson($returnType)) {
                    $item['id'] = $item[$modelObject->idColumn];
                }
                $jsonData[] = $item;
            }

            $responseData['items'] = $jsonData;

            if ($this->returnJson($returnType)) {
                echo $this->getParameter('callback') . '(' .
                    json_encode($responseData) . ')';
            }
        } catch (Exception $e) {
            if ($this->returnJson($returnType)) {
                echo $this->getParameter('callback') . '(' . json_encode([
                    'totalCount' => 1,
                    'items' => [['customer_id' => $e->getMessage()]]]) . ')';
            }
            throw $e;
        }


        if ($this->returnJson($returnType)){
            $this->terminate();
        }
        if ($this->returnArray($returnType)){
            return $responseData;
        }

        foreach ($responseData as $k => $v)
            $this->template->{$k} = $v;
    }

    public function convertQuery($q)
    {
        return strtr($q, [
                'ä' => 'a',
                'Ä' => 'A',
                'á' => 'a',
                'Á' => 'A',
                'â' => 'a',
                'Â' => 'A',
                'č' => 'c',
                'Č' => 'C',
                'ć' => 'c',
                'Ć' => 'C',
                'ď' => 'd',
                'Ď' => 'D',
                'ě' => 'e',
                'Ě' => 'E',
                'é' => 'e',
                'É' => 'E',
                'ë' => 'e',
                'Ë' => 'E',
                'í' => 'i',
                'Í' => 'I',
                'î' => 'i',
                'Î' => 'I',
                'ľ' => 'l',
                'Ľ' => 'L',
                'ĺ' => 'l',
                'Ĺ' => 'L',
                'ń' => 'n',
                'Ń' => 'N',
                'ň' => 'n',
                'Ň' => 'N',
                'ó' => 'o',
                'Ó' => 'O',
                'ö' => 'o',
                'Ö' => 'O',
                'ô' => 'o',
                'Ô' => 'O',
                'ő' => 'o',
                'Ő' => 'O',
                'ř' => 'r',
                'Ř' => 'R',
                'ŕ' => 'r',
                'Ŕ' => 'R',
                'š' => 's',
                'Š' => 'S',
                'ś' => 's',
                'Ś' => 'S',
                'ť' => 't',
                'Ť' => 'T',
                'ú' => 'u',
                'Ú' => 'U',
                'ů' => 'u',
                'Ů' => 'U',
                'ü' => 'u',
                'Ü' => 'U',
                'ù' => 'u',
                'Ù' => 'U',
                'ũ' => 'u',
                'Ũ' => 'U',
                'û' => 'u',
                'Û' => 'U',
                'ý' => 'y',
                'Ý' => 'Y',
                'ž' => 'z',
                'Ž' => 'Z',
                'ź' => 'z',
                'Ź' => 'Z'
            ]);
    }
}
