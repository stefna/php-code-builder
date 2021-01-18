<?php declare(strict_types=1);

namespace CodeHelper;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\CodeHelper\ClassMethodCall;
use Stefna\PhpCodeBuilder\CodeHelper\LineCode;
use Stefna\PhpCodeBuilder\CodeHelper\VariableReference;

final class LineCodeTest extends TestCase
{
	public function testWithVariableParam()
	{
		$call = ClassMethodCall::this('setSecurityValue', [
			'site-bearer-token',
			new VariableReference('siteBearerToken'),
		]);

		$this->assertSame('$this->setSecurityValue(\'site-bearer-token\', $siteBearerToken);', trim((new LineCode($call))->getSource()));
	}
}
