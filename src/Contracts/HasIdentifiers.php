<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Contracts;

use Stefna\PhpCodeBuilder\ValueObject\Identifier;

interface HasIdentifiers
{
	/**
	 * @return Identifier[]
	 */
	public function getIdentifiers(): array;
}
