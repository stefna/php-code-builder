<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\Renderer;

use JetBrains\PhpStorm\ArrayShape;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Stefna\OpenApiRuntime\ServerConfiguration\SecurityScheme;
use Stefna\PhpCodeBuilder\PhpAttribute;
use Stefna\PhpCodeBuilder\PhpDocComment;
use Stefna\PhpCodeBuilder\PhpVariable;
use Stefna\PhpCodeBuilder\Renderer\Php74Renderer;
use Stefna\PhpCodeBuilder\Renderer\Php7Renderer;
use Stefna\PhpCodeBuilder\Renderer\Php81Renderer;
use Stefna\PhpCodeBuilder\Renderer\Php8Renderer;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;
use Stefna\PhpCodeBuilder\ValueObject\Type;

final class PhpVariableTest extends TestCase
{
	public function testProtectedSimplePhp7(): void
	{
		$variable = PhpVariable::protected('test', Type::fromString('string'));

		$renderer = new Php7Renderer();

		$this->assertSame([
			'/** @var string */',
			'protected $test;',
		], $renderer->renderVariable($variable));
	}

	public function testProtectedSimplePhp74(): void
	{
		$variable = PhpVariable::public('test', Type::fromString('string'));

		$renderer = new Php74Renderer();

		$this->assertSame(['public string $test;'], $renderer->renderVariable($variable));
	}

	public function testProtectedWithComplexCommentPhp7(): void
	{
		$variable = PhpVariable::protected('test', Type::fromString('string'));
		$comment = PhpDocComment::var($variable->getType());
		$comment->setDescription('Test description');
		$variable->setComment($comment);

		$renderer = new Php7Renderer();

		$this->assertSame([
			'/**',
			' * Test description',
			' *',
			' * @var string',
			' */',
			'protected $test;',
		], $renderer->renderVariable($variable));
	}

	public function testProtectedWithComplexCommentPhp74(): void
	{
		$variable = PhpVariable::protected('test', Type::fromString('string'));
		$comment = PhpDocComment::var($variable->getType());
		$comment->setDescription('Test description');
		$variable->setComment($comment);

		$renderer = new Php74Renderer();

		$this->assertSame([
			'/**',
			' * Test description',
			' */',
			'protected string $test;',
		], $renderer->renderVariable($variable));
	}

	public function testVariableWithoutAccess(): void
	{
		$var = new PhpVariable('', Identifier::simple('test'), Type::empty());

		$renderer = new Php7Renderer();

		$this->assertSame(['public $test;'], $renderer->renderVariable($var));
	}

	public function testStaticVariable(): void
	{
		$var = PhpVariable::private('test', Type::fromString('Class'))->setStatic();

		$renderer = new Php7Renderer();
		$this->assertSame([
			'/** @var Class */',
			'private static $test;',
		], $renderer->renderVariable($var));

		$renderer = new Php74Renderer();
		$this->assertSame(['private static Class $test;'], $renderer->renderVariable($var));
	}

	#[DataProvider('defaultValueProvider')]
	public function testVariableWithDefaultValue(Type $type, $value, $expected): void
	{
		$variable = PhpVariable::private('test', $type);
		$variable->setInitializedValue($value);

		$renderer = new Php7Renderer();

		$lines = $renderer->renderVariable($variable);

		$expectedArr = [];
		if ($type->getDocBlockTypeHint()) {
			$expectedArr[] = '/** @var ' . $type->getDocBlockTypeHint() . ' */';
		}
		$expectedArr[] = 'private $test = ';
		if (is_array($expected)) {
			$expectedArr[array_key_last($expectedArr)] .= array_shift($expected);
			foreach ($expected as $x) {
				$expectedArr[] = $x;
			}
			$expectedArr[array_key_last($expectedArr)] .= ';';
		}
		else {
			$expectedArr[array_key_last($expectedArr)] .= $expected . ';';
		}

		if (is_string($lines)) {
			$this->assertSame($expectedArr[0], $lines);
		}
		else {
			$this->assertSame($expectedArr, $lines);
		}
	}

	#[DataProvider('defaultValueProvider')]
	public function test74VariableWithDefaultValue(Type $type, $value, $expected): void
	{
		$variable = PhpVariable::private('test', $type);
		$variable->setInitializedValue($value);

		$renderer = new Php74Renderer();

		$lines = $renderer->renderVariable($variable);

		$expectedArr = [];
		if ($type->needDockBlockTypeHint()) {
			$expectedArr[] = '/** @var ' . $type->getDocBlockTypeHint() . ' */';
		}
		$typeStr = $type->getTypeHint();
		$typeStr = $typeStr ? $typeStr . ' ' : '';
		$expectedArr[] = 'private ' . ($typeStr) . '$test = ';
		if (is_array($expected)) {
			$expectedArr[array_key_last($expectedArr)] .= array_shift($expected);
			foreach ($expected as $x) {
				$expectedArr[] = $x;
			}
			$expectedArr[array_key_last($expectedArr)] .= ';';
		}
		else {
			$expectedArr[array_key_last($expectedArr)] .= $expected . ';';
		}

		if (is_string($lines)) {
			$this->assertSame($expectedArr[0], $lines);
		}
		else {
			$this->assertSame($expectedArr, $lines);
		}
	}

	/**
	 * @return array<mixed>
	 */
	public static function defaultValueProvider(): array
	{
		return [
			[Type::fromString('string'), 'test value', '\'test value\''],
			[Type::fromString('int'), 2, '2'],
			[Type::empty(), true, 'true'],
			[
				Type::fromString('string[]'),
				['test 1', 'test 2'],
				[
					'[',
					[
						'\'test 1\',',
						'\'test 2\',',
					],
					']',
				],
			],
		];
	}

	public function testArrayAsDefaultValue(): void
	{
		$var = new PhpVariable(
			PhpVariable::PROTECTED_ACCESS,
			Identifier::simple('securitySchemes'),
			Type::fromString(SecurityScheme::class . '[]'),
			[],
		);

		$renderer = new Php7Renderer();
		$this->assertSame([
			'/** @var \Stefna\OpenApiRuntime\ServerConfiguration\SecurityScheme[] */',
			'protected $securitySchemes = [];',
		], $renderer->renderVariable($var));
	}

	public function testReadOnlyPhp8(): void
	{
		$var = new PhpVariable(
			PhpVariable::PUBLIC_ACCESS,
			Identifier::simple('test'),
			Type::fromString('string'),
			readOnly: true,
		);

		$renderer = new Php8Renderer();
		$this->assertSame([
			'public string $test;',
		], $renderer->renderVariable($var));
	}

	public function testReadOnlyPhp81(): void
	{
		$var = new PhpVariable(
			PhpVariable::PUBLIC_ACCESS,
			Identifier::simple('test'),
			Type::fromString('string'),
			readOnly: true,
		);

		$renderer = new Php81Renderer();
		$this->assertSame([
			'public readonly string $test;',
		], $renderer->renderVariable($var));
	}

	public function testRenderNotRemovingDocBlockWithExpandedTypeInfo(): void
	{
		$variable = PhpVariable::protected('test', Type::fromString('null|string[]'));
		$renderer = new Php74Renderer();

		$this->assertSame([
			'/** @var string[]|null */',
			'protected ?array $test = null;',
		], $renderer->renderVariable($variable));
	}

	public function testVariableWithMixedType(): void
	{
		$variable = PhpVariable::protected('test', Type::fromString('mixed'));

		$renderer = new Php8Renderer();

		$this->assertSame([
			'protected mixed $test;',
		], $renderer->renderVariable($variable));
	}

	public function testVariableWithMixedAndNullType(): void
	{
		$variable = PhpVariable::protected('test', Type::fromString('mixed|null'));

		$renderer = new Php8Renderer();

		$this->assertSame([
			'protected mixed $test;',
		], $renderer->renderVariable($variable));
	}

	public function testVariableWithMixedType74(): void
	{
		$variable = PhpVariable::protected('test', Type::fromString('mixed'));

		$renderer = new Php74Renderer();

		$this->assertSame([
			'/** @var mixed */',
			'protected $test;',
		], $renderer->renderVariable($variable));
	}

	public function testVariableWithMixedAndNullType74(): void
	{
		$variable = PhpVariable::protected('test', Type::fromString('mixed|null'));

		$renderer = new Php74Renderer();

		$this->assertSame([
			'/** @var mixed */',
			'protected $test;',
		], $renderer->renderVariable($variable));
	}

	public function testAttributesOn74(): void
	{
		$variable = PhpVariable::protected('test', Type::fromString('array<string, scalar>'));
		$variable->addAttribute(new PhpAttribute(HiddenVariable::class));
		$variable->addAttribute(new PhpAttribute(ArrayShape::class, '["f" => "int", "string", "x" => "float"]'));
		$renderer = new Php74Renderer();

		$this->assertSame([
			'/** @var array<string, scalar> */',
			'protected array $test;',
		], $renderer->renderVariable($variable));
	}

	public function testAttributes(): void
	{
		$variable = PhpVariable::protected('test', Type::fromString('array<string, scalar>'));
		$variable->addAttribute(new PhpAttribute(HiddenVariable::class));
		$variable->addAttribute(new PhpAttribute(ArrayShape::class, '["f" => "int", "string", "x" => "float"]'));
		$renderer = new Php8Renderer();

		$this->assertSame([
			'/** @var array<string, scalar> */',
			'#[HiddenVariable]',
			'#[ArrayShape(["f" => "int", "string", "x" => "float"])]',
			'protected array $test;',
		], $renderer->renderVariable($variable));
	}
}
