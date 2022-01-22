<?php

namespace Good\Manners\CollectionCondition;

use Good\Service\Collection;

trait TypeValidator
{
    private function validateComparisonCollectionValue($comparisonValue, $conditionName)
    {
        if (!($comparisonValue instanceof Collection))
        {
            throw new \Exception("Cannot test value of '" . print_r($comparisonValue, true) . "' against " . $conditionName . ": not a collection");
        }
    }
}

?>
