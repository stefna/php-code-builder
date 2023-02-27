<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\CodeHelper;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\CodeHelper\AssignmentCode;
use Stefna\PhpCodeBuilder\CodeHelper\ForeachCode;
use Stefna\PhpCodeBuilder\CodeHelper\VariableReference;
use Stefna\PhpCodeBuilder\FlattenSource;

final class ForeachCodeTest extends TestCase
{
	public function testSimpleLoop(): void
	{
		$loop = new ForeachCode(new VariableReference('test'), function (VariableReference $key, VariableReference $value) {
			return ['$x[' . $key->toString() . '] = ' . $value->toString() . ';'];
		});
		$this->assertSame([
			'foreach ($test as $key => $value) {',
			['$x[$key] = $value;'],
			'}',
		], $loop->getSourceArray());
	}

	public function testLoopWithPrefixedNames(): void
	{
		$loop = new ForeachCode(
			new VariableReference('test'),
			function (VariableReference $key, VariableReference $value) {
				return ['$x[' . $key->toString() . '] = ' . $value->toString() . ';'];
			},
			'prefix'
		);
		$this->assertSame([
			'foreach ($test as $prefixKey => $prefixValue) {',
			['$x[$prefixKey] = $prefixValue;'],
			'}',
		], $loop->getSourceArray());
	}
	public function testLoopThatReturnCodeInterface(): void
	{
		$loop = new ForeachCode(
			new VariableReference('test'),
			function (VariableReference $key, VariableReference $value) {
				return [
					new AssignmentCode(
						VariableReference::array('x', $key),
						$value,
					),
				];
			},
			'prefix'
		);

		$source = FlattenSource::source($loop->getSourceArray());
		$this->assertSame(implode(PHP_EOL, [
			'foreach ($test as $prefixKey => $prefixValue) {',
			'	$x[$prefixKey] = $prefixValue;',
			'}',
		]), trim($source));
	}
}
