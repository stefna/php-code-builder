<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\CodeHelper\Methods;

final class ComplexTypeWithJsonSerialize implements \JsonSerializable
{
	public function jsonSerialize(): string
	{
		return 'complexType';
	}
}
