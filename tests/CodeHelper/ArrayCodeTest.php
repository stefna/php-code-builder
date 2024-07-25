<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\CodeHelper;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\CodeHelper\ArrayCode;
use Stefna\PhpCodeBuilder\CodeHelper\InstantiateClass;
use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\PhpVariable;
use Stefna\PhpCodeBuilder\ValueObject\Type;

final class ArrayCodeTest extends TestCase
{
	public function testSimpleAssocArray(): void
	{
		$array = new ArrayCode([
			'test1' => 2,
			'test2' => 'string',
			'test3' => true,
		]);

		$this->assertSame("[
	'test1' => 2,
	'test2' => 'string',
	'test3' => true,
]", trim(FlattenSource::source($array->getSourceArray())));
	}

	public function testCustomIndentLevel(): void
	{
		$array = new ArrayCode([
			'test1' => 2,
			'test2' => 'string',
			'test3' => true,
		]);

		$this->assertSame([
			'[',
			[
				"'test1' => 2,",
				"'test2' => 'string',",
				"'test3' => true,",
			],
			']',
		], $array->getSourceArray(1));
	}

	public function testNestedAssocArray(): void
	{
		$array = new ArrayCode([
			'test1' => 2,
			'test2' => 'string',
			'test3' => true,
			'test4' => [
				'sub1' => 'test',
			],
		]);

		$this->assertSame("[
	'test1' => 2,
	'test2' => 'string',
	'test3' => true,
	'test4' => [
		'sub1' => 'test',
	],
]", trim(FlattenSource::source($array->getSourceArray())));
	}

	public function testSimpleArray(): void
	{
		$array = new ArrayCode([
			'string',
			1,
			false,
			[
				'assoc' => true,
			],
		]);

		$this->assertSame("[
	'string',
	1,
	false,
	[
		'assoc' => true,
	],
]", trim(FlattenSource::source($array->getSourceArray())));
	}

	public function testArrayWithAssignMeantFromVariable(): void
	{
		$var = PhpVariable::protected('testVar', Type::fromString('string'));
		$array = new ArrayCode([
			'test1' => $var,
		]);

		$this->assertSame("[
	'test1' => \$this->testVar,
]", trim(FlattenSource::source($array->getSourceArray())));
	}

	public function testSimpleList(): void
	{
		$array = new ArrayCode([
			'a',
		]);

		$this->assertSame("[
	'a',
]", trim(FlattenSource::source($array->getSourceArray())));
	}

	public function testListOfVariable(): void
	{
		$var = PhpVariable::protected('testVar', Type::fromString('string'));
		$array = new ArrayCode([
			$var,
		]);

		$this->assertSame('[
	$this->testVar,
]', trim(FlattenSource::source($array->getSourceArray())));
	}

	public function testListOfTypes(): void
	{
		$array = new ArrayCode([
			'test' => Type::fromString(\RangeException::class),
			'self' => Type::fromString(self::class),
		]);

		$this->assertSame("[
	'test' => RangeException::class,
	'self' => Stefna\PhpCodeBuilder\Tests\CodeHelper\ArrayCodeTest::class,
]", trim(FlattenSource::source($array->getSourceArray())));
	}

	public function testComplexArray(): void
	{
		$array = new ArrayCode([
			'test1' => new InstantiateClass('Test', ['a', 'b', 'c', 'd']),
			'test2' => new InstantiateClass('Test', ['a', 'b', 'c', 'd']),
		]);

		$this->assertSame([
			'[',
			[
				"'test1' => new Test(",
				[
					"'a',",
					"'b',",
					"'c',",
					"'d'",
				],
				'),',
				"'test2' => new Test(",
				[
					"'a',",
					"'b',",
					"'c',",
					"'d'",
				],
				'),',
			],
			']',
		], $array->getSourceArray());
	}
}
