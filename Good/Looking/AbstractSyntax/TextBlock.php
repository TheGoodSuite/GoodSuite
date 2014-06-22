<?php

namespace Good\Looking\AbstractSyntax;

use Good\Looking\Environment;

class TextBlock implements Element
{
    private $text;
    
    public function __construct($text)
    {
        $this->text = $text;
    }
    
    public function execute(Environment $environment)
    {
        return '?>' . $this->text . '<?php ';
    }
}

?>