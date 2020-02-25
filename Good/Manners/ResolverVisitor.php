<?php

namespace Good\Manners;

interface ResolverVisitor
{
    public function resolverVisitResolvedReferenceProperty($name, $typeName,
                                                            Resolver $resolver);
    public function resolverVisitUnresolvedReferenceProperty($name);

    public function resolverVisitResolvedScalarCollectionProperty($name);
    public function resolverVisitResolvedReferenceCollectionProperty($name, $typeName, Resolver $resolver);
    public function resolverVisitUnresolvedCollectionProperty($name);

    public function resolverVisitScalarProperty($name);

    public function resolverVisitOrderAsc($number, $name);
    public function resolverVisitOrderDesc($number, $name);
}

?>
