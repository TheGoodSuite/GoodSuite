<?php

namespace Good\Manners;

require_once dirname(__FILE__) . '/Resolver.php';

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