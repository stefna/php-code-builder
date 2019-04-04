<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

/**
 * Abstract base class for all PHP elements, variables, functions and classes etc.
 *
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @author Andreas Sundqvist <andreas@stefna.is>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
abstract class PhpElement
{
	/**
	 * The access of the function |public|private|protected
	 *
	 * @var string
	 */
	protected $access;

	/**
	 * The identifier of the element
	 *
	 * @var string
	 */
	protected $identifier;

	/**
	 * Default indentation level
	 *
	 * @var int
	 */
	protected $indentionLevel = 1;

	/**
	 * Function to be overloaded, return the source code of the specialized element
	 *
	 * @return string
	 */
	abstract public function getSource(): string;

	/**
	 * @return string The access of the element
	 */
	public function getAccess(): string
	{
		return $this->access;
	}

	/**
	 * @return string The identifier, name, of the element
	 */
	public function getIdentifier(): string
	{
		return $this->identifier;
	}

	/**
	 * Takes a string and prepends it with the current indention string
	 *
	 * Has support for multiple lines
	 *
	 * @param string $source
	 * @return string
	 */
	public function getSourceRow(string $source): string
	{
		if (strpos($source, PHP_EOL) === false) {
			return Indent::indent($this->indentionLevel) . $source . PHP_EOL;
		}

		$ret = '';
		$rows = explode(PHP_EOL, $source);
		if (trim($rows[0]) === '') {
			$rows = array_splice($rows, 1);
		}
		if (trim($rows[count($rows) - 1]) === '') {
			$rows = array_splice($rows, 0, count($rows) - 1);
		}
		foreach ($rows as $row) {
			$ret .= Indent::indent($this->indentionLevel) . $row . PHP_EOL;
		}

		return $ret;
	}

	/**
	 * @param string $identifier
	 * @return $this
	 */
	public function setIdentifier(string $identifier): self
	{
		$this->identifier = $identifier;
		return $this;
	}

	/**
	 * Sets the indention level to use
	 *
	 * @param int $level
	 * @return $this
	 */
	public function setIndentionLevel(int $level): self
	{
		$this->indentionLevel= $level;
		return $this;
	}
}
