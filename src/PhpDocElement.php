<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\ValueObject\Type;

/**
 * Class that represents a element (var, param, throws etc.) in a comment in php
 *
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @author Andreas Sundqvist <andreas@stefna.is>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class PhpDocElement
{
	/*** @var string */
	private $type;
	/*** @var Type|null */
	private $datatype;
	/*** @var string */
	private $variableName;
	/*** @var string */
	private $description;

	/**
	 * @param Type|string|null $dataType
	 */
	public function __construct(string $type, $dataType, string $variableName, string $description)
	{
		$this->type = $type;
		$this->datatype = is_string($dataType) ? Type::fromString($dataType) : $dataType;
		$this->variableName = $variableName;
		$this->description = $description;
	}

	/**
	 * Returns the whole row of generated comment source
	 *
	 * @return string
	 */
	public function getSource(): string
	{
		$ret = ' * ';

		$ret .= '@' . $this->type;

		if ($this->datatype) {
			$ret .= ' ' . $this->datatype->getDocBlockTypeHint();
		}

		if ($this->variableName !== '') {
			$ret .= ' $' . $this->variableName;
		}

		if ($this->description !== '') {
			$ret .= ' ' . $this->description;
		}

		$ret .= PHP_EOL;

		return $ret;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function getDataType(): Type
	{
		return $this->datatype;
	}

	public function getVariableName(): string
	{
		return $this->variableName;
	}

	public function getDescription(): string
	{
		return $this->description;
	}

	public function getHashCode(): string
	{
		return md5(implode('', [
			$this->type,
			$this->variableName,
			$this->description,
			($this->datatype ? $this->datatype->getDocBlockTypeHint() : ''),
		]));
	}
}
