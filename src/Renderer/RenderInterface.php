<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Renderer;

/**
 * @phpstan-type SourceArray array<int, string|array<int, string>>
 */
interface RenderInterface
{
	/**
	 * @phpstan-param SourceArray|object $obj
	 */
	public function render(array|object $obj): string;
}
