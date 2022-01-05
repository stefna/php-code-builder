<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\PhpStan;

use Stefna\PhpCodeBuilder\PhpDocElement;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;
use Stefna\PhpCodeBuilder\ValueObject\Type;

final class TemplateField extends PhpDocElement
{
	public function __construct(
		private string $name,
		private ?Identifier $ofType = null,
	) {
		parent::__construct('template', Type::empty(), '', '');
	}

	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @phpstan-pure
	 */
	public function getOfType(): ?Identifier
	{
		return $this->ofType;
	}

	/**
	 * Returns the whole row of generated comment source
	 *
	 * @return string
	 */
	public function getSource(): string
	{
		$ret = ' * @template ';
		$ret .= $this->name;

		if ($this->ofType) {
			$ret .= ' of ' . $this->ofType->getName();
		}

		$ret .= PHP_EOL;

		return $ret;
	}
}
