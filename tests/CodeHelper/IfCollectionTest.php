<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\CodeHelper;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\CodeHelper\ForeachCode;
use Stefna\PhpCodeBuilder\CodeHelper\IfHelper\IfCollection;
use Stefna\PhpCodeBuilder\CodeHelper\IfHelper\IfFactory;
use Stefna\PhpCodeBuilder\CodeHelper\ReturnCode;
use Stefna\PhpCodeBuilder\CodeHelper\VariableReference;
use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;

final class IfCollectionTest extends TestCase
{
	public function testIfElseIf(): void
	{
		$collection = new IfCollection();
		$var = new VariableReference('test');
		$collection[] = IfFactory::instanceOf(
			$var,
			Identifier::fromString(WriteableServerConfigurationInterface::class),
			[
				new ReturnCode($var),
			]
		);
		$collection[] = IfFactory::nullCheck($var, [
			new ReturnCode($var),
		]);

		$this->assertEquals(
			'if ($test instanceof WriteableServerConfigurationInterface) {
	return $test;
}
elseif ($test === null) {
	return $test;
}
',
			FlattenSource::source($collection->getSourceArray()),
		);
	}

	public function testElse(): void
	{
		$collection = new IfCollection();
		$var = new VariableReference('test');
		$collection[] = IfFactory::methodExist($var, 'toString', [
			new ReturnCode($var),
		]);
		$collection[] = IfFactory::nullCheck($var, [
			new ReturnCode($var),
		]);

		$collection->addElse([
			new ReturnCode($var),
		]);

		$this->assertEquals(
			'if (is_object($test) && method_exists($test, \'toString\')) {
	return $test;
}
elseif ($test === null) {
	return $test;
}
else {
	return $test;
}
',
			FlattenSource::source($collection->getSourceArray()),
		);
	}

	public function testLoopWithIfCollection(): void
	{
		$loop = new ForeachCode(new VariableReference('test'), function (VariableReference $key, VariableReference $value) {
			$collection = new IfCollection();
			$collection[] = IfFactory::instanceOf($value, RequestBody::class, [
				'$return[] = ' . $value->toString() . ';',
			]);
			$collection[] = IfFactory::methodExist($value, 'toString', [
				'$return[] = ' . $value->toString() . '->toString();',
			]);

			return [
				$collection,
			];
		});

		$this->assertSame(
			'foreach ($test as $key => $value) {
	if ($value instanceof RequestBody) {
		$return[] = $value;
	}
	elseif (is_object($value) && method_exists($value, \'toString\')) {
		$return[] = $value->toString();
	}
}
',
			FlattenSource::source($loop->getSourceArray())
		);
	}
}
