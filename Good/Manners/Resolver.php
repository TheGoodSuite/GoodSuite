<?php

require_once dirname(__FILE__) . '/ResolverVisitor.php';

interface GoodMannersResolver
{
	public function resolverAccept(GoodMannersResolverVisitor $visitor);
}

?>