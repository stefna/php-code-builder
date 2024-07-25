<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\CodeHelper;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\CodeHelper\ArrayCode;
use Stefna\PhpCodeBuilder\CodeHelper\ClassMethodCall;
use Stefna\PhpCodeBuilder\CodeHelper\ReturnCode;
use Stefna\PhpCodeBuilder\CodeHelper\StaticMethodCall;
use Stefna\PhpCodeBuilder\CodeHelper\VariableReference;
use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\PhpMethod;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;
use Stefna\PhpCodeBuilder\ValueObject\Type;

final class ReturnCodeTest extends TestCase
{
	public function testEmptyArray(): void
	{
		$array = new ReturnCode(new ArrayCode());

		$this->assertCount(1, $array->getSourceArray());
		$this->assertSame(['return [];'], $array->getSourceArray());
	}

	public function testArray(): void
	{
		$array = new ReturnCode(new ArrayCode([
			'test1' => 2,
			'test2' => 'string',
			'test3' => true,
		]));

		$this->assertCount(3, $array->getSourceArray());
		$this->assertSame([
			'return [',
			[
				'\'test1\' => 2,',
				'\'test2\' => \'string\',',
				'\'test3\' => true,',
			],
			'];',
		], $array->getSourceArray());
	}

	public function testMethodCall(): void
	{
		$call = new ReturnCode(ClassMethodCall::this('setSecurityValue', [
			'site-bearer-token',
			new VariableReference('siteBearerToken'),
		]));

		$this->assertCount(1, $call->getSourceArray());
		$this->assertSame(
			"return \$this->setSecurityValue('site-bearer-token', \$siteBearerToken);\n",
			FlattenSource::source($call->getSourceArray()),
		);
	}

	public function testComplex(): void
	{
		$call = new ClassMethodCall(VariableReference::this(), 'addSecurityScheme', [
			new StaticMethodCall(Identifier::fromString(StaticMethodCall::class), 'test', [
				'site-bearer-token',
				new ArrayCode([
					'type' => 'http',
					'scheme' => 'bearer',
					'description' => 'Valid for site specific endpoints',
				]),
			]),
		]);
		$return = new ReturnCode($call);

		$this->assertSame('return $this->addSecurityScheme(StaticMethodCall::test(\'site-bearer-token\', [
	\'type\' => \'http\',
	\'scheme\' => \'bearer\',
	\'description\' => \'Valid for site specific endpoints\',
]));
', FlattenSource::source($return->getSourceArray()));
	}
}
