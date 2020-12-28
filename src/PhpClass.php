<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\ValueObject\Identifier;

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
	/** @var Identifier */
	private $extends;
	/** @var Identifier[] */
	private $implements;

	/**
	 * @param Identifier|string $identifier
	 * @param Identifier|string|null $extends A string of the class that this class extends
	 * @param Identifier[] $implements
	 */
	public function __construct(
		$identifier,
		$extends = null,
		?PhpDocComment $comment = null,
		bool $final = false,
		bool $abstract = false,
		array $implements = []
	) {
		parent::__construct($identifier, $comment);
		$this->access = '';
		$this->final = $final;
		$this->extends = Identifier::fromUnknown($extends);
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
	 * @param Identifier|string $interface
	 * @return $this
	 */
	public function addInterface($interface): self
	{
		$identifier = Identifier::fromUnknown($interface);
		$this->addUse($identifier);
		$this->implements[] = $identifier;

		return $this;
	}

	public function addTrait(string $trait): self
	{
		$this->traits[] = $trait;
		return $this;
	}

	/**
	 * @param Identifier|string $extends
	 */
	public function setExtends($extends): self
	{
		$extends = Identifier::fromUnknown($extends);
		$this->addUse($extends);
		$this->extends = $extends;
		return $this;
	}

	protected function formatInheritance(): string
	{
		$ret = '';
		if ($this->extends) {
			$ret .= ' extends ' . $this->extends->toString();
		}

		if ($this->implements) {
			$ret .= ' implements ';
			foreach ($this->implements as $identifier) {
				$ret .= $identifier->toString() . ', ';
			}
			$ret = substr($ret, 0, -2);
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
