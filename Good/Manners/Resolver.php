<?php

namespace Good\Manners;

require_once dirname(__FILE__) . '/ResolverVisitor.php';

interface Resolver
{
	public function resolverAccept(ResolverVisitor $visitor);
}

?>