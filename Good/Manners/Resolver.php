<?php

namespace Good\Manners;

interface Resolver
{
    public function acceptResolverVisitor(ResolverVisitor $visitor);
    public function getType();
    public function getRoot();
}

?>
