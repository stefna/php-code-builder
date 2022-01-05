<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\CodeHelper;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\CodeHelper\ClassMethodCall;
use Stefna\PhpCodeBuilder\CodeHelper\NullSafeOperator;
use Stefna\PhpCodeBuilder\CodeHelper\VariableReference;

final class NullSafeOperatorTest extends TestCase
{
	public function test(): void
	{
		$call = new ClassMethodCall(new VariableReference('test'), 'method');
		$nullSafe = new NullSafeOperator($call);
		$this->assertSame(['$test?->method()'], $nullSafe->getSourceArray());
	}

	public function testCreateClassMethodCall(): void
	{
		$nullSafe = NullSafeOperator::create(new VariableReference('test'), 'method');
		$this->assertSame(['$test?->method()'], $nullSafe->getSourceArray());
	}
}
