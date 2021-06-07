<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Renderer;

interface RenderInterface
{
	/**
	 * @param array<array-key, mixed>|object $obj
	 */
	public function render(array|object $obj): string;
}
