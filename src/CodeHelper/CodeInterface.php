<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

interface CodeInterface
{
	public function getSource(int $currentIndent = 0): string;
}
