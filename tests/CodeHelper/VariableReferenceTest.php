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
}
