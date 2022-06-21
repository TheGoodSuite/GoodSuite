<?php

require_once dirname(__FILE__) . '/../allModifiers.php';
require_once dirname(__FILE__) . '/ModifierStorableBaseTest.php';

// Integration test:
// We test everything again to see if all is still working with all
// provided modifiers applied

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState enabled
 */
class ModifierStorableIntegrationTest extends ModifierStorableBaseTest
{
    protected function getModifiers()
    {
        global $allModifiers;

        return $allModifiers;
    }
}

?>
