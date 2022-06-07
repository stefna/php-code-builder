<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\CodeHelper\Methods;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\CodeHelper\Methods\JsonSerializeMethod;
use Stefna\PhpCodeBuilder\PhpClass;
use Stefna\PhpCodeBuilder\PhpVariable;
use Stefna\PhpCodeBuilder\Renderer\Php8Renderer;
use Stefna\PhpCodeBuilder\Tests\Renderer\AssertResultTrait;
use Stefna\PhpCodeBuilder\ValueObject\Type;

final class JsonSerializeMethodTest extends TestCase
{
	use AssertResultTrait;

	public function testFromClass(): void
	{
		$class = new PhpClass('Test');
		$class->addVariable(PhpVariable::protected('random', Type::fromString('string')));
		$class->addVariable(PhpVariable::protected('randomBool', Type::fromString('bool')));
		$class->addVariable(PhpVariable::protected('randomInt', Type::fromString('int')));
		$class->addVariable(PhpVariable::protected('randomIntList', Type::fromString('int[]')));
		$method = JsonSerializeMethod::fromClass($class);

		$renderer = new Php8Renderer();
		$this->assertSourceResult($renderer->renderMethod($method), 'JsonSerializeMethodTest.' . __FUNCTION__);
	}

	public function testFromClassWithComplexTypes(): void
	{
		$class = new PhpClass('Test');
		$class->addVariable(PhpVariable::protected('jsonSerialize', Type::fromString(ComplexTypeWithJsonSerialize::class)));
		$class->addVariable(PhpVariable::protected('stringable', Type::fromString(ComplexTypeWithStringable::class)));
		$class->addVariable(PhpVariable::protected('toString', Type::fromString(ComplexTypeWithToString::class)));
		$method = JsonSerializeMethod::fromClass($class);

		$renderer = new Php8Renderer();
		$this->assertSourceResult($renderer->renderMethod($method), 'JsonSerializeMethodTest.' . __FUNCTION__);
	}

	public function testIterator(): void
	{
		$class = new PhpClass('Test');
		$class->addVariable(PhpVariable::protected('getArrayCopy', Type::fromString(ComplexTypeWithGetArrayCopy::class)));
		$class->addVariable(PhpVariable::protected('iteratorAggregate', Type::fromString(ComplexTypeWithIteratorAggregate::class)));
		$method = JsonSerializeMethod::fromClass($class);

		$renderer = new Php8Renderer();
		$this->assertSourceResult($renderer->renderMethod($method), 'JsonSerializeMethodTest.' . __FUNCTION__);
	}

	public function testArrays(): void
	{
		$class = new PhpClass('Test');
		$class->addVariable(PhpVariable::protected('simpleArray', Type::fromString('int[]')));
		$class->addVariable(PhpVariable::protected('arrayWithToString', Type::fromString(ComplexTypeWithToString::class . '[]')));
		$class->addVariable(PhpVariable::protected('iteratorAggregate', Type::fromString(ComplexTypeWithIteratorAggregate::class . '[]')));
		$class->addVariable(PhpVariable::protected('arrayCopy', Type::fromString(ComplexTypeWithGetArrayCopy::class . '[]')));
		$method = JsonSerializeMethod::fromClass($class);

		$renderer = new Php8Renderer();
		$this->assertSourceResult($renderer->renderMethod($method), 'JsonSerializeMethodTest.' . __FUNCTION__);
	}

	public function testWithNullableValues(): void
	{
		$class = new PhpClass('Test');
		$class->addVariable(PhpVariable::protected('jsonSerialize', Type::fromString('null|' . ComplexTypeWithJsonSerialize::class)));
		$class->addVariable(PhpVariable::protected('stringable', Type::fromString('null|' . ComplexTypeWithStringable::class)));
		$class->addVariable(PhpVariable::protected('toString', Type::fromString('null|' . ComplexTypeWithToString::class)));
		$method = JsonSerializeMethod::fromClass($class);

		$renderer = new Php8Renderer();
		$this->assertSourceResult($renderer->renderMethod($method), 'JsonSerializeMethodTest.' . __FUNCTION__);
	}
}
