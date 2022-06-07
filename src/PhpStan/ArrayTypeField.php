<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\PhpStan;

use Stefna\PhpCodeBuilder\PhpDocElement;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;
use Stefna\PhpCodeBuilder\ValueObject\Type;

final class ArrayTypeField extends PhpDocElement
{
	public function __construct(
		private string $name,
		private array $fieldMap,
	) {
		parent::__construct('phpstan-type', Type::empty(), '', '');
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getIdentifier(): Identifier
	{
		return Identifier::fromString($this->name);
	}

	/**
	 * Returns the whole row of generated comment source
	 *
	 * @return string
	 */
	public function getSource(): string
	{
		$ret = ' * @phpstan-type ';
		$ret .= $this->name;
		$ret .= ' array{';
		$ret .= PHP_EOL;
		foreach ($this->fieldMap as $field => $type) {
			if ($type instanceof Type) {
				$type = $type->getType();
			}
			if ($type instanceof Identifier) {
				$type = $type->getAlias() ?? $type->getName();
			}
			$ret .= ' *     ' . $field . ': ' . $type . ',' . PHP_EOL;
		}
		$ret .= ' * }' . PHP_EOL;

		return $ret;
	}
}
