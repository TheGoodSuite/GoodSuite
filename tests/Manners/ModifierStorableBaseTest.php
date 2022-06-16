<?php

require_once dirname(__FILE__) . '/../Service/GoodServiceBaseTest.php';

// Extend to test that no base functionality is screwed up

// Making abstract so I can have a child that does exactly this, and one
// that does the integration test with other modifiers
// (PHPUnit doesn't like it if I do this with a non-abstract class)

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
abstract class GoodMannersModifierStorableBaseTest extends GoodServiceBaseTest
{
    protected function getModifiers()
    {
        return [new \Good\Manners\Modifier\Storable()];
    }

    // todo
}

?>
