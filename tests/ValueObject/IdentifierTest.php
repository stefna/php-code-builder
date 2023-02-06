<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\ValueObject;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;
use Stefna\PhpCodeBuilder\ValueObject\Type;

final class IdentifierTest extends TestCase
{
	public function testWithNamespaceRespectInstanceMap(): void
	{
		$identity1 = Identifier::fromString(\Test\Random\ClassString::class);
		$identity2 = Identifier::fromString('ClassString');

		$this->assertNotSame($identity1, $identity2);
		$identity3 = $identity2->withNamespace('Test\Random');
		$this->assertNotSame($identity2, $identity3);
		$this->assertSame($identity1, $identity3);
		$this->assertTrue($identity1->equal($identity3));
		$identity4 = $identity2->withNamespace('Test\NewRandom');
		$this->assertNotSame($identity3, $identity4);
	}

	public function testFromObject(): void
	{
		$type = Type::fromString('int');
		$identity = Identifier::fromObject($type);

		$this->assertSame(Type::class, $identity->getFqcn());
	}
}
