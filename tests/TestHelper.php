<?php

class TestHelper
{
    public static function cleanGeneratedFiles()
    {
        $path = dirname(__FILE__) . '/generated/';
        $iterator = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);

        foreach ($iterator as $file)
        {
            if ($file->getExtension() === 'php' || $file->getExtension() == 'datatype')
            {
                unlink($file->getPathname());
            }
        }
    }
}

?>
