<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\ValueObject\Identifier;

class PhpInterface extends PhpTrait
{
	protected const TYPE = 'interface';

	/** @var Identifier[] */
	private $extends = [];

	/**
	 * Add interface to class
	 *
	 * @param Identifier|string $interface
	 * @return $this
	 */
	public function addExtend($interface): self
	{
		$this->extends[] = Identifier::fromUnknown($interface);

		return $this;
	}

	protected function formatInheritance(): string
	{
		$ret = '';
		if ($this->extends) {
			$ret .= ' extends ';
			foreach ($this->extends as $identifier) {
				$ret .= $identifier->toString() . ', ';
			}
		}
		return substr($ret, 0, -2);
	}

	public function addMethod(PhpMethod $method): PhpTrait
	{
		$method = clone $method;
		$method->setAbstract();
		$method->setAccess('public');
		return parent::addMethod($method);
	}
}
