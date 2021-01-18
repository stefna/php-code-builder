<?php declare(strict_types=1);

namespace CodeHelper;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\CodeHelper\ClassMethodCall;
use Stefna\PhpCodeBuilder\CodeHelper\IfCode;
use Stefna\PhpCodeBuilder\CodeHelper\LineCode;
use Stefna\PhpCodeBuilder\CodeHelper\StaticMethodCall;
use Stefna\PhpCodeBuilder\CodeHelper\VariableReference;
use Stefna\PhpCodeBuilder\PhpMethod;
use Stefna\PhpCodeBuilder\PhpParam;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;
use Stefna\PhpCodeBuilder\ValueObject\Type;

final class IfCodeTest extends TestCase
{
	public function testIf()
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
}', trim($if->getSource()));
	}

	public function testIndent()
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
		$method = PhpMethod::public('setValue', [
			new PhpParam('token', Type::fromString('string'))
		], [
			$if,
		]);

		$this->assertSame('public function setValue(string $token)
{
	if ($this->serverConfiguration instanceof WriteableServerConfigurationInterface) {
		$this->serverConfiguration->setSecurityValue(\'test-scheme\', SecurityValue::apiKey($token));
	}
}
', $method->getSource());
	}
}
