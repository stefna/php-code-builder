<?php declare(strict_types=1);

namespace CodeHelper;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\CodeHelper\ArrayCode;
use Stefna\PhpCodeBuilder\CodeHelper\InstantiateClass;
use Stefna\PhpCodeBuilder\CodeHelper\StaticMethodCall;
use Stefna\PhpCodeBuilder\CodeHelper\VariableReference;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;

final class InstantiateClassTest extends TestCase
{
	public function testSimple()
	{
		$call = new InstantiateClass(Identifier::fromString(StaticMethodCall::class), []);

		$this->assertSame('new StaticMethodCall()', trim($call->getSource()));
	}

	public function testComplex()
	{
		$call = new InstantiateClass(Identifier::fromString(StaticMethodCall::class), [
			'site-bearer-token',
			new ArrayCode([
				'type' => 'http',
				'scheme' => 'bearer',
				'description' => 'Valid for site specific endpoints',
			]),
			new VariableReference('testVar'),
		]);

		$this->assertSame('new StaticMethodCall(\'site-bearer-token\', [
	\'type\' => \'http\',
	\'scheme\' => \'bearer\',
	\'description\' => \'Valid for site specific endpoints\',
], $testVar)', trim($call->getSource()));
	}
}
