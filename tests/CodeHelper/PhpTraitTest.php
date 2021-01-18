<?php declare(strict_types=1);

namespace CodeHelper;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\PhpClass;
use Stefna\PhpCodeBuilder\PhpConstant;
use Stefna\PhpCodeBuilder\PhpDocComment;
use Stefna\PhpCodeBuilder\PhpDocElementFactory;
use Stefna\PhpCodeBuilder\PhpFile;
use Stefna\PhpCodeBuilder\PhpInterface;
use Stefna\PhpCodeBuilder\PhpMethod;
use Stefna\PhpCodeBuilder\PhpParam;
use Stefna\PhpCodeBuilder\PhpTrait;
use Stefna\PhpCodeBuilder\PhpVariable;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;
use Stefna\PhpCodeBuilder\ValueObject\Type;

final class PhpTraitTest extends TestCase
{
	public function testSimple()
	{
		$trait = new PhpTrait('Test');
		$trait->addVariable(PhpVariable::protected('test', Type::fromString('string')));
		$trait->addMethod(PhpMethod::protected('runTest', [new PhpParam('foo', Type::fromString('Foo'))], [
			'return new self();',
		], Type::fromString('self')));

		var_dump($trait->getSource());
		var_dump($trait->getSourceArray());
		var_dump(FlattenSource::source([$trait]));
	}

	public function testComplex()
	{
		$comment = new PhpDocComment('Test Description');
		$comment->addMethod(PhpDocElementFactory::method('DateTime', 'TestClass', 'getDate'));
		$comment->setAuthor(PhpDocElementFactory::getAuthor('test', 'test@stefna.is'));

		$var = PhpVariable::private('random', Type::fromString('int'));
		$class = new PhpClass(Identifier::fromString('\Sunkan\Test\TestClass'), \ArrayObject::class, $comment, true);
		$class->addVariable($var, true);
		$class->addConstant(PhpConstant::private('SEED', '12'));
		$class->addTrait(NonExistingTrait::class);
		$class->addInterface(\IteratorAggregate::class);

		$file = PhpFile::createFromClass($class);
		var_dump($file->getSourceArray());
		echo FlattenSource::source([$file]);
	}

	public function testInterface()
	{
		$interface = new PhpInterface('TestInterface');
		$interface->addMethod(PhpMethod::public('test', [], [], Type::fromString('int')));

		var_dump($interface->getSource());
		var_dump($interface->getSourceArray());
	}

	public function testInterfaceExtend()
	{
		$interface = new PhpInterface('TestInterface');
		$interface->addExtend(\IteratorAggregate::class);
		$interface->addExtend(\Iterator::class);
		$interface->addMethod(PhpMethod::public('test', [], [], Type::fromString('int')));

		var_dump($interface->getSource());
		var_dump($interface->getSourceArray());
	}
}
