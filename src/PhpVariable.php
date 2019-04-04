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
	/** @var PhpDocComment|null */
	private $comment;

	/** @var string */
	private $initializedValue;

	/** @var string */
	private $type;

	/**
	 * @param string $access
	 * @param string $identifier
	 * @param string $initialization The value to set the variable at initialization
	 * @param string $type
	 * @param PhpDocComment $comment
	 */
	public function __construct(
		string $access,
		string $identifier,
		string $initialization = '',
		string $type = '',
		PhpDocComment $comment = null
	) {
		if ($type && !$comment && PHP_VERSION_ID < 70400) {
			$comment = PhpDocComment::var($type);
		}
		$this->comment = $comment;
		$this->access = $access;
		$this->identifier = $identifier;
		$this->initializedValue = $initialization ? ' = ' . $initialization : '';
		$this->type = $type;
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
		$dec .= ' $' . $this->identifier . $this->initializedValue . ';';

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
		return $this->initializedValue;
	}
}
