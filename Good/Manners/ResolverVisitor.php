<?php

namespace Good\Manners;

interface ResolverVisitor
{
    public function resolverVisitResolvedReferenceProperty($name, $typeName, 
                                                            Resolver $resolver);
    public function resolverVisitUnresolvedReferenceProperty($name);
    public function resolverVisitNonReferenceProperty($name);
    
    public function resolverVisitOrderAsc($number, $name);
    public function resolverVisitOrderDesc($number, $name);
}

?>