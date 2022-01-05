<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\Renderer;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\PhpMethod;
use Stefna\PhpCodeBuilder\PhpParam;
use Stefna\PhpCodeBuilder\PhpVariable;
use Stefna\PhpCodeBuilder\Renderer\Php7Renderer;
use Stefna\PhpCodeBuilder\Renderer\Php8Renderer;
use Stefna\PhpCodeBuilder\ValueObject\Type;

final class PhpMethodTest extends TestCase
{
	use AssertResultTrait;

	public function testPublicSimpleMethod()
	{
		$method = PhpMethod::public('testMethod', [
			new PhpParam('param1', Type::fromString('string')),
		], [
			'$this->param = $param1;',
			'return $this;',
		], Type::fromString('self'));

		$renderer = new Php7Renderer();

		$this->assertSourceResult($renderer->renderMethod($method), 'PhpMethodTest.' . __FUNCTION__);
	}

	public function testNullableParam()
	{
		$param = new PhpParam('param1', Type::fromString('string'));
		$method = PhpMethod::public('testMethod', [$param], [
			'$this->param = $param1;',
			'return $this;',
		], Type::fromString('self'));

		$param->allowNull();

		$renderer = new Php7Renderer();

		$this->assertTrue($param->isNullable());
		$this->assertSourceResult($renderer->renderMethod($method), 'PhpMethodTest.' . __FUNCTION__);
	}

	public function testAbstractMethod()
	{
		$method = PhpMethod::public('testMethod', [
			new PhpParam('param1', Type::fromString('string')),
		], [], Type::fromString('resource'))->setAbstract();

		$renderer = new Php7Renderer();

		$this->assertSourceResult($renderer->renderMethod($method), 'PhpMethodTest.' . __FUNCTION__);
	}

	public function testConstructorWithAutoAssign()
	{
		$var2 = PhpVariable::private('test2', Type::fromString('string'));
		$var3 = PhpVariable::protected('test3', Type::fromString('int|string'));
		$ctor = PhpMethod::constructor([
			new PhpParam('test1', Type::fromString('int'), autoCreateVariable: true),
			PhpParam::fromVariable($var2),
			PhpParam::fromVariable($var3),
			new PhpParam('test4', Type::fromString('bool'), true),
		], [], true);

		$renderer = new Php7Renderer();
		$this->assertSourceResult($renderer->renderMethod($ctor), 'PhpMethodTest.' . __FUNCTION__);
	}

	public function testConstructorWithAutoAssignPhp8()
	{
		$var = PhpVariable::private('test2', Type::fromString('string'));
		$ctor = PhpMethod::constructor([
			new PhpParam('test1', Type::fromString('int'), autoCreateVariable: true),
			PhpParam::fromVariable($var),
			new PhpParam('test3', Type::fromString('int|string|null'), autoCreateVariable: true),
			new PhpParam('test4', Type::fromString('bool'), true),
		], [], true);

		$renderer = new Php8Renderer();
		$this->assertSourceResult($renderer->renderMethod($ctor), 'PhpMethodTest.' . __FUNCTION__);
	}

	public function testConstructorSimplePhp8()
	{
		$ctor = PhpMethod::constructor([
			new PhpParam('test1', Type::fromString('int')),
		], [
			'// nothing'
		]);

		$renderer = new Php8Renderer();
		$this->assertSourceResult($renderer->renderMethod($ctor), 'PhpMethodTest.' . __FUNCTION__);
	}

	public function testConstructorAlwaysMultiLineWhenPromotingPhp8()
	{
		$ctor = PhpMethod::constructor([
			new PhpParam('test1', Type::fromString('int'), autoCreateVariable: true),
		], [], true);

		$renderer = new Php8Renderer();
		$this->assertSourceResult($renderer->render($ctor), 'PhpMethodTest.' . __FUNCTION__);
	}

	public function testConstructorPromotingNotRenderingVariablePhp8()
	{
		$var = PhpVariable::protected('test', Type::fromString('float'));
		$ctor = PhpMethod::constructor([
			PhpParam::fromVariable($var),
		], [], true);

		$renderer = new Php8Renderer();
		$renderer->renderMethod($ctor);

		$this->assertTrue($var->isPromoted());
	}

	public function testFinalAbstractMethod()
	{
		$this->expectException(\BadMethodCallException::class);

		PhpMethod::protected('testMethod', [], [])->setAbstract()->setFinal();
	}

	public function testPrivateAbstractMethod()
	{
		$this->expectException(\BadMethodCallException::class);

		PhpMethod::private('testMethod', [], [])->setAbstract();
	}

	public function testFinalPrivateMethod()
	{
		$this->expectException(\BadMethodCallException::class);

		PhpMethod::private('testMethod', [], [])->setFinal();
	}

	public function testFinalStaticMethod()
	{
		$method = PhpMethod::private('testMethod', [], [])
			->setStatic()
			->setAccess(PhpMethod::PUBLIC_ACCESS)
			->setFinal();

		$renderer = new Php7Renderer();
		$this->assertSourceResult($renderer->renderMethod($method), 'PhpMethodTest.' . __FUNCTION__);
	}

	public function testLegacyFunctionTestConstructorMethod()
	{
		$method = PhpMethod::constructor([
			new PhpParam('fooIpsumLong', Type::fromString('string')),
			new PhpParam('barIpsumLong', Type::fromString('string|int')),
			new PhpParam('bazIpsumLong', Type::fromString('int')),
			new PhpParam('fozIpsumLong', Type::fromString('string')),
			new PhpParam('alzIpsumLong', Type::fromString('string')),
			new PhpParam('qweIpsumLong', Type::fromString('string')),
			new PhpParam('rtyIpsumLong', Type::fromString('string')),
		], [
			'$this->do = null;',
		]);

		$renderer = new Php7Renderer();
		$this->assertSame([
			'/**',
			' * @param string|int $barIpsumLong',
			' */',
			'public function __construct(',
			[
				'string $fooIpsumLong,',
				'$barIpsumLong,',
				'int $bazIpsumLong,',
				'string $fozIpsumLong,',
				'string $alzIpsumLong,',
				'string $qweIpsumLong,',
				'string $rtyIpsumLong',
			],
			') {',
			['$this->do = null;'],
			'}',
		], $renderer->renderMethod($method));
	}

	public function testLegacyFunctionTestMultilineParamsAbstract()
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

		$renderer = new Php7Renderer();
		$this->assertSourceResult($renderer->renderMethod($func), 'PhpMethodTest.' . __FUNCTION__);
	}

	public function testAbstractMethodWithBody()
	{
		$func = new PhpMethod('protected', 'test', [
			new PhpParam('foo', Type::fromString('string|int')),
		], [
			'return $foo * $foo;',
		], Type::fromString('int'));
		$func->setAbstract();

		$renderer = new Php7Renderer();
		$this->assertSourceResult($renderer->renderMethod($func), 'PhpMethodTest.' . __FUNCTION__);
	}

	public function testImmutableSetter()
	{
		$var = PhpVariable::protected('test', Type::fromString('string'));
		$setter = PhpMethod::setter($var, immutable: true);

		$renderer = new Php7Renderer();
		$this->assertSourceResult($renderer->renderMethod($setter), 'PhpMethodTest.' . __FUNCTION__);
	}


	/**
	 * @dataProvider getterPrefixes
	 */
	public function testAutoGetterPrefix(string $name, Type $type, string $expectedMethodName)
	{
		$var = PhpVariable::protected($name, $type);
		$method = PhpMethod::getter($var);

		$this->assertSame($expectedMethodName, $method->getIdentifier()->toString());
	}

	public function getterPrefixes()
	{
		return [
			['hasError', Type::fromString('bool'), 'hasError'],
			['isAbstract', Type::fromString('bool'), 'isAbstract'],
			['normal', Type::fromString('string'), 'getNormal'],
		];
	}
}
