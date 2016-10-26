<?php
namespace App\Model;

use App\Model\Filter\ModelFilter;

class Product extends BaseModel
{
	public function __construct()
	{
		parent::__construct('PRODUCT',
            (new ModelFilter())->addCondition('TECHNICAL_STATUS', ModelFilter::ConditionIn, ['A', 'P'])
        );
	}
}
