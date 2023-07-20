<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\Renderer;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\PhpAttribute;
use Stefna\PhpCodeBuilder\Renderer\Php8Renderer;

final class PhpAttributeTest extends TestCase
{
	use AssertResultTrait;

	public function testAttribute(): void
	{
		$attribute = new PhpAttribute(\Attribute::class, '\Attribute::TARGET_CLASS');
		$renderer = new Php8Renderer();

		$this->assertSame(['#[Attribute(\Attribute::TARGET_CLASS)]'], $renderer->renderAttribute($attribute));
	}
}
