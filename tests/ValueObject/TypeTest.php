<?php declare(strict_types=1);

namespace ValueObject;

use Stefna\PhpCodeBuilder\PhpClass;
use Stefna\PhpCodeBuilder\ValueObject\Type;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
	public function testNeedDocBlock(): void
	{
		$type = Type::fromString('string|number|null');
		$this->assertTrue($type->isUnion());
		$this->assertTrue($type->isNullable());
		$this->assertTrue($type->needDockBlockTypeHint());
	}

	public function testNullDontMakeUnion(): void
	{
		$type = Type::fromString('string|null');
		$this->assertFalse($type->isUnion());
		$this->assertTrue($type->isNullable());
		$this->assertFalse($type->needDockBlockTypeHint());
	}

	public function testTypeHint(): void
	{
		$type = Type::fromString('string|null');

		$this->assertSame('?string', $type->getTypeHint());
		$this->assertSame('string|null', $type->getDocBlockTypeHint());
	}

	public function testDocBlockTypeHintOrder(): void
	{
		$type = Type::fromString('null|int');

		$this->assertSame('?int', $type->getTypeHint());
		$this->assertSame('int|null', $type->getDocBlockTypeHint());
	}

	public function testParseTypeHint(): void
	{
		$type = Type::fromString('?int');
		$this->assertTrue($type->isNullable());
		$this->assertSame('?int', $type->getTypeHint());
		$this->assertSame('int|null', $type->getDocBlockTypeHint());
	}

	public function testTypeHintAlias(): void
	{
		$type = Type::fromString('double');
		$this->assertFalse($type->isNullable());
		$this->assertSame('float', $type->getTypeHint());
		$this->assertSame('float', $type->getDocBlockTypeHint());
	}

	public function testArrayOf(): void
	{
		$type = Type::fromString('string[]');
		$this->assertTrue($type->isArray());
		$this->assertSame('array', $type->getTypeHint());
		$this->assertSame('string[]', $type->getDocBlockTypeHint());
	}

	public function testNamespacedType(): void
	{
		$type = Type::fromString(PhpClass::class);
		$this->assertTrue($type->isTypeNamespaced());
		$this->assertNotSame(PhpClass::class, $type->getTypeHint());
		$this->assertSame('\\' . PhpClass::class, $type->getTypeHint());
	}

	public function testRootNamespace(): void
	{
		$type = Type::fromString(\DateTimeImmutable::class);
		$this->assertFalse($type->isTypeNamespaced());
		$this->assertSame(\DateTimeImmutable::class, $type->getTypeHint());
		$this->assertNotSame('\\' . \DateTimeImmutable::class, $type->getTypeHint());
	}

	/**
	 * @dataProvider invalidInput
	 */
	public function testInvalidInput(string $input): void
	{
		$this->expectException(\InvalidArgumentException::class);
		$type = Type::fromString($input);
		var_dump($type);
	}

	public function invalidInput(): array
	{
		return [
			['null|null'],
			[''],
		];
	}

	/**
	 * @dataProvider invalidTypeHints
	 */
	public function testInvalidReturnTypeHints(string $input): void
	{
		$type = Type::fromString($input);
		$this->assertNull($type->getTypeHint());
	}

	public function invalidTypeHints(): array
	{
		return [
			['mixed'],
			['resource'],
			['static'],
		];
	}

	/**
	 * @dataProvider arrayTypes
	 */
	public function testArrayTypes(string $input, ?string $expectedType): void
	{
		$type = Type::fromString($input);
		$this->assertSame($expectedType, $type->getArrayType());
	}

	public function arrayTypes()
	{
		return [
			['int[]', 'int'],
			['array<int, string>', 'string'],
			['array<string,int>', 'int'],
			['array<int, DateTime>', 'DateTime'],
			['Uuid[]', 'Uuid'],
			['string', null],
		];
	}

	public function testSimplifiedName(): void
	{
		$type = Type::fromString(self::class);
		$this->assertSame(self::class, $type->getType());
		$type->simplifyName();
		$this->assertTrue($type->isSimplified());
		$this->assertSame('TypeTest', $type->getType());
	}

	public function testUnionTypeDocBlock(): void
	{
		$type = Type::empty();
		$type->addUnion(Type::fromString('string'));
		$type->addUnion(Type::fromString('float'));
		$this->assertSame('string|float', $type->getDocBlockTypeHint());
	}
}
