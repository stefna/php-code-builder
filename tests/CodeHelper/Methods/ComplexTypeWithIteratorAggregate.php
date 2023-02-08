<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\CodeHelper\Methods;

final class ComplexTypeWithIteratorAggregate implements \IteratorAggregate
{
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator([1, 2, 3]);
	}
}
