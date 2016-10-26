<?php
namespace App\Model\Filter;

class ModelFilter implements IModelFilter
{
	protected $sorts		= array();
	protected $groups		= array();
	protected $conditions	= array();
	protected $limit		= array();
	protected $fetchType	= 'ALL';
	protected $actualGroup	= null;

	const FetchAll = 'ALL';
	const FetchSingle = 'SINGLE';
	const ConditionIn = 'IN';

	protected $sqlCalcFundRows = false;
	/**						CONDITIONS						**/


	public static function get($type = 'ALL')
	{
		return new self($type);
	}

	public function __construct($type = 'ALL')
	{
		$this -> setFetchType($type);
		return $this;
	}

	public function addGroupConditions($group, $separator)
	{
		$this -> group[$group] 	= array('group'=>$group, 'separator'=>$separator);
		$this -> actualGroup 	= $this -> group[$group];
		return $this;
	}

	public function endGroupConditions($group)
	{
		$this -> actualGroup 	= null;
		return $this;
	}

	public function __call($name, $args)
	{
		$this -> removeCondition($name);
		$this -> addCondition($name,Arrays::getValueByIndex($args,1,'='),Arrays::getValueByIndex($args,0));
		return $this;
	}

	public function addCondition($column,$type = null, $value = null)
	{
		$this -> conditions[] = array('column'=>$column, 'type'=>$type, 'value'=>$value, 'group' => null);
		return $this;
	}

	public function singleCondition($column,$type,$value = null)
	{
		$this -> conditions = array();
		$this -> conditions[] = array('column'=>$column, 'type'=>$type, 'value'=>$value, 'group' => null);
		return $this;
	}

	public function removeCondition($conditionIndex)
	{
		foreach($this->conditions as $key => $value)
			if($value['column']==$conditionIndex)
				unset($this->conditions[$key]);
		return $this;
	}

	public function clearConditions()
	{
		$this -> conditions = array();
		return $this;
	}

	public function clear()
	{
		$this -> conditions	= array();
		$this -> limit		= array();
		$this -> sorts		= array();
		$this -> groups		= array();
		$this -> rand		= 0;
		return $this;
	}

	public function getConditions()
	{
		return $this -> conditions;
	}

	public function getCondition($column)
	{
		foreach($this->getConditions() as $condition)
			if($condition['column']==$column)
				return $condition['value'];
		return $this;
	}


	/**						SORTS						**/
	public function addSortBy($column, $direction = 'ASC')
	{
		$this -> sorts[$column] = $direction;
		return $this;
	}

	public function removeSortBy($column = null)
	{
		if( ! is_null( $column ) )
			unset($this->sorts[$column]);
		else
			$this->sorts = array();
		return $this;
	}

	public function getSortBy()
	{
		return $this -> sorts;
	}


	/**						SORTS/ORDER						**/
	/** 	addSortBy je spatne pojmenovana, jelikoz ORDERuje query, proto jsou pridany OrderBy funkce volajici SortBy
	**/
	public function addOrderBy($column, $direction = 'ASC')
	{
		$this->addSortBy($column, $direction);
		return $this;
	}
	public function removeOrderBy($column)
	{
		$this->removeSortBy($column);
		return $this;
	}
	public function getOrderBy()
	{
		return $this -> getSortBy();
	}

	/**						GROUP BY					**/
	public function addGroupBy($column)
	{
		$this -> groups[$column] = $column;
		return $this;
	}

	public function removeGroupBy($column)
	{
		unset($this->groups[$column]);
		return $this;
	}

	public function getGroupBy()
	{
		return $this -> groups;
		return $this;
	}

	/* 						LIMITS						**/

	public function setLimit($firstIndex,$limit)
	{
		$this -> limit['firstIndex'] = $firstIndex;
		$this -> limit['limit']		 = $limit;
		return $this;
	}

	public function getLimit()
	{
		return $this -> limit;
	}

	/**					FETCH TYPES						**/

	public function setFetchType($type)
	{
		if(in_array($type,array('ALL','SINGLE')))
			$this -> fetchType = $type;
		return $this;
	}

	/**
	 * @return fetchType [ALL, SINGLE]
	 */
	public function getFetchType()
	{
		return $this -> fetchType;
	}

	public function merge(ModelFilter $filter)
	{
		$this->conditions = array_merge ( $this->conditions , $filter->getConditions() );
		$this->sorts      = array_merge ( $this->sorts , $filter->getSortBy() );
		return $this;
	}

	public function getOrderByClausule()
	{
		$aaa = $this->getSortBy();
		if( $aaa )
		{
			return ' ORDER BY '.implode(' , ', array_map(function($v)use($aaa){ return ' `'.array_search($v,$aaa).'` '.$v;   }, $aaa)  );
		}
		return '';
	}

    public function getWhereClausule($prefix = null)
    {
        $output = [];
        foreach($this->getConditions() as $condition)
        {
            if( !is_null(Arrays::getValueByIndexName($condition,'value')) )
            {
                if( Arrays::getValueByIndexName($condition,'type') == ModelFilter::ConditionIn )
                {
                    $output[] = sprintf(	'%s IN (%s)',
                        $this->prefix($prefix, Arrays::getValueByIndexName($condition,'column')),
                        implode(',', array_map(function($x){
                           return sprintf('"%s"', $x);
                        }, Arrays::getValueByIndexName($condition,'value'))));
                }elseif( preg_match('|(DATE_FORMAT)|', Arrays::getValueByIndexName($condition,'value')) )
                {
                    $output[] = sprintf('`%s` %s %s',
                        $this->prefix($prefix, Arrays::getValueByIndexName($condition,'column')),
                        Arrays::getValueByIndexName($condition,'type','='),
                        Arrays::getValueByIndexName($condition,'value'));
                }else

                    $output[] = sprintf('`%s` %s "%s"',
                        $this->prefix($prefix, Arrays::getValueByIndexName($condition,'column')),
                        Arrays::getValueByIndexName($condition,'type','='),
                        Arrays::getValueByIndexName($condition,'value'));
            }
            else
                $output[] = sprintf('`%s` %s',
                    $this->prefix($prefix, Arrays::getValueByIndexName($condition,'column')),
                    Arrays::getValueByIndexName($condition,'type',' IS NULL'));
        }

        return implode(' AND ', $output);
    }

    private function prefix($prefix, $column)
    {
        if (null === $prefix)
            return $column;

        return sprintf('%s.%s', $prefix, $column);
    }

	public function getId()
	{
		foreach($this->conditions as $c)
		{
			if( $c['column'] == 'id' && $c['type'] == '=' )
			{
				return $c['value'];
			}
		}
		return false;
	}

	public function setSqlCalcFundRows($set = true)
	{
		$this->sqlCalcFundRows = $set;
		return $this;
	}

	public function getSqlCalcFundRows()
	{
		return $this->sqlCalcFundRows;
	}
}
