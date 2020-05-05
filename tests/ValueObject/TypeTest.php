<?php declare(strict_types=1);

namespace ValueObject;

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
			['number'],
		];
	}
}
