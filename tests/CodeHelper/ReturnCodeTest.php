<?php declare(strict_types=1);

namespace CodeHelper;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\CodeHelper\ArrayCode;
use Stefna\PhpCodeBuilder\CodeHelper\ClassMethodCall;
use Stefna\PhpCodeBuilder\CodeHelper\ReturnCode;
use Stefna\PhpCodeBuilder\CodeHelper\StaticMethodCall;
use Stefna\PhpCodeBuilder\CodeHelper\VariableReference;
use Stefna\PhpCodeBuilder\PhpMethod;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;
use Stefna\PhpCodeBuilder\ValueObject\Type;

final class ReturnCodeTest extends TestCase
{
	public function testEmptyArray()
	{
		$array = new ReturnCode(new ArrayCode());

		$this->assertCount(1, $array->getSourceArray());
		$this->assertSame(['return [];'], $array->getSourceArray());
	}

	public function testArray()
	{
		$array = new ReturnCode(new ArrayCode([
			'test1' => 2,
			'test2' => "string",
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

	public function testMethodCall()
	{
		$call = new ReturnCode(ClassMethodCall::this('setSecurityValue', [
			'site-bearer-token',
			new VariableReference('siteBearerToken'),
		]));

		$this->assertCount(1, $call->getSourceArray());
		$this->assertSame("return \$this->setSecurityValue('site-bearer-token', \$siteBearerToken);\n", $call->getSource());
	}

	public function testComplex()
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
', $return->getSource());
	}

	public function testIndentationInMethod()
	{
		$method = PhpMethod::public('getSecurity', [], [
			new ReturnCode(new ArrayCode([])),
		], Type::fromString('string[]'));

		$this->assertSame('/**
 * @return string[]
 */
public function getSecurity(): array
{
	return [];
}
', $method->getSource());
	}
}
