<?php declare(strict_types=1);

namespace CodeHelper;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\CodeHelper\ClassMethodCall;
use Stefna\PhpCodeBuilder\CodeHelper\IfCode;
use Stefna\PhpCodeBuilder\CodeHelper\LineCode;
use Stefna\PhpCodeBuilder\CodeHelper\ReturnCode;
use Stefna\PhpCodeBuilder\CodeHelper\StaticMethodCall;
use Stefna\PhpCodeBuilder\CodeHelper\VariableReference;
use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\PhpMethod;
use Stefna\PhpCodeBuilder\PhpParam;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;
use Stefna\PhpCodeBuilder\ValueObject\Type;

final class IfCodeTest extends TestCase
{
	public function testNullCheck(): void
	{
		$var = new VariableReference('test');
		$if = IfCode::nullCheck($var, [
			new ReturnCode($var),
		]);

		$this->assertSame('if ($test === null) {
	return $test;
}
', FlattenSource::source($if->getSourceArray()));
	}

	public function testIf(): void
	{
		$if = IfCode::instanceOf(
			VariableReference::this('serverConfiguration'),
			Identifier::fromString(WriteableServerConfigurationInterface::class),
			[
				new LineCode(
					new ClassMethodCall(VariableReference::this('serverConfiguration'), 'setSecurityValue', [
						'test-scheme',
						new StaticMethodCall(Identifier::fromString('SecurityValue'), 'apiKey', [
							new VariableReference('token'),
						]),
					])
				),
			]
		);

		$this->assertSame('if ($this->serverConfiguration instanceof WriteableServerConfigurationInterface) {
	$this->serverConfiguration->setSecurityValue(\'test-scheme\', SecurityValue::apiKey($token));
}', trim(FlattenSource::source($if->getSourceArray())));
	}
}
