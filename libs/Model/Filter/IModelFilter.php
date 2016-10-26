<?php
namespace App\Model\Filter;

interface IModelFilter
{

    /**                FILTERING                **/

    /**
     * @param $arr array of condition typicaly ['id','=',1] OR ['deleted','=',1] ) )
     * @return void
     */
    public function addCondition($column, $type = null, $value = null);

    /**
     * @param $arr array of condition typicaly array('id', 'delete' ...)
     */
    public function removeCondition($condition);

    /**
     * @return array        | typicaly array('id'=>1, 'delete'=>0, ...)
     */
    public function getConditions();

    /**                    SORTING                    **/
    /**
     * @param $column        | column by
     * @param $direction    | direction (ASC,DESC)
     */
    public function addSortBy($column, $direction = 'ASC');

    /**
     * @return array        | array( array( 'id' => 'ASC' ), array( 'deleted' => 'DESC' ));
     */
    public function getSortBy();

    /**                LIMITING                **/
    /**
     * @param $firstIndex    | 0
     * @param $limit        | 20
     */
    public function setLimit($firstIndex, $limit);

    /**
     * @return array        | array(firstIndex => '0', limit => '20')
     */
    public function getLimit();

    /**                    FETCH TYPE                **/

    /**
     * @param $type        | [ALL=all by filter,SINGLE=single by filter]
     */
    public function setFetchType($type);

    /**
     * @return fetchType    | [ALL=all by filter,SINGLE=single by filter]
     */
    public function getFetchType();

}
