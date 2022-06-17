<?php

use Good\Looking\Looking;

class GoodLookingCachingTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown(): void
    {
        $path = __dir__ . '/../generated/';
        $iterator = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
        $recursiveIterator = new \RecursiveIteratorIterator($iterator);

        foreach ($recursiveIterator as $file)
        {
            if ($file->getExtension() === 'compiledTemplate' ||
                $file->getExtension() === 'template')
            {
                unlink($file->getPathname());
            }
        }
    }

    public function testUsingCache()
    {
        // Note: sometimes misbehaves on my local setup that has the file being
        //       accessed over a sambe mounted file system (on a virtual network)
        $this->expectOutputString('YES');

        file_put_contents(__dir__ . '/../generated/template.template', 'NO');
        // this cached value is newer than the template, so it should be
        // what is served
        file_put_contents(__dir__ . '/../generated/template.template.compiledTemplate', 'YES');

        $goodLooking = new Looking(__dir__ . '/../generated/template.template');
        $goodLooking->display();
    }

    public function testNotOverusingCache()
    {
        // Note: sometimes misbehaves on my local setup that has the file being
        //       accessed over a sambe mounted file system (on a virtual network)
        $this->expectOutputString('NO');

        file_put_contents(__dir__ . '/../generated/template.template.compiledTemplate', 'YES');

        // We need to wait a second before this actually workds (because filemtime
        // only different after a second
        // This is no problem, though, as this is something that should happen manually
        // and thus never more than once per second.
        sleep(1);

        file_put_contents(__dir__ . '/../generated/template.template', 'NO');


        $goodLooking = new Looking(__dir__ . '/../generated/template.template');
        $goodLooking->display();
    }
}

?>
