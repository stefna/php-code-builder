<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\ValueObject\Type;
use Stefna\PhpCodeBuilder\ValueObject\TypeCall;

final class JsonSerializeTypeCallResolver implements TypeCallResolverInterface
{
	public function resolve(Type $type, VariableReference $variableReference): TypeCall
	{
		if ($type->isNative()) {
			return new TypeCall($variableReference, []);
		}

		$extraCode = [];
		$call = $variableReference;

		if ($type->isArray()) {
			if ($type->isUnion()) {
				var_dump($type);
				var_dump('todo');
				exit;
			}
			$arrayType = $type->getArrayTypeObject();
			if (!$arrayType) {
				var_dump($type);
				var_dump('WTF');
				exit;
			}
			$localVariable = new VariableReference($variableReference->getName());

			$extraCode = [
				$localVariable->toString() . ' = [];',
				new ForeachCode($variableReference, function (string $keyName, string $valueName) use ($arrayType, $localVariable) {
					[$arrayCall, $arrayExtraCode] = $this->classTypeResolver(
						new VariableReference($valueName),
						$arrayType,
					);
					/** @var string $arrayCallStr */
					$arrayCallStr = $arrayCall->getSourceArray()[0];
					return FlattenSource::applySourceOn([
						$localVariable->toString() . '[' . $keyName . '] = ' . $arrayCallStr . ';',
					], $arrayExtraCode);
				}),
			];
			$call = $localVariable;
		}
		if (class_exists($type->getFqcn())) {
			[$call, $extraCode] = $this->classTypeResolver($variableReference, $type);
		}

		return new TypeCall($call, $extraCode);
	}

	/**
	 * @return array{CodeInterface, array}
	 * @throws \ReflectionException
	 */
	private function classTypeResolver(VariableReference $variableReference, Type $type): array
	{
		$extraCode = [];
		$call = $variableReference;
		$reflection = new \ReflectionClass($type->getFqcn());
		if ($reflection->implementsInterface(\JsonSerializable::class)) {
			$call = new ClassMethodCall($variableReference, 'jsonSerialize');
		}
		elseif ($reflection->implementsInterface(\Stringable::class)) {
			$call = new CastCode($variableReference, 'string');
		}
		elseif ($reflection->hasMethod('toString')) {
			$call = new ClassMethodCall($variableReference, 'toString');
		}
		elseif ($reflection->hasMethod('getArrayCopy')) {
			$call = new ClassMethodCall($variableReference, 'getArrayCopy');
		}
		elseif ($reflection->implementsInterface(\IteratorAggregate::class)) {
			$localVariable = new VariableReference($variableReference->getName());
			$prefix = '';
			if ($localVariable->getName() === 'value') {
				$prefix = 'inner';
				$localVariable = new VariableReference('innerLoop');
			}
			$extraCode = [
				$localVariable->toString() . ' = [];',
				new ForeachCode(
					$variableReference,
					function (string $keyName, string $valueName) use ($localVariable) {
						return [
							$localVariable->toString() . '[' . $keyName . '] = ' . $valueName . ';',
						];
					},
					$prefix
				),
			];
			$call = $localVariable;
		}

		if ($call instanceof ClassMethodCall && $type->isNullable()) {
			$call = TernaryOperator::nullableCall($variableReference, $call);
		}

		return [$call, $extraCode];
	}
}
