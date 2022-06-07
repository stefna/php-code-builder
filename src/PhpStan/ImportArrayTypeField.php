<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\PhpStan;

use Stefna\PhpCodeBuilder\PhpDocElement;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;
use Stefna\PhpCodeBuilder\ValueObject\Type;

final class ImportArrayTypeField extends PhpDocElement
{
	private Identifier $arrayType;

	public function __construct(
		private Identifier $fromClass,
		Identifier|string $arrayType,
		private ?string $alias = null,
	) {
		$this->arrayType = Identifier::fromUnknown($arrayType);
		if ($this->alias) {
			$dataType = clone $this->arrayType;
			$dataType->setAlias($this->alias);
		}
		parent::__construct('phpstan-import-type', Type::fromIdentifier($dataType ?? $this->arrayType), '', '');
	}

	public function getName(): string
	{
		return $this->arrayType->getName();
	}

	public function getIdentifier(): Identifier
	{
		return $this->arrayType;
	}

	public function getFromClass(): Identifier
	{
		return $this->fromClass;
	}

	/**
	 * Returns the whole row of generated comment source
	 *
	 * @return string
	 */
	public function getSource(): string
	{
		$ret = ' * @phpstan-import-type ';
		$ret .= $this->getName();
		$ret .= ' from ';
		$ret .= $this->fromClass->getName();
		if ($this->alias) {
			$ret .= ' as ' . $this->alias;
		}
		$ret .= PHP_EOL;

		return $ret;
	}
}
