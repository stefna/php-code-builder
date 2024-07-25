<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\CodeHelper;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\CodeHelper\ClassMethodCall;
use Stefna\PhpCodeBuilder\CodeHelper\StaticMethodCall;
use Stefna\PhpCodeBuilder\CodeHelper\TernaryOperator;
use Stefna\PhpCodeBuilder\CodeHelper\VariableReference;
use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;

final class TernaryOperatorTest extends TestCase
{
	public function testSimple(): void
	{
		$t = new TernaryOperator(new VariableReference('test'), 'true', 'false');

		$this->assertSame(['$test ? true : false'], $t->getSourceArray());
	}

	public function testSimpleMultiLine(): void
	{
		$class = new VariableReference('classTest');
		$t = new TernaryOperator(
			$class,
			new ClassMethodCall($class, 'test', [
				'test1',
				'test2',
				'test3',
				'test4',
			]),
			'null',
		);

		$this->assertSame('$classTest ? $classTest->test(
	\'test1\',
	\'test2\',
	\'test3\',
	\'test4\'
) : null
', FlattenSource::source($t->getSourceArray()));
	}

	public function testComplexMultiLine(): void
	{
		$class = new VariableReference('classTest');
		$t = new TernaryOperator(
			$class,
			new ClassMethodCall($class, 'success', [
				'test1',
				'test2',
				'test3',
				'test4',
			]),
			new ClassMethodCall($class, 'failure', [
				'test1',
				'test2',
				'test3',
				'test4',
			]),
		);

		$this->assertSame('$classTest ? $classTest->success(
	\'test1\',
	\'test2\',
	\'test3\',
	\'test4\'
) : $classTest->failure(
	\'test1\',
	\'test2\',
	\'test3\',
	\'test4\'
)
', FlattenSource::source($t->getSourceArray()));
	}

	public function testNullableCallFactory(): void
	{
		$call = new StaticMethodCall(Identifier::fromString(VariableReference::class), 'this');
		$var = new VariableReference('test');
		$code = TernaryOperator::nullableCall($var, $call);

		$this->assertSame([
			'$test ? VariableReference::this() : null',
		], $code->getSourceArray());
	}
}
