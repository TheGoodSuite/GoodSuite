<?php

require_once dirname(__FILE__) . '/../allModifiers.php';
require_once dirname(__FILE__) . '/ModifierObservableBaseTest.php';

// Integration test:
// We test everything again to see if all is still working with all
// provided modifiers applied

/**
 * @runTestsInSeparateProcesses
 */
class GoodServiceModifierObservableIntegrationTest extends GoodServiceModifierObservableBaseTest
{
    protected function compile($types, $modifiers = null, $inputFiles = null)
    {
        global $allModifiers;

        if ($modifiers == null)
        {
            parent::compile($types, $allModifiers, $inputFiles);
        }
        else
        {
            parent::compile($types, $modifiers, $inputFiles);
        }
    }
}

?>
