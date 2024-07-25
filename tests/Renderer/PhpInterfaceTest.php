<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\Renderer;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\PhpClass;
use Stefna\PhpCodeBuilder\PhpConstant;
use Stefna\PhpCodeBuilder\PhpInterface;
use Stefna\PhpCodeBuilder\PhpMethod;
use Stefna\PhpCodeBuilder\PhpParam;
use Stefna\PhpCodeBuilder\PhpStan\ArrayTypeField;
use Stefna\PhpCodeBuilder\PhpTrait;
use Stefna\PhpCodeBuilder\PhpVariable;
use Stefna\PhpCodeBuilder\Renderer\Php7Renderer;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;
use Stefna\PhpCodeBuilder\ValueObject\Type;

final class PhpInterfaceTest extends TestCase
{
	use AssertResultTrait;

	public function testSimpleInterface(): void
	{
		$interface = new PhpInterface(Identifier::fromString(\Test\TestInterface::class));
		$interface->addMethod(PhpMethod::public('testMethod', [], []));

		$renderer = new Php7Renderer();

		$this->assertSourceResult($renderer->renderInterface($interface), 'PhpInterfaceTest.' . __FUNCTION__);
	}

	public function testExtendSingleInterface(): void
	{
		$interface = new PhpInterface(Identifier::fromString(\Test\TestInterface::class));
		$interface->addMethod(PhpMethod::public('testMethod', [], []));
		$interface->addExtend(Identifier::fromString(\JsonSerializable::class));

		$renderer = new Php7Renderer();

		$this->assertSourceResult($renderer->renderInterface($interface), 'PhpInterfaceTest.' . __FUNCTION__);
	}

	public function testExtendMultipleInterface(): void
	{
		$interface = new PhpInterface(Identifier::fromString(\Test\TestInterface::class));
		$interface->addMethod(PhpMethod::public('testMethod', [], []));
		$interface->addExtend(Identifier::fromString(\JsonSerializable::class));
		$interface->addExtend(Identifier::fromString(\Traversable::class));
		$interface->addExtend(Identifier::fromString(\IteratorAggregate::class));

		$renderer = new Php7Renderer();

		$this->assertSourceResult($renderer->renderInterface($interface), 'PhpInterfaceTest.' . __FUNCTION__);
	}

	public function testInterfaceWithEverything(): void
	{
		$interface = new PhpInterface(Identifier::fromString(\Test\TestInterface::class));
		$interface->addMethod(PhpMethod::public('testMethod', [], []));
		$interface->addExtend(Identifier::fromString(\JsonSerializable::class));
		$interface->addExtend(Identifier::fromString(\Traversable::class));
		$interface->addExtend(Identifier::fromString(\IteratorAggregate::class));

		$var = PhpVariable::public('publicVar', Type::fromString('string'));
		$var->setInitializedValue('testValue');
		$interface->addVariable($var);
		$interface->addConstant(PhpConstant::public('publicConst'));

		$renderer = new Php7Renderer();

		$this->assertSourceResult($renderer->render($interface), 'PhpInterfaceTest.' . __FUNCTION__);
	}

	#[DataProvider('privateProtectedStuff')]
	public function testAddingPrivateStuffToInterface(
		PhpVariable|PhpConstant|PhpMethod $stuff,
	): void {
		$interface = new PhpInterface(Identifier::fromString(\Test\TestInterface::class));

		$this->expectException(\BadMethodCallException::class);

		if ($stuff instanceof PhpVariable) {
			$interface->addVariable($stuff);
		}
		elseif ($stuff instanceof PhpConstant) {
			$interface->addConstant($stuff);
		}
		if ($stuff instanceof PhpMethod) {
			$interface->addMethod($stuff);
		}
	}

	#[DataProvider('privateProtectedStuff')]
	public function testAddingPrivateStuffToInterfaceWithConvert(
		PhpVariable|PhpConstant|PhpMethod $stuff,
	): void {
		$interface = new PhpInterface(Identifier::fromString(\Test\TestInterface::class));

		if ($stuff instanceof PhpVariable) {
			$interface->addVariable($stuff, true);
			$interfaceVariable = $interface->getVariable($stuff->getIdentifier());
			$this->assertSame('public', $interfaceVariable->getAccess());
			$this->assertNotSame($stuff->getAccess(), $interfaceVariable->getAccess());
		}
		elseif ($stuff instanceof PhpConstant) {
			$interface->addConstant($stuff, true);
			$interfaceConstant = $interface->getConstant($stuff->getIdentifier());
			$this->assertSame('public', $interfaceConstant->getAccess());
			$this->assertNotSame($stuff->getAccess(), $interfaceConstant->getAccess());
		}
		if ($stuff instanceof PhpMethod) {
			$interface->addMethod($stuff, true);
			$interfaceMethod = $interface->getMethod($stuff->getIdentifier());
			$this->assertSame('public', $interfaceMethod->getAccess());
			$this->assertNotSame($stuff->getAccess(), $interfaceMethod->getAccess());
		}
	}

	public static function privateProtectedStuff(): array
	{
		return [
			[PhpVariable::private('privateVar', Type::empty())],
			[PhpVariable::protected('protectedVar', Type::empty())],
			[PhpConstant::private('privateConst')],
			[PhpConstant::protected('protectedConst')],
			[PhpMethod::private('privateMethod', [], [])],
			[PhpMethod::protected('protectedMethod', [], [])],
		];
	}

	public function testCreateInterfaceFromClass(): void
	{
		$class = new PhpClass(Identifier::fromString(Test\TestClass::class));
		$class->setExtends(\DateTimeImmutable::class);
		$class->addInterface(Identifier::fromString(\JsonSerializable::class));
		$var = PhpVariable::private('param1', Type::fromString('string|int'));
		$ctor = PhpMethod::constructor([
			PhpParam::fromVariable($var),
		], [], true);
		$class->addMethod($ctor);

		$class->addVariable(PhpVariable::public('var1', Type::fromString('string|int|null')));
		$class->addVariable(PhpVariable::private('var2', Type::empty()));
		$class->addVariable(PhpVariable::protected('var3', Type::empty()));

		$class->addMethod(PhpMethod::protected('notInInterfaceProtected', [], []));
		$class->addMethod(PhpMethod::private('notInInterfacePrivate', [], []));

		$class->addConstant(PhpConstant::public('inInterface'));
		$class->addConstant(PhpConstant::private('notInInterfacePrivate'));
		$class->addConstant(PhpConstant::protected('notInInterfaceProtected'));

		$class->addMethod(PhpMethod::public('testPublicMethod', [], []));
		$class->addMethod(PhpMethod::private('privateMethodNotInInterface', [], []));
		$class->addMethod(PhpMethod::protected('protectedMethodNotInInterface', [], []));

		$renderer = new Php7Renderer();

		$this->assertSourceResult(
			$renderer->renderInterface(PhpInterface::fromClass(
				Identifier::fromString(Test\TestInterface::class),
				$class
			)),
			'PhpInterfaceTest.' . __FUNCTION__,
		);
	}

	public function testVerifyThatRenderingInterfaceDontAffectRenderOfClass(): void
	{
		$class = new PhpClass(Identifier::fromString(Test\TestClass::class));
		$class->addMethod(PhpMethod::public('testPublicMethod', [], [
			'// void',
		]));
		$interface = PhpInterface::fromClass(
			Identifier::fromString(Test\TestInterface::class),
			$class,
		);

		$renderer = new Php7Renderer();

		$this->assertSourceResult(
			$renderer->render($interface),
			'PhpInterfaceTest.' . __FUNCTION__ . '.interface',
		);
		$this->assertSourceResult(
			$renderer->render($class),
			'PhpInterfaceTest.' . __FUNCTION__ . '.class',
		);
	}

	public function testCreateFromClassKeepsUseStatements(): void
	{
		$class = new PhpClass(Identifier::fromString(Test\TestClass::class));
		$class->setExtends(\DateTimeImmutable::class);
		$class->addInterface(Identifier::fromString(\JsonSerializable::class));
		$class->addUse(ArrayTypeField::class);
		$var = PhpVariable::private('param1', Type::fromString('string|int'));
		$ctor = PhpMethod::constructor([
			PhpParam::fromVariable($var),
		], [], true);
		$class->addMethod($ctor);

		$class->addVariable(PhpVariable::public('var1', Type::fromString('string|int|null')));
		$class->addVariable(PhpVariable::private('var2', Type::empty()));
		$class->addVariable(PhpVariable::protected('var3', Type::empty()));

		$class->addMethod(PhpMethod::protected('notInInterfaceProtected', [], []));
		$class->addMethod(PhpMethod::private('notInInterfacePrivate', [], []));

		$class->addConstant(PhpConstant::public('inInterface'));
		$class->addConstant(PhpConstant::private('notInInterfacePrivate'));
		$class->addConstant(PhpConstant::protected('notInInterfaceProtected'));

		$class->addMethod(PhpMethod::public('testPublicMethod', [], []));
		$class->addMethod(PhpMethod::private('privateMethodNotInInterface', [], []));
		$class->addMethod(PhpMethod::protected('protectedMethodNotInInterface', [], []));

		$interface = PhpInterface::fromClass(
			Identifier::fromString(Test\TestInterface::class),
			$class,
		);

		$this->assertSame(count($class->getUses()), count($interface->getUses()));
	}
}
