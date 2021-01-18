<?php declare(strict_types=1);

namespace CodeHelper;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\CodeHelper\IfCode;
use Stefna\PhpCodeBuilder\CodeHelper\LineCode;
use Stefna\PhpCodeBuilder\CodeHelper\StaticMethodCall;
use Stefna\PhpCodeBuilder\CodeHelper\VariableReference;
use Stefna\PhpCodeBuilder\PhpFunction;
use Stefna\PhpCodeBuilder\PhpMethod;
use Stefna\PhpCodeBuilder\PhpParam;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;
use Stefna\PhpCodeBuilder\ValueObject\Type;

final class PhpFunctionTest extends TestCase
{
	public function testSimple()
	{
		$func = new PhpFunction('test', [
			new PhpParam('foo', Type::fromString('string|int')),
		], [
			'return $foo * $foo;',
		], Type::fromString('int'));

		var_dump($func->getSource());
		var_dump($func->getSourceArray());
	}

	public function testComplexSource()
	{
		$func = new PhpFunction('test', [
			new PhpParam('foo', Type::fromString('string|int')),
		], [
			new IfCode('is_int($foo)', [
				'return $foo * $foo;',
			]),
			new LineCode(new StaticMethodCall(Identifier::fromString('SecurityValue'), 'apiKey', [
				new VariableReference('foo'),
			])),
		], Type::fromString('int'));

		var_dump($func->getSource());
		var_dump($func->getSourceArray());
	}

	public function testMultilineParams()
	{
		$func = new PhpFunction('test', [
			new PhpParam('fooIpsumLong', Type::fromString('string')),
			new PhpParam('barIpsumLong', Type::fromString('string|int')),
			new PhpParam('bazIpsumLong', Type::fromString('int')),
			new PhpParam('fozIpsumLong', Type::fromString('string')),
			new PhpParam('alzIpsumLong', Type::fromString('string')),
			new PhpParam('qweIpsumLong', Type::fromString('string')),
			new PhpParam('rtyIpsumLong', Type::fromString('string')),
		], [
			'return $foo * $foo;',
		], Type::fromString('int'));

		var_dump($func->getSource());
		var_dump($func->getSourceArray());
	}

	public function testAbstractMethod()
	{
		$func = new PhpMethod('private', 'test', [
			new PhpParam('foo', Type::fromString('string|int')),
		], [
			'return $foo * $foo;',
		], Type::fromString('int'));
		$func->setAbstract();

		var_dump($func->getSource());
		var_dump($func->getSourceArray());
	}

	public function testMultilineParamsAbstract()
	{
		$func = new PhpMethod('protected', 'test', [
			new PhpParam('fooIpsumLong', Type::fromString('string')),
			new PhpParam('barIpsumLong', Type::fromString('string|int')),
			new PhpParam('bazIpsumLong', Type::fromString('int')),
			new PhpParam('fozIpsumLong', Type::fromString('string')),
			new PhpParam('alzIpsumLong', Type::fromString('string')),
			new PhpParam('qweIpsumLong', Type::fromString('string')),
			new PhpParam('rtyIpsumLong', Type::fromString('string')),
		], [
			'return $foo * $foo;',
		], Type::fromString('int'));
		$func->setAbstract()->setStatic();

		var_dump($func->getSource());
		var_dump($func->getSourceArray());
	}
}
