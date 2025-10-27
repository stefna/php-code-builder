<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper\IfHelper;

use Stefna\PhpCodeBuilder\CodeHelper\VariableReference;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;

final class IfFactory
{
	/**
	 * @param array<int, mixed> $code
	 * @param Identifier|class-string $identifier
	 */
	public static function instanceOf(VariableReference $var, Identifier|string $identifier, array $code): IfCode
	{
		$identifier = Identifier::fromUnknown($identifier);
		return new IfCode(
			$var->toString() . ' instanceof ' . $identifier->getName(),
			$code
		);
	}

	/**
	 * @param array<int, mixed> $code
	 */
	public static function nullCheck(VariableReference $var, array $code): IfCode
	{
		return new IfCode(
			$var->toString() . ' === null',
			$code
		);
	}

	/**
	 * @param array<int, mixed> $code
	 */
	public static function methodExist(
		VariableReference $var,
		string $method,
		array $code,
	): IfCode {
		return new IfCode(
			sprintf(
				'is_object(%1$s) && method_exists(%1$s, \'%2$s\')',
				$var->toString(),
				$method,
			),
			$code,
		);
	}
}
