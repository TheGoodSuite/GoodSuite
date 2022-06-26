<?php

namespace Good\Memory;

use Good\Manners\Resolver;
use Good\Manners\ResolverVisitor;

class CollectionEntryResolver implements Resolver
{
    private $ownerType;
    private $collectionProperty;
    private $resolver;

    public function __construct($ownerType, $collectionProperty, Resolver $resolver = null)
    {
        $this->ownerType = $ownerType;
        $this->collectionProperty = $collectionProperty;
        $this->resolver = $resolver;
    }

    public function acceptResolverVisitor(ResolverVisitor $visitor)
    {
        if ($this->resolver === null)
        {
            $visitor->resolverVisitScalarProperty("value");
        }
        else
        {
            $visitor->resolverVisitResolvedReferenceProperty("value", $this->resolver->getType(), $this->resolver);
        }

            // public function ;
            // public function resolverVisitUnresolvedReferenceProperty($name);
            //
            // public function resolverVisitResolvedScalarCollectionProperty($name);
            // public function resolverVisitResolvedReferenceCollectionProperty($name, $typeName, Resolver $resolver);
            // public function resolverVisitUnresolvedCollectionProperty($name);
            //
            // public function resolverVisitScalarProperty($name);
            //
            // public function resolverVisitOrderAsc($number, $name);
            // public function resolverVisitOrderDesc($number, $name);
    }

    public function getType()
    {
        return $this->ownerType . '_' . $this->collectionProperty;
    }

    public function getRoot()
    {
        return $this;
    }
}

?>
