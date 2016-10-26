<?php
namespace App\Model;

use App\Model\Filter\ModelFilter;

class TariffSpace extends BaseModel
{
    public function __construct()
    {
        parent::__construct(
            'TARIFF_SPACE',
            (new ModelFilter())->addCondition('TECHNICAL_STATUS', ModelFilter::ConditionIn, ['A', 'P'])
        );
    }
}
