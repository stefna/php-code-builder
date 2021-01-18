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

		$this->assertSame([
			'/**',
			' * @param string|int $foo',
			' */',
			'function test($foo): int',
			'{',
			['return $foo * $foo;'],
			'}',
		], $func->getSourceArray());
	}

	public function testConstructorMethod()
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
		], $method->getSourceArray());
	}

	public function testWithoutReturnType()
	{
		$func = new PhpFunction('test', [
			new PhpParam('foo', Type::fromString('string|int')),
		], [
			'return $foo * $foo;',
		]);
		$this->assertSame([
			'/**',
			' * @param string|int $foo',
			' */',
			'function test($foo)',
			'{',
			['return $foo * $foo;'],
			'}',
		], $func->getSourceArray());
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
', $func->getSource());
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
		], $func->getSourceArray());
	}

	public function testAbstractMethod()
	{
		$func = new PhpMethod('private', 'test', [
			new PhpParam('foo', Type::fromString('string|int')),
		], [
			'return $foo * $foo;',
		], Type::fromString('int'));
		$func->setAbstract();

		$this->assertSame([
			'/**',
			' * @param string|int $foo',
			' */',
			'abstract private function test($foo): int;',
		], $func->getSourceArray());
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

		$this->assertSame('/**
 * @param string|int $barIpsumLong
 */
abstract protected static function test(
	string $fooIpsumLong,
	$barIpsumLong,
	int $bazIpsumLong,
	string $fozIpsumLong,
	string $alzIpsumLong,
	string $qweIpsumLong,
	string $rtyIpsumLong
): int;
', $func->getSource());
	}
}
