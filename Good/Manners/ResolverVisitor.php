<?php

require_once dirname(__FILE__) . '/Resolver.php';

interface GoodMannersResolverVisitor
{
	public function resolverVisitResolvedReferenceProperty($name, $typeName, 
															GoodMannersResolver $resolver);
	public function resolverVisitUnresolvedReferenceProperty($name);
	public function resolverVisitNonReferenceProperty($name);
	
	public function resolverVisitOrderAsc($number, $name);
	public function resolverVisitOrderDesc($number, $name);
}

?>