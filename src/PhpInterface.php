<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

class PhpInterface extends PhpTrait
{
	protected const TYPE = 'interface';

	/** @var string[] */
	private $extends = [];

	/**
	 * Add interface to class
	 *
	 * @param string $interface
	 * @return $this
	 */
	public function addExtend(string $interface): self
	{
		$this->extends[] = $interface;

		return $this;
	}

	protected function formatInheritance(): string
	{
		$ret = '';
		if ($this->extends) {
			$ret .= ' extends ';
			$ret .= implode(', ', $this->extends);
		}
		return $ret;
	}

	public function addMethod(PhpMethod $method): PhpTrait
	{
		$method = clone $method;
		$method->setAbstract();
		$method->setAccess('public');
		return parent::addMethod($method);
	}
}
