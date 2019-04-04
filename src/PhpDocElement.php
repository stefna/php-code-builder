<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

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

	/*** @var string */
	private $datatype;

	/*** @var string*/
	private $variableName;

	/*** @var string */
	private $description;

	public function __construct(string $type, string $dataType, string $variableName, string $description)
	{
		$this->type = $type;
		$this->datatype = $dataType;
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

		if ($this->datatype !== '') {
			$ret .= ' ' . $this->datatype;
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

	public function getDataType(): string
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
}
