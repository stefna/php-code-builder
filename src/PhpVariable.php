<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

/**
 * Class that represents the source code for a variable in php
 *
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @author Andreas Sundqvist <andreas@stefna.is>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class PhpVariable extends PhpElement
{
	private const NO_VALUE = '__PhpVariable_NoValue__';

	/** @var PhpDocComment|null */
	private $comment;

	/** @var string */
	private $initializedValue;

	/** @var string */
	private $type;

	/** @var bool */
	private $static = false;

	public function __construct(
		string $access,
		string $identifier,
		$value = self::NO_VALUE,
		string $type = '',
		PhpDocComment $comment = null
	) {
		if ($type && !$comment && PHP_VERSION_ID < 70400) {
			$comment = PhpDocComment::var($type);
		}
		$this->comment = $comment;
		$this->access = $access;
		$this->identifier = $identifier;
		$this->initializedValue = $value;
		$this->type = $type;
	}

	public function setStatic(): self
	{
		$this->static = true;
		return $this;
	}

	/**
	 * Returns the complete source code for the variable
	 *
	 * @return string
	 */
	public function getSource(): string
	{
		$ret = '';

		if ($this->comment) {
			$ret .= $this->getSourceRow($this->comment->getSource());
		}

		$dec = $this->access;
		if ($this->type && PHP_VERSION_ID >= 70400) {
			$dec .= ' ' . $this->type;
		}

		$dec .= ' $' . $this->identifier;
		if ($this->initializedValue !== self::NO_VALUE) {
			$dec .= ' = ' . FormatValue::format($this->initializedValue);
		}
		$dec .= ';';

		$sourceRow = $this->getSourceRow($dec);
		// Strip unnecessary null as default value
		$sourceRow = preg_replace('@\s+=\s+null;@', ';', $sourceRow);
		$ret .= $sourceRow;

		return $ret;
	}

	public function setInitializedValue(string $initializedValue): PhpVariable
	{
		$this->initializedValue = $initializedValue;
		return $this;
	}

	public function getInitializedValue(): string
	{
		return $this->initializedValue === self::NO_VALUE ? '' : $this->initializedValue;
	}
}
