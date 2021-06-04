<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Renderer;

interface RenderInterface
{
	public function render(array|object $obj): string;
}
