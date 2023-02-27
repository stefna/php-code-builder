<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\CodeHelper;

use PHPUnit\Framework\TestCase;

final class ForeachCodeTest extends TestCase
{
	public function testSimpleLoop(): void
	{
		$loop = new ForeachCode(new VariableReference('test'), fn ($key, $value) => ['$x[' . $key . '] = ' . $value . ';']);
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
			fn ($key, $value) => ['$x[' . $key . '] = ' . $value . ';'],
			'prefix'
		);
		$this->assertSame([
			'foreach ($test as $prefixKey => $prefixValue) {',
			['$x[$prefixKey] = $prefixValue;'],
			'}',
		], $loop->getSourceArray());
	}
}
