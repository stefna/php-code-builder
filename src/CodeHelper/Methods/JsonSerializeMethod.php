<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper\Methods;

use Stefna\PhpCodeBuilder\CodeHelper\ArrayCode;
use Stefna\PhpCodeBuilder\CodeHelper\CodeInterface;
use Stefna\PhpCodeBuilder\CodeHelper\JsonSerializeTypeCallResolver;
use Stefna\PhpCodeBuilder\CodeHelper\ReturnCode;
use Stefna\PhpCodeBuilder\CodeHelper\TypeCallResolverInterface;
use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\PhpClass;
use Stefna\PhpCodeBuilder\PhpMethod;
use Stefna\PhpCodeBuilder\PhpVariable;
use Stefna\PhpCodeBuilder\Renderer\RenderInterface;

/**
 * @phpstan-import-type SourceArray from RenderInterface
 */
final class JsonSerializeMethod extends PhpMethod
{
	public static function fromClass(PhpClass $class, TypeCallResolverInterface $resolver = null): self
	{
		$resolver = $resolver ?? new JsonSerializeTypeCallResolver();

		$source = [];
		$array = [];
		foreach ($class->getVariables() as $identifier) {
			/** @var PhpVariable $variable */
			$variable = $class->getVariable($identifier);
			$typeCall = $resolver->resolve($variable->getType(), $variable->getCodeReference());
			$array[$variable->getIdentifier()->toString()] = $typeCall->getCall();
			if ($typeCall->getExtraSource()) {
				$source = FlattenSource::applySourceOn($typeCall->getExtraSource(), $source);
			}
		}

		$source[] = new ReturnCode(new ArrayCode($array));
		return self::fromSource($source);
	}

	/**
	 * @phpstan-param SourceArray|CodeInterface $source
	 */
	public static function fromSource(array|CodeInterface $source): self
	{
		if ($source instanceof CodeInterface) {
			$source = $source->getSourceArray();
		}
		return new self(self::PUBLIC_ACCESS, 'jsonSerialize', [], $source);
	}
}
