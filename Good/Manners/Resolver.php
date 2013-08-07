<?php

namespace Good\Manners;

interface Resolver
{
    public function resolverAccept(ResolverVisitor $visitor);
    public function getType();
}

?>