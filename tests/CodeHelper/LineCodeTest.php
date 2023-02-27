<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\CodeHelper;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\CodeHelper\ClassMethodCall;
use Stefna\PhpCodeBuilder\CodeHelper\LineCode;
use Stefna\PhpCodeBuilder\CodeHelper\VariableReference;
use Stefna\PhpCodeBuilder\FlattenSource;

final class LineCodeTest extends TestCase
{
	public function testWithVariableParam(): void
	{
		$call = ClassMethodCall::this('setSecurityValue', [
			'site-bearer-token',
			new VariableReference('siteBearerToken'),
		]);

		$this->assertSame(
			'$this->setSecurityValue(\'site-bearer-token\', $siteBearerToken);',
			trim(FlattenSource::source((new LineCode($call))->getSourceArray())),
		);
	}
}
