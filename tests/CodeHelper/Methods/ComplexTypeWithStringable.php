<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\CodeHelper\Methods;

final class ComplexTypeWithStringable implements \Stringable
{
	public function __toString(): string
	{
		return 'complexType';
	}
}
