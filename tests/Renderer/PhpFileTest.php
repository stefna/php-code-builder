<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\Renderer;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\PhpClass;
use Stefna\PhpCodeBuilder\PhpFile;
use Stefna\PhpCodeBuilder\PhpMethod;
use Stefna\PhpCodeBuilder\PhpParam;
use Stefna\PhpCodeBuilder\PhpVariable;
use Stefna\PhpCodeBuilder\Renderer\Php7Renderer;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;
use Stefna\PhpCodeBuilder\ValueObject\Type;

final class PhpFileTest extends TestCase
{
	use AssertResultTrait;

	public function testSingleClassFile()
	{
		$class = new PhpClass(
			Identifier::fromString(Test\TestClass::class),
			extends: \DateTimeImmutable::class,
			implements: [Identifier::fromString(\JsonSerializable::class)]
		);
		$var = PhpVariable::private('param1', Type::fromString('string|int'));
		$ctor = PhpMethod::constructor([
			PhpParam::fromVariable($var),
		], [], true);
		$class->addMethod($ctor);

		$var2 = PhpVariable::public('var1', Type::fromString('string|int|null'));
		$class->addVariable($var2);

		$ctor->addParam(new PhpParam('param2', Type::fromString('?int'), autoCreateVariable: true));
		$ctor->addParam(new PhpParam('noneAssigned', Type::fromString('float')));

		$renderer = new Php7Renderer();

		$this->assertSourceResult(
			$renderer->renderFile(PhpFile::createFromClass($class)),
			'PhpFileTest.' . __FUNCTION__,
		);
	}

}
