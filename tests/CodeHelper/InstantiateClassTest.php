<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\CodeHelper;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\CodeHelper\ArrayCode;
use Stefna\PhpCodeBuilder\CodeHelper\InstantiateClass;
use Stefna\PhpCodeBuilder\CodeHelper\StaticMethodCall;
use Stefna\PhpCodeBuilder\CodeHelper\VariableReference;
use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\Renderer\Php7Renderer;
use Stefna\PhpCodeBuilder\Tests\Renderer\AssertResultTrait;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;

final class InstantiateClassTest extends TestCase
{
	use AssertResultTrait;

	public function testSimple(): void
	{
		$call = new InstantiateClass(Identifier::fromString(StaticMethodCall::class), []);

		$this->assertSame(
			'new StaticMethodCall()',
			trim(FlattenSource::source($call->getSourceArray())),
		);
	}

	public function testComplex(): void
	{
		$call = new InstantiateClass(Identifier::fromString(StaticMethodCall::class), [
			'site-bearer-token',
			new ArrayCode([
				'type' => 'http',
				'scheme' => 'bearer',
				'description' => 'Valid for site specific endpoints',
			]),
			new VariableReference('testVar'),
		]);

		$this->assertSame('new StaticMethodCall(\'site-bearer-token\', [
	\'type\' => \'http\',
	\'scheme\' => \'bearer\',
	\'description\' => \'Valid for site specific endpoints\',
], $testVar)', trim(FlattenSource::source($call->getSourceArray())));
	}

	public function testMixedParamsInInstantiateClass(): void
	{
		$x = new InstantiateClass(
			Identifier::fromString('self'),
			[
				'random',
				'random',
				new ArrayCode(['id']),
				new ArrayCode(['a', 'b', 'c', 'd', 'e', 'f']),
				null,
				null,
			],
		);

		$source = $x->getSourceArray();
		$this->assertSame([
			'new self(',
			[
				"'random',",
				"'random',",
				'[',
				[
					"'id',",
				],
				'],',
				'[',
				["'a',", "'b',", "'c',", "'d',", "'e',", "'f',"],
				'],',
				'null,',
				'null',
			],
			')',
		], $source);

		$this->assertSourceResult(FlattenSource::source($source), 'InstantiateClassTest.testMixedParamsInInstantiateClass');
	}

	public function testMixedParamsWithArrayAsSecondArgument(): void
	{
		$x = new InstantiateClass(
			Identifier::fromString('self'),
			[
				'random',
				new ArrayCode(['id']),
				new ArrayCode(['a', 'b', 'c', 'd', 'e', 'f']),
				null,
				null,
			],
		);

		$source = $x->getSourceArray();

		$this->assertSourceResult(FlattenSource::source($source), 'InstantiateClassTest.testMixedParamsWithArrayAsSecondArgument');
	}
}
