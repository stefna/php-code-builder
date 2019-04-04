<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\Exception\DuplicateValue;

class PhpTrait extends PhpElement
{
	protected const TYPE = 'trait';

	/** @var string[] */
	protected $uses = [];

	/** @var string[] */
	protected $traits = [];

	/** @var PhpConstant[] */
	private $constants = [];

	/** @var PhpVariable[] */
	private $variables = [];

	/** @var PhpFunction[] */
	private $methods = [];

	/** @var PhpDocComment */
	private $comment;

	public function __construct(string $identifier, PhpDocComment $comment = null)
	{
		$this->access = '';
		$this->comment = $comment;
		$this->identifier = $identifier;
	}

	/**
	 * @return string Returns the compete source code for the class
	 */
	public function getSource(): string
	{
		$ret = '';

		if ($this->comment) {
			$ret .= $this->comment->getSource();
		}


		$ret .= $this->formatAccessor();

		$ret .= static::TYPE;
		$ret .= ' ' . $this->identifier;

		$ret .= $this->formatInheritance();

		$ret .= PHP_EOL . '{' . PHP_EOL;

		$addNewLine = false;

		if (count($this->traits)) {
			foreach ($this->traits as $trait) {
				$ret .= Indent::indent(1) . 'use ' . $trait . ';' . PHP_EOL;
			}
			$addNewLine = true;
		}

		if (count($this->constants) > 0) {
			if ($addNewLine) {
				$ret .= true;
			}
			foreach ($this->constants as $constant) {
				$ret .= $constant->getSource();
			}
			$addNewLine = true;
		}

		if (count($this->variables) > 0) {
			if ($addNewLine) {
				$ret .= true;
			}
			$varSources = [];
			foreach ($this->variables as $variable) {
				$varSources[] = $variable->getSource();
			}

			$ret .= implode(PHP_EOL, $varSources);
			$addNewLine = true;
		}

		if (count($this->methods) > 0) {
			if ($addNewLine) {
				$ret .= PHP_EOL;
			}
			$methodSources = [];
			foreach ($this->methods as $method) {
				$methodSources[] = $method->getSource();
			}

			$ret .= implode(PHP_EOL, $methodSources);
		}

		$ret .= '}' . PHP_EOL;

		return $ret;
	}

	/**
	 * Add use statement above class
	 *
	 * @param string $class
	 * @param string $alias
	 * @return PhpClass
	 */
	public function addUse(string $class, string $alias = null): self
	{
		$this->uses[$class] = $alias ?: $class;

		return $this;
	}

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
		if (array_key_exists($constant->getIdentifier(), $this->constants)) {
			throw new DuplicateValue("A constant of the name ({$constant->getIdentifier()}) does already exist.");
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
	public function addVariable(PhpVariable $variable): self
	{
		if ($this->variableExists($variable->getIdentifier())) {
			throw new DuplicateValue("A variable of the name ({$variable->getIdentifier()}) is already defined.");
		}

		$this->variables[$variable->getIdentifier()] = $variable;

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
		if ($this->methodExists($method->getIdentifier())) {
			throw new DuplicateValue("A function of the name ({$method->getIdentifier()}) does already exist.");
		}

		$this->methods[$method->getIdentifier()] = $method;

		return $this;
	}

	/**
	 * Checks if a variable with the same name is already defined
	 *
	 * @param string $identifier
	 * @return bool
	 */
	public function variableExists(string $identifier): bool
	{
		return array_key_exists($identifier, $this->variables);
	}

	/**
	 * Checks if a method with the same name is already defined
	 *
	 * @param string $identifier
	 * @return bool
	 */
	public function methodExists(string $identifier): bool
	{
		return array_key_exists($identifier, $this->methods);
	}

	public function getVariable(string $identifier): ?PhpVariable
	{
		if (!$this->variableExists($identifier)) {
			return null;
		}
		return $this->variables[$identifier];
	}

	protected function formatInheritance(): string
	{
		return '';
	}

	protected function formatAccessor(): string
	{
		return '';
	}
}
