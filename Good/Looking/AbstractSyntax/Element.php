<?php

namespace Good\Looking\AbstractSyntax;

use Good\Looking\Environment;

interface Element
{
    public function execute(Environment $environment);
}

?>