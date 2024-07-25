<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\Renderer;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\CodeHelper\IfCode;
use Stefna\PhpCodeBuilder\CodeHelper\LineCode;
use Stefna\PhpCodeBuilder\CodeHelper\StaticMethodCall;
use Stefna\PhpCodeBuilder\CodeHelper\VariableReference;
use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\PhpFunction;
use Stefna\PhpCodeBuilder\PhpMethod;
use Stefna\PhpCodeBuilder\PhpParam;
use Stefna\PhpCodeBuilder\Renderer\Php7Renderer;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;
use Stefna\PhpCodeBuilder\ValueObject\Type;

final class PhpFunctionTest extends TestCase
{
	use AssertResultTrait;

	public function testSimpleFunction(): void
	{
		$func = new PhpFunction(
			'testFunction',
			[
				new PhpParam('test', Type::fromString('string')),
			],
			[
				'if ($test) {',
				['return 42;'],
				'}',
				'return -1;',
			],
			Type::fromString('void'),
		);

		$renderer = new Php7Renderer();
		$this->assertSourceResult($renderer->renderFunction($func), 'PhpFunctionTest.' . __FUNCTION__);
	}

	public function testFunctionWithMoreThan3Params(): void
	{
		$param3 = new PhpParam('test3', Type::fromString('string'));
		$func = (new PhpFunction(
			'testFunction',
			[
				new PhpParam('test', Type::fromString('string')),
				new PhpParam('test2', Type::fromString('string[]')),
				$param3,
			],
			[
				'return 42;'
			],
		))->setReturnTypeHint(Type::fromString('int'));

		$param3->setValue('testValue');

		$renderer = new Php7Renderer();

		$this->assertSourceResult($renderer->render($func), 'PhpFunctionTest.' . __FUNCTION__);
	}

	public function testFunctionModifyParam(): void
	{
		$func = new PhpFunction(
			'testFunction',
			[
				new PhpParam('test', Type::fromString('string')),
				new PhpParam('test2', Type::fromString('string')),
				new PhpParam('test3', Type::fromString('string[]')),
				new PhpParam('removeMe', Type::fromString('string')),
			],
			[
				'// nothing'
			],
			Type::fromString('int')
		);

		$param = $func->getParam('test2');
		$param->setType(Type::fromString('int'));

		$func->removeParam('removeMe');

		$renderer = new Php7Renderer();

		$this->assertSourceResult($renderer->renderFunction($func), 'PhpFunctionTest.' . __FUNCTION__);
	}

	public function testCloneHandlingParams(): void
	{
		$func = new PhpFunction('testFunction', [
			new PhpParam('test', Type::fromString('string')),
		], [], Type::fromString('void'));

		$cloneFunc = clone $func;

		$this->assertNotSame($func->getParam('test'), $cloneFunc->getParam('test'));
		$this->assertNotSame(
			$func->getParam('test')->getParent(),
			$cloneFunc->getParam('test')->getParent(),
		);
	}

	public function testLegacyFunctionTestSimple(): void
	{
		$func = new PhpFunction('test', [
			new PhpParam('foo', Type::fromString('string|int')),
		], [
			'return $foo * $foo;',
		], Type::fromString('int'));

		$renderer = new Php7Renderer();

		$this->assertSame([
			'/**',
			' * @param string|int $foo',
			' */',
			'function test($foo): int',
			'{',
			['return $foo * $foo;'],
			'}',
		], $renderer->renderFunction($func));
	}

	public function testLegacyFunctionTestWithoutReturnType(): void
	{
		$func = new PhpFunction('test', [
			new PhpParam('foo', Type::fromString('string|int')),
		], [
			'return $foo * $foo;',
		]);
		$renderer = new Php7Renderer();
		$this->assertSame([
			'/**',
			' * @param string|int $foo',
			' */',
			'function test($foo)',
			'{',
			['return $foo * $foo;'],
			'}',
		], $renderer->renderFunction($func));
	}

	public function testLegacyFunctionTestComplexSource(): void
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

		$renderer = new Php7Renderer();
		$this->assertSame('/**
 * @param string|int $foo
 */
function test($foo): int
{
	if (is_int($foo)) {
		return $foo * $foo;
	}
	SecurityValue::apiKey($foo);
}
', FlattenSource::source($renderer->renderFunction($func)));
	}

	public function testLegacyFunctionTestMultilineParams(): void
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

		$renderer = new Php7Renderer();

		$this->assertSame([
			'/**',
			' * @param string|int $barIpsumLong',
			' */',
			'function test(',
			[
				'string $fooIpsumLong,',
				'$barIpsumLong,',
				'int $bazIpsumLong,',
				'string $fozIpsumLong,',
				'string $alzIpsumLong,',
				'string $qweIpsumLong,',
				'string $rtyIpsumLong',
			],
			'): int {',
			['return $foo * $foo;'],
			'}',
		], $renderer->renderFunction($func));
	}
}
