<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\CodeHelper\CodeInterface;
use Stefna\PhpCodeBuilder\Exception\DuplicateValue;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;

class PhpTrait extends PhpElement implements CodeInterface
{
	protected const TYPE = 'trait';

	/** @var Identifier[] */
	protected $uses = [];
	/** @var Identifier[] */
	protected $traits = [];
	/** @var PhpConstant[]|\SplObjectStorage<Identifier, PhpConstant> */
	private $constants;
	/** @var PhpVariable[]|\SplObjectStorage<Identifier, PhpVariable> */
	private $variables;
	/** @var PhpMethod[]|\SplObjectStorage<Identifier, PhpMethod> */
	private $methods;
	/** @var PhpDocComment */
	private $comment;

	/**
	 * @param Identifier|string
	 */
	public function __construct($identifier, PhpDocComment $comment = null)
	{
		$this->access = '';
		$this->comment = $comment;
		$this->identifier = Identifier::fromUnknown($identifier);
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

	/**
	 * @return string Returns the compete source code for the class
	 */
	public function getSource(int $currentIndent = 0): string
	{
		return FlattenSource::source($this->getSourceArray());
	}

	public function getSourceArray(int $currentIndent = 0): array
	{
		$ret = [];
		if ($this->comment) {
			foreach ($this->comment->getSourceArray() as $line) {
				$ret[] = $line;
			}
		}

		$declaration = $this->formatAccessor();
		$declaration .= static::TYPE;
		$declaration .= ' ' . $this->identifier->getName();
		$declaration .= $this->formatInheritance();

		$ret[] = $declaration;
		$ret[] = '{';
		$classBody = [];

		$addNewLine = false;
		if (count($this->traits)) {
			foreach ($this->traits as $trait) {
				$classBody[] = 'use ' . $trait->toString() . ';';
			}
			$addNewLine = true;
		}

		if (count($this->constants) > 0) {
			if ($addNewLine) {
				$classBody[] = '';
			}
			foreach ($this->constants as $identifier) {
				$classBody[] = trim($this->constants[$identifier]->getSource());
			}
			$addNewLine = true;
		}

		if (count($this->variables) > 0) {
			if ($addNewLine) {
				$classBody[] = '';
			}
			foreach ($this->variables as $identifier) {
				$classBody[] = trim($this->variables[$identifier]->getSource());
			}
		}

		if (count($this->methods) > 0) {
			foreach ($this->methods as $identifier) {
				if ($addNewLine) {
					$classBody[] = '';
				}
				array_push($classBody, ...$this->methods[$identifier]->getSourceArray());
			}
		}
		$ret[] = $classBody;
		$ret[] = '}';

		return $ret;
	}

	/**
	 * Add use statement above class
	 *
	 * @param Identifier|string $class
	 * @param string $alias
	 * @return PhpClass
	 */
	public function addUse($class, string $alias = null): self
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
	 * If no name is supplied and the value is a string the value is used as
	 * name otherwise exception is raised
	 *
	 * @param mixed $value
	 * @param string $name
	 * @throws \InvalidArgumentException
	 * @throws DuplicateValue
	 * @return $this
	 */
	public function addConstant(PhpConstant $constant): self
	{
		if ($this->constants->contains($constant->getIdentifier())) {
			throw new DuplicateValue("A constant of the name ({$constant->getIdentifier()->getName()}) does already exist.");
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
		if ($this->variableExists($variable->getIdentifier()->getName())) {
			throw new DuplicateValue("A variable of the name ({$variable->getIdentifier()}) is already defined.");
		}
		$this->addUseFromType($variable->getType());
		$this->variables[$variable->getIdentifier()] = $variable;

		if ($createGetterSetter) {
			$this->addMethod(PhpMethod::getter($variable));
			$this->addMethod(PhpMethod::setter($variable));
		}

		return $this;
	}

	/**
	 * Adds a method to the class
	 *
	 * @param PhpMethod $method The function object to add
	 * @return $this
	 * @throws DuplicateValue If the method name is already defined
	 */
	public function addMethod(PhpMethod $method): self
	{
		if ($this->methods->contains($method->getIdentifier())) {
			throw new DuplicateValue("A function of the name ({$method->getIdentifier()->getName()}) does already exist.");
		}
		return $this->replaceMethod($method->getIdentifier(), $method);
	}

	/**
	 * @param Identifier|string $identifier
	 */
	public function replaceMethod($identifier, PhpMethod $method): self
	{
		if (is_string($identifier)) {
			$identifier = Identifier::simple($identifier);
		}
		$this->addUseFromType($method->getReturnType());
		foreach ($method->getParams() as $param) {
			$this->addUseFromType($param->getType());
		}

		$this->methods[$identifier] = $method;

		return $this;
	}

	/**
	 * Checks if a variable with the same name is already defined
	 *
	 * @param Identifier|string $identifier
	 * @return bool
	 */
	public function variableExists(string $identifier): bool
	{
		if (is_string($identifier)) {
			$identifier = Identifier::simple($identifier);
		}
		return $this->variables->contains($identifier);
	}

	/**
	 * Checks if a method with the same name is already defined
	 *
	 * @param Identifier|string $identifier
	 * @return bool
	 */
	public function methodExists($identifier): bool
	{
		if (is_string($identifier)) {
			$identifier = Identifier::simple($identifier);
		}

		return $this->methods->contains($identifier);
	}

	/**
	 * @param Identifier|string $identifier
	 */
	public function getVariable($identifier): ?PhpVariable
	{
		if (is_string($identifier)) {
			$identifier = Identifier::simple($identifier);
		}
		if (!$this->variableExists($identifier)) {
			return null;
		}
		return $this->variables[$identifier];
	}

	/**
	 * @return \SplObjectStorage<Identifier, PhpVariable>
	 */
	public function getVariables()
	{
		return $this->variables;
	}

	/**
	 * @param Identifier|string $identifier
	 */
	public function getMethod($identifier): ?PhpMethod
	{
		if (is_string($identifier)) {
			$identifier = Identifier::simple($identifier);
		}
		if (!$this->methodExists($identifier)) {
			return null;
		}
		return $this->methods[$identifier];
	}

	protected function formatInheritance(): string
	{
		return '';
	}

	protected function formatAccessor(): string
	{
		return '';
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
}
