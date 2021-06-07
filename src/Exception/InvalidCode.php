<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Exception;

final class InvalidCode extends \RuntimeException
{
	public static function invalidType(): self
	{
		throw new self('Invalid code structure');
	}
}
