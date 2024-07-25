<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\CodeHelper;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\CodeHelper\VariableReference;

final class VariableReferenceTest extends TestCase
{
	public function testArrayVariable(): void
	{
		$var = new VariableReference("input['status']");

		$this->assertSame("\$input['status']", $var->toString());
	}

	public function testArrayVariableWithDynamicKey(): void
	{
		$key = new VariableReference('key');
		$var = VariableReference::array('input', $key);

		$this->assertSame('$input[$key]', $var->toString());
	}
}
