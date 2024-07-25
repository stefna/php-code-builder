<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\Renderer;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\PhpAttribute;
use Stefna\PhpCodeBuilder\PhpClass;
use Stefna\PhpCodeBuilder\PhpConstant;
use Stefna\PhpCodeBuilder\PhpDocComment;
use Stefna\PhpCodeBuilder\PhpDocElementFactory;
use Stefna\PhpCodeBuilder\PhpFile;
use Stefna\PhpCodeBuilder\PhpInterface;
use Stefna\PhpCodeBuilder\PhpMethod;
use Stefna\PhpCodeBuilder\PhpParam;
use Stefna\PhpCodeBuilder\PhpStan\ArrayTypeField;
use Stefna\PhpCodeBuilder\PhpStan\ExtendsField;
use Stefna\PhpCodeBuilder\PhpStan\ImplementsField;
use Stefna\PhpCodeBuilder\PhpStan\ImportArrayTypeField;
use Stefna\PhpCodeBuilder\PhpStan\TemplateField;
use Stefna\PhpCodeBuilder\PhpVariable;
use Stefna\PhpCodeBuilder\Renderer\Php74Renderer;
use Stefna\PhpCodeBuilder\Renderer\Php7Renderer;
use Stefna\PhpCodeBuilder\Renderer\Php81Renderer;
use Stefna\PhpCodeBuilder\Renderer\Php82Renderer;
use Stefna\PhpCodeBuilder\Renderer\Php8Renderer;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;
use Stefna\PhpCodeBuilder\ValueObject\Type;

final class PhpClassTest extends TestCase
{
	use AssertResultTrait;

	private function getTestClass(): PhpClass
	{
		$class = new PhpClass(
			Identifier::fromString(Test\TestClass::class),
			extends: \DateTimeImmutable::class,
			implements: [Identifier::fromString(\JsonSerializable::class)]
		);
		$var = PhpVariable::protected('param1', Type::fromString('string|int'));
		$var->setReadOnly(true);
		$ctor = PhpMethod::constructor([
			PhpParam::fromVariable($var),
		], [], true);
		$class->addMethod($ctor);

		$var2 = PhpVariable::public('var1', Type::fromString('string|int|null'));
		$class->addVariable($var2);

		$ctor->addParam(new PhpParam('param2', Type::fromString('?int'), autoCreateVariable: true));
		$ctor->addParam(new PhpParam('noneAssigned', Type::fromString('float')));

		return $class;
	}

	public function testClassRenderedWithPhp7(): void
	{
		$renderer = new Php7Renderer();

		$this->assertSourceResult($renderer->render($this->getTestClass()), 'PhpClassTest.' . __FUNCTION__);
	}

	public function testClassRenderedWithPhp74(): void
	{
		$renderer = new Php74Renderer();

		$this->assertSourceResult($renderer->renderClass($this->getTestClass()), 'PhpClassTest.' . __FUNCTION__);
	}

	public function testClassRenderedWithPhp8(): void
	{
		$renderer = new Php8Renderer();

		$this->assertSourceResult($renderer->renderClass($this->getTestClass()), 'PhpClassTest.' . __FUNCTION__);
	}

	public function testClassRenderedWithPhp81(): void
	{
		$renderer = new Php81Renderer();

		$this->assertSourceResult($renderer->renderClass($this->getTestClass()), 'PhpClassTest.' . __FUNCTION__);
	}

	public function testClassRenderedWithPhp82(): void
	{
		$renderer = new Php82Renderer();

		$this->assertSourceResult($renderer->renderClass($this->getTestClass()), 'PhpClassTest.' . __FUNCTION__);
	}

	public function testClassWithPropertyPromotion(): void
	{
		$class = new PhpClass(
			Identifier::fromString(Test\TestClass::class),
		);
		$ctor = PhpMethod::constructor([
			new PhpParam(
				'test',
				Type::fromString('string'),
				autoCreateVariable: true,
				autoCreateVariableSetter: false,
				autoCreateVariableGetter: true,
			),
		], [], true);
		$class->addMethod($ctor);
		$renderer = new Php8Renderer();

		$this->assertSourceResult($renderer->renderClass($class), 'PhpClassTest.' . __FUNCTION__);
	}

	public function testLegacyTestComplex(): void
	{
		$comment = new PhpDocComment('Test Description');
		$comment->addMethod(PhpDocElementFactory::method('DateTime', 'TestClass', 'getDate'));
		$comment->setAuthor(PhpDocElementFactory::getAuthor('test', 'test@stefna.is'));

		$var = PhpVariable::private('random', Type::fromString('int'));
		$class = new PhpClass(
			Identifier::fromString('\Sunkan\Test\TestClass'),
			\ArrayObject::class,
			$comment
		);
		$class->setFinal();
		$class->addVariable($var, true);
		$class->addConstant(PhpConstant::private('SEED', '12'));
		$class->addTrait(NonExistingTrait::class);
		$class->addInterface(\IteratorAggregate::class);

		$renderer = new Php7Renderer();
		$this->assertSourceResult($renderer->renderClass($class), 'PhpClassTest.' . __FUNCTION__);
	}

	public function testAbstractClass(): void
	{
		$class = new PhpClass(Identifier::fromString(Test\AbstractTest\TestClass::class));
		$class->setAbstract();
		$class->addMethod(PhpMethod::protected('testNonProtectedMethod', [], []));
		$class->addMethod(PhpMethod::protected('testAbstractProtectedMethod', [], [])->setAbstract());

		$renderer = new Php7Renderer();
		$this->assertSourceResult($renderer->renderClass($class), 'PhpClassTest.' . __FUNCTION__);
	}

	public function testAddingAbstractMethodToNoneAbstractClass(): void
	{
		$this->expectException(\BadMethodCallException::class);

		$class = new PhpClass(Identifier::fromString(Test\TestClass::class));
		$class->addMethod(PhpMethod::protected('testAbstractProtectedMethod', [], [])->setAbstract());
	}

	public function testAutoSetterAndGetter(): void
	{
		$class = new PhpClass(
			Identifier::fromString(Test\TestClass::class),
			extends: \DateTimeImmutable::class,
			implements: [Identifier::fromString(\JsonSerializable::class)]
		);
		$ctor = PhpMethod::constructor([
			new PhpParam(
				'param1',
				Type::fromString('string'),
				autoCreateVariable: true,
				autoCreateVariableSetter: true,
				autoCreateVariableGetter: true,
			),
		], [], true);
		$class->addMethod($ctor);

		$renderer = new Php7Renderer();
		$this->assertSourceResult($renderer->renderClass($class), 'PhpClassTest.' . __FUNCTION__);
	}

	public function testDocBlockWithImplementsAndExtend(): void
	{
		$templateFieldT = new TemplateField('T', Identifier::fromString(Test\AbstractTest2\GenericClass::class));
		$extends = Identifier::fromString(Test\AbstractTest2\AbstractClass::class);
		$implement = Identifier::fromString(Test\AbstractTest2\GenericInterface::class);
		$extraGenericIdentifier = Identifier::fromString(Test\AbstractTest2\GenericClass2::class);
		$extraGenericIdentifier->setAlias('GenericAlias');

		$comment = new PhpDocComment();
		$comment->addField($templateFieldT);
		$comment->addField(new ExtendsField($extends, $templateFieldT, $extraGenericIdentifier));
		$comment->addField(new ImplementsField($implement, $templateFieldT));

		$class = new PhpClass(
			Identifier::fromString(Test\AbstractTest\TestClass::class),
			extends: $extends,
			implements: [$implement],
			comment: $comment,
		);

		$renderer = new Php7Renderer();
		$this->assertSourceResult($renderer->renderClass($class), 'PhpClassTest.' . __FUNCTION__);
	}

	public function testPhpStanArrayType(): void
	{
		$addressIdentifier = Identifier::fromString(Test\Address::class);
		$addressIdentifier->setAlias('OfficeAddress');
		$arrayType = new ArrayTypeField('RowSchema', [
			'name' => 'string',
			'age?' => Type::fromString('int'),
			'address' => $addressIdentifier,
		]);
		$comment = new PhpDocComment();
		$comment->addField($arrayType);

		$class = new PhpClass(
			Identifier::fromString(Test\AbstractTest\TestClass::class),
			comment: $comment,
		);

		$methodComment = new PhpDocComment();
		$methodComment->addField(PhpDocElementFactory::getReturn('RowSchema'));

		$method = PhpMethod::public('getArray', [], [], Type::fromString('array'));
		$method->setComment($methodComment);
		$class->addMethod($method);

		$renderer = new Php7Renderer();
		$this->assertSourceResult($renderer->renderClass($class), 'PhpClassTest.' . __FUNCTION__);
	}

	public function testPhpStanImportArrayType(): void
	{
		$addressIdentifier = Identifier::fromString(Test\Address::class);
		$addressIdentifier->setAlias('OfficeAddress');
		$arrayType = new ArrayTypeField('RowSchema', [
			'name' => 'string',
			'age?' => Type::fromString('int'),
			'address' => $addressIdentifier,
		]);

		$importedType = new ImportArrayTypeField(
			Identifier::fromString(Test\AbstractTest\TestClass::class),
			$arrayType->getIdentifier(),
			'RandomSchema',
		);

		$comment = new PhpDocComment();
		$comment->addField($importedType);

		$class = new PhpClass(
			Identifier::fromString(Test\AbstractTest\TestClass::class),
			comment: $comment,
		);

		$methodComment = new PhpDocComment();
		$methodComment->addField(PhpDocElementFactory::getReturn($importedType->getDataType()));

		$method = PhpMethod::public('getArray', [], [], Type::fromString('array'));
		$method->setComment($methodComment);
		$class->addMethod($method);

		$renderer = new Php7Renderer();
		$this->assertSourceResult($renderer->renderClass($class), 'PhpClassTest.' . __FUNCTION__);
	}

	public function testDocBlockWithNestedTemplates(): void
	{
		$implement = Identifier::fromString(RepositoryInterface::class);

		$comment = new PhpDocComment();
		$comment->addField(new ImplementsField(
			$implement,
			Identifier::fromString('Id'),
			Identifier::fromString('BuilderInterface')->genericOf(Identifier::fromString('Select')),
			Identifier::fromString('Entity'),
			Identifier::fromString('Collection'),
		));

		$class = new PhpClass(
			Identifier::fromString(Test\AbstractTest\TestClass::class),
			implements: [$implement],
			comment: $comment,
		);

		$renderer = new Php7Renderer();
		$this->assertSourceResult($renderer->renderClass($class), 'PhpClassTest.' . __FUNCTION__);
	}

	public function testAutoSetterAndGetterImmutable(): void
	{
		$class = new PhpClass(
			Identifier::fromString(Test\TestClass::class),
			extends: \DateTimeImmutable::class,
			implements: [Identifier::fromString(\JsonSerializable::class)]
		);
		$class->setImmutable();
		$ctor = PhpMethod::constructor([
			new PhpParam(
				'param1',
				Type::fromString('string'),
				autoCreateVariable: true,
				autoCreateVariableSetter: true,
				autoCreateVariableGetter: true,
			),
		], [], true);
		$class->addMethod($ctor);

		$renderer = new Php7Renderer();
		$this->assertSourceResult($renderer->renderClass($class), 'PhpClassTest.' . __FUNCTION__);
	}

	public function testPhp82ReadOnlyClass(): void
	{
		$class = new PhpClass(
			Identifier::fromString(Test\TestClass::class),
			final: true,
			readOnly: true,
		);
		$ctor = PhpMethod::constructor([
			new PhpParam(
				'test',
				Type::fromString('string'),
				autoCreateVariable: true,
				autoCreateVariableSetter: false,
				autoCreateVariableGetter: true,
			),
		], [], true);
		$class->addMethod($ctor);
		$class->addVariable(PhpVariable::public('stringTest', Type::fromString('string')));

		$renderer = new Php82Renderer();
		$this->assertSourceResult($renderer->renderClass($class), 'PhpClassTest.' . __FUNCTION__);
	}

	public function testPhp81ReadOnlyClass(): void
	{
		$class = new PhpClass(
			Identifier::fromString(Test\TestClass::class),
			final: true,
			readOnly: true,
		);
		$ctor = PhpMethod::constructor([
			new PhpParam(
				'test',
				Type::fromString('string'),
				autoCreateVariable: true,
				autoCreateVariableSetter: false,
				autoCreateVariableGetter: true,
			),
		], [], true);
		$class->addMethod($ctor);
		$class->addVariable(PhpVariable::public('stringTest', Type::fromString('string')));

		$renderer = new Php81Renderer();
		$this->assertSourceResult($renderer->renderClass($class), 'PhpClassTest.' . __FUNCTION__);
	}

	public function testPhp81PropertyDocBlock(): void
	{
		$comment = new PhpDocComment();
		$class = new PhpClass(
			Identifier::fromString(Test\TestClass::class),
			comment: $comment,
			final: true,
			readOnly: true,
		);
		$ctor = PhpMethod::constructor([
			new PhpParam(
				'test',
				Type::fromString('string'),
				autoCreateVariable: true,
				autoCreateVariableSetter: false,
				autoCreateVariableGetter: true,
			),
		], [], true);
		$class->addMethod($ctor);
		$var = PhpVariable::public('stringTest', Type::fromString('string'));
		$class->addVariable($var);
		$comment->addField(PhpDocElementFactory::getPropertyFromVariable($var, 'VARCHAR(10) NOT NULL'));

		$renderer = new Php81Renderer();
		$this->assertSourceResult($renderer->renderClass($class), 'PhpClassTest.' . __FUNCTION__);
	}

	public function testStripEmptyTypesFromUses(): void
	{
		$class = new PhpClass(Identifier::fromString(Test\TestClass::class));
		$type = Type::fromString(PhpClass::class);
		$type->addUnion(PhpInterface::class);
		$class->addMethod(PhpMethod::constructor([
			new PhpParam('test', $type),
		], [], true));

		$this->assertCount(2, $class->getUses());
		foreach ($class->getUses() as $type) {
			$this->assertNotEmpty($type->getName());
		}
	}

	public function testVariableAttributeFromCtorNotPropagateToSetter(): void
	{
		$class = new PhpClass(Identifier::fromString('Test'));

		$type = Type::fromString('int');
		$param = new PhpParam('test3', $type, autoCreateVariable: true, autoCreateVariableSetter: true);
		$param->getVariable()?->addAttribute(new PhpAttribute(TestAttribute::class, '1'));
		$ctor = PhpMethod::constructor([
			$param,
		], [], true);

		$class->addMethod($ctor);

		$renderer = new Php8Renderer();
		$this->assertSourceResult(
			$renderer->render($class),
			'PhpClassTest.testVariableAttributeFromCtorNotPropegateToSetter',
		);
	}
}
