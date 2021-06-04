<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\CodeHelper\CodeInterface;
use Stefna\PhpCodeBuilder\Exception\DuplicateValue;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;

class PhpTrait
{
	protected const TYPE = 'trait';

	/** @var Identifier[] */
	protected array $uses = [];
	/** @var Identifier[] */
	protected array $traits = [];
	/** @var \SplObjectStorage<Identifier, PhpConstant> */
	protected \SplObjectStorage $constants;
	/** @var \SplObjectStorage<Identifier, PhpVariable> */
	protected \SplObjectStorage $variables;
	/** @var \SplObjectStorage<Identifier, PhpMethod> */
	protected \SplObjectStorage $methods;

	public function __construct(
		protected Identifier $identifier,
		protected ?PhpDocComment $comment = null,
	) {
		$this->methods = new \SplObjectStorage();
		$this->variables = new \SplObjectStorage();
		$this->constants = new \SplObjectStorage();
	}

	public function setComment(PhpDocComment $comment): void
	{
		$this->comment = $comment;
	}

	public function getComment(): PhpDocComment
	{
		if (!$this->comment) {
			$this->comment = new PhpDocComment();
		}
		return $this->comment;
	}

	public function addUse(Identifier|string $class, string $alias = null): static
	{
		$class = Identifier::fromUnknown($class);
		if (!$alias && $this->identifier->equal($class)) {
			return $this;
		}
		if ($alias) {
			$class->setAlias($alias);
		}
		$this->uses[] = $class;

		return $this;
	}

	/**
	 * @return Identifier[]
	 */
	public function getUses(): array
	{
		return $this->uses;
	}

	/**
	 * Adds a constant to the class
	 *
	 * @throws \InvalidArgumentException
	 * @throws DuplicateValue
	 */
	public function addConstant(PhpConstant $constant): static
	{
		if ($this->constants->contains($constant->getIdentifier())) {
			throw new DuplicateValue(sprintf(
				'A constant of the name (%s) does already exist.',
				$constant->getIdentifier()->getName(),
			));
		}

		$this->constants[$constant->getIdentifier()] = $constant;

		return $this;
	}

	/**
	 * Adds a variable to the class
	 *
	 * Throws Exception if the variable is already defined
	 *
	 * @param PhpVariable $variable The variable object to add
	 * @throws DuplicateValue If the variable name already exists
	 * @return $this
	 */
	public function addVariable(PhpVariable $variable, bool $createGetterSetter = false): self
	{
		if ($this->hasVariable($variable->getIdentifier())) {
			throw new DuplicateValue(sprintf(
				'A variable of the name (%s) is already defined.',
				$variable->getIdentifier()->getName(),
			));
		}
		$this->addUseFromType($variable->getType());
		$this->variables[$variable->getIdentifier()] = $variable;

		if ($createGetterSetter) {
			// todo fix
#			$this->addMethod(PhpMethod::getter($variable));
#			$this->addMethod(PhpMethod::setter($variable));
		}

		return $this;
	}

	/**
	 * Adds a method to the class
	 *
	 * @throws DuplicateValue If the method name is already defined
	 */
	public function addMethod(PhpMethod $method): static
	{
		if ($this->methods->contains($method->getIdentifier())) {
			throw new DuplicateValue(sprintf(
				'A method of the name (%s) is already defined.',
				$method->getIdentifier()->getName(),
			));
		}
		return $this->replaceMethod($method->getIdentifier(), $method);
	}

	public function replaceMethod(Identifier|string $identifier, PhpMethod $method): static
	{
		$identifier = Identifier::fromUnknown($identifier);
		$this->addUseFromType($method->getReturnType());
		foreach ($method->getParams() as $param) {
			$this->addUseFromType($param->getType());
		}

		$method->setParent($this);
		$this->methods[$identifier] = $method;

		return $this;
	}

	/**
	 * Checks if a variable with the same name is already defined
	 */
	public function hasVariable(Identifier|string $identifier): bool
	{
		$identifier = Identifier::fromUnknown($identifier);
		return $this->variables->contains($identifier);
	}

	/**
	 * Checks if a method with the same name is already defined
	 */
	public function hasMethod(Identifier|string $identifier): bool
	{
		$identifier = Identifier::fromUnknown($identifier);
		return $this->methods->contains($identifier);
	}

	/**
	 * Checks if a constant with the same name is already defined
	 */
	public function hasConstant(Identifier|string $identifier): bool
	{
		$identifier = Identifier::fromUnknown($identifier);
		return $this->constants->contains($identifier);
	}

	public function getVariable(Identifier|string $identifier): ?PhpVariable
	{
		$identifier = Identifier::fromUnknown($identifier);
		if (!$this->hasVariable($identifier)) {
			return null;
		}
		return $this->variables[$identifier];
	}

	/**
	 * @return \SplObjectStorage<Identifier, PhpVariable>
	 */
	public function getVariables(): \SplObjectStorage
	{
		return $this->variables;
	}

	/**
	 * @param string|Identifier $identifier
	 */
	public function getMethod(Identifier|string $identifier): ?PhpMethod
	{
		$identifier = Identifier::fromUnknown($identifier);
		if (!$this->hasMethod($identifier)) {
			return null;
		}
		return $this->methods[$identifier];
	}

	public function getConstant(Identifier|string $identifier): ?PhpConstant
	{
		$identifier = Identifier::fromUnknown($identifier);
		if (!$this->hasConstant($identifier)) {
			return null;
		}
		return $this->constants[$identifier];
	}

	/**
	 * @param ValueObject\Type $type
	 */
	private function addUseFromType(ValueObject\Type $type): void
	{
		if ($type->isTypeNamespaced() && !$type->isSimplified()) {
			$this->addUse($type->getIdentifier());
			$type->simplifyName();
		}
	}

	/**
	 * @return \SplObjectStorage<Identifier, PhpConstant>
	 */
	public function getConstants(): \SplObjectStorage
	{
		return $this->constants;
	}

	public function getMethods(): \SplObjectStorage
	{
		return $this->methods;
	}

	public function getIdentifier(): Identifier
	{
		return $this->identifier;
	}

	public function getTraits(): array
	{
		return $this->traits;
	}
}
