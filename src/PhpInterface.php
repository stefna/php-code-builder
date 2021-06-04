<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\ValueObject\Identifier;

class PhpInterface extends PhpTrait
{
	protected const TYPE = 'interface';

	/** @var Identifier[] */
	private array $extends = [];

	/**
	 * Add interface to class
	 */
	public function addExtend(Identifier|string $interface): static
	{
		$this->extends[] = Identifier::fromUnknown($interface);

		return $this;
	}

	/**
	 * @return Identifier[]
	 */
	public function getExtends(): array
	{
		return $this->extends;
	}

	public function addMethod(PhpMethod $method, bool $forcePublic = false): static
	{
		if ($method->getAccess() !== PhpMethod::PUBLIC_ACCESS) {
			if ($forcePublic === false) {
				throw new \BadMethodCallException('Methods on interfaces must be "public"');
			}
			$method = clone $method;
			$method->setAccess('public');
		}
		return parent::addMethod($method);
	}

	public function addConstant(PhpConstant $constant, bool $forcePublic = false): static
	{
		if ($constant->getAccess() !== PhpConstant::PUBLIC_ACCESS) {
			if ($forcePublic === false) {
				throw new \BadMethodCallException('Constants on interfaces must be "public"');
			}
			$constant = clone $constant;
			$constant->setAccess('public');
		}
		return parent::addConstant($constant);
	}

	public function addVariable(PhpVariable $variable, bool $forcePublic = false): static
	{
		if ($variable->getAccess() !== PhpConstant::PUBLIC_ACCESS) {
			if ($forcePublic === false) {
				throw new \BadMethodCallException('Variables on interfaces must be "public"');
			}
			$variable = clone $variable;
			$variable->setAccess('public');
		}
		return parent::addVariable($variable);
	}

	public static function fromClass(Identifier $identifier, PhpClass  $class): self
	{
		$interface = new self($identifier);
		$constants = $class->getConstants();
		foreach ($constants as $identifier) {
			/** @var PhpConstant $constant */
			$constant = $constants[$identifier];
			if ($constant->getAccess() === PhpConstant::PUBLIC_ACCESS) {
				$interface->addConstant($constant);
			}
		}

		$variables = $class->getVariables();
		foreach ($variables as $identifier) {
			/** @var PhpVariable $variable */
			$variable = $variables[$identifier];
			if ($variable->getAccess() === PhpVariable::PUBLIC_ACCESS) {
				$interface->addVariable($variable);
			}
		}

		$methods = $class->getMethods();
		foreach ($methods as $identifier) {
			/** @var PhpMethod $method */
			$method = $methods[$identifier];
			if ($method->isConstructor()) {
				// skip constructor. Shouldn't be part of the interface
				// if you needed it you can add it manually
				continue;
			}

			if ($method->getAccess() === PhpMethod::PUBLIC_ACCESS) {
				$interface->addMethod($method);
			}
		}

		return $interface;
	}
}
