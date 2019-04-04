<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\Exception\DuplicateValue;

/**
 * Class that represents the source code for a class in php
 *
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @author Andreas Sundqvist <andreas@stefna.is>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class PhpClass extends PhpTrait
{
	protected const TYPE = 'class';

	/** @var bool */
	private $abstract;

	/** @var bool */
	private $final;

	/** @var string */
	private $extends;

	/** @var string[] */
	private $implements;

	/**
	 * @param string $identifier
	 * @param string $extends A string of the class that this class extends
	 * @param PhpDocComment $comment
	 * @param bool $final
	 * @param bool $abstract
	 * @param array $implements
	 */
	public function __construct(
		string $identifier,
		?string $extends = null,
		?PhpDocComment $comment = null,
		bool $final = false,
		bool $abstract = false,
		array $implements = []
	) {
		parent::__construct($identifier, $comment);
		$this->access = '';
		$this->final = $final;
		$this->extends = $extends;
		$this->implements = $implements;
		$this->abstract = $abstract;
	}

	public function setAbstract(): self
	{
		$this->abstract = true;
		return $this;
	}

	public function setFinal(): self
	{
		$this->final = true;
		return $this;
	}

	/**
	 * Add interface to class
	 *
	 * @param string $interface
	 * @return $this
	 */
	public function addInterface(string $interface): self
	{
		$this->implements[] = $interface;

		return $this;
	}

	public function setExtends(string $extends): self
	{
		$this->extends = $extends;
		return $this;
	}

	protected function formatInheritance(): string
	{
		$ret = '';
		if ($this->extends !== '') {
			if (\strpos($this->extends, '\\') !== false) {
				$this->extends = '\\' . ltrim($this->extends, '\\');
			}
			$ret .= ' extends ' . $this->extends;
		}

		if ($this->implements) {
			$ret .= ' implements ';
			$ret .= implode(', ', $this->implements);
		}
		return $ret;
	}

	protected function formatAccessor(): string
	{
		$ret = '';
		if ($this->final) {
			$ret .= 'final ';
		}
		elseif ($this->abstract) {
			$ret .= 'abstract ';
		}
		return $ret;
	}
}
