<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

interface CodeInterface
{
	/**
	 * @return list<string|string[]>
	 */
	public function getSourceArray(): array;
}
