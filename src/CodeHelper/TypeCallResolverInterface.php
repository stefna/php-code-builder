<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\ValueObject\Type;
use Stefna\PhpCodeBuilder\ValueObject\TypeCall;

interface TypeCallResolverInterface
{
	public function resolve(Type $type, VariableReference $variableReference): TypeCall;
}
