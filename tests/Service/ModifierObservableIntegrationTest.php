<?php

require_once dirname(__FILE__) . '/../allModifiers.php';
require_once dirname(__FILE__) . '/ModifierObservableBaseTest.php';

// Integration test:
// We test everything again to see if all is still working with all
// provided modifiers applied

/**
 * @runTestsInSeparateProcesses
 */
class ModifierObservableIntegrationTest extends ModifierObservableBaseTest
{
    protected function getModifiers()
    {
        global $allModifiers;

        return $allModifiers;
    }
}

?>
