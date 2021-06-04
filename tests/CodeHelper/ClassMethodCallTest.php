<?php declare(strict_types=1);

namespace CodeHelper;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\CodeHelper\ArrayCode;
use Stefna\PhpCodeBuilder\CodeHelper\ClassMethodCall;
use Stefna\PhpCodeBuilder\CodeHelper\StaticMethodCall;
use Stefna\PhpCodeBuilder\CodeHelper\VariableReference;
use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;

final class ClassMethodCallTest extends TestCase
{
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

		$this->assertSame('$this->addSecurityScheme(StaticMethodCall::test(\'site-bearer-token\', [
	\'type\' => \'http\',
	\'scheme\' => \'bearer\',
	\'description\' => \'Valid for site specific endpoints\',
]))', trim(FlattenSource::source($call->getSourceArray())));
	}

	public function testWithVariableParam()
	{
		$call = new ClassMethodCall(VariableReference::this(), 'setSecurityValue', [
			'site-bearer-token',
			new VariableReference('siteBearerToken'),
		]);

		$this->assertSame(
			'$this->setSecurityValue(\'site-bearer-token\', $siteBearerToken)',
			trim(FlattenSource::source($call->getSourceArray())),
		);
	}

	public function testWithStaticMethodCall()
	{
		$x = new ClassMethodCall(VariableReference::this('serverConfiguration'), 'setSecurityValue', [
			'test-scheme',
			new StaticMethodCall(Identifier::fromString('SecurityValue'), 'apiKey', [
				new VariableReference('token'),
			]),
		]);

		$this->assertSame(
			'$this->serverConfiguration->setSecurityValue(\'test-scheme\', SecurityValue::apiKey($token))',
			trim(FlattenSource::source($x->getSourceArray())),
		);
	}
}
