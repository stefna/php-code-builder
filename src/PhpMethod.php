<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\ValueObject\Type;

/**
 * Class that represents the source code for a method in php
 *
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @author Andreas Sundqvist <andreas@stefna.is>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class PhpMethod extends PhpFunction
{
	public const PRIVATE_ACCESS = 'private';
	public const PROTECTED_ACCESS = 'protected';
	public const PUBLIC_ACCESS = 'public';

	protected bool $final = false;
	protected bool $static = false;
	protected bool $abstract = false;

	protected bool $constructor = false;
	protected bool $constructorAutoAssign = false;

	protected PhpTrait|PhpClass|PhpInterface|null $parent = null;

	/**
	 * @param PhpParam[] $params
	 */
	public static function constructor(array $params, array $source, bool $autoAssign = false): self
	{
		$self = new self(
			self::PUBLIC_ACCESS,
			'__construct',
			[],
			$source,
			Type::empty(),
		);
		$self->constructor = true;
		$self->constructorAutoAssign = $autoAssign;
		foreach ($params as $param) {
			$self->addParam($param);
		}
		return $self;
	}

	public static function setter(PhpVariable $var, array $source = [], bool $fluent = false): self
	{
		$source[] = '$this->' . $var->getIdentifier()->toString() . ' = $' . $var->getIdentifier()->toString() . ';';
		if ($fluent) {
			$source[] = 'return $this;';
		}

		$valueParam = PhpParam::fromVariable($var);
		$valueParam->setType(clone $var->getType());
		$self = new self(self::PUBLIC_ACCESS, 'set' . ucfirst($var->getIdentifier()->toString()), [
			$valueParam,
		], $source, Type::fromString('void'));
		$var->setSetter($self);
		return $self;
	}

	public static function getter(PhpVariable $var, array $source = []): self
	{
		$type = $var->getType();
		$prefix = 'get';
		if ($type->is('bool')) {
			$prefix = 'is';
		}
		$methodName = $identifier = $var->getIdentifier()->toString();
		if (str_starts_with($identifier, $prefix)) {
			$methodName = substr($methodName, strlen($prefix));
		}

		$source[] = 'return $this->' . $identifier . ';';
		$self = self::public($prefix . ucfirst($methodName), [], $source, $var->getType());
		$var->setGetter($self);
		return $self;
	}

	public static function public(string $identifier, array $params, array $source, Type $type = null): self
	{
		return new self(self::PUBLIC_ACCESS, $identifier, $params, $source, $type ?? Type::empty());
	}

	public static function private(string $identifier, array $params, array $source, Type $type = null): self
	{
		return new self(self::PRIVATE_ACCESS, $identifier, $params, $source, $type ?? Type::empty());
	}

	public static function protected(string $identifier, array $params, array $source, Type $type = null): self
	{
		return new self(self::PROTECTED_ACCESS, $identifier, $params, $source, $type ?? Type::empty());
	}

	/**
	 * @param PhpParam[]|array<string, Type|string> $params
	 * @param array<array-key, string|string[]> $source
	 */
	public function __construct(
		protected string $access,
		string $identifier,
		array $params,
		array $source,
		?Type $returnTypeHint = null,
		?PhpDocComment $comment = null,
	) {
		parent::__construct($identifier, $params, $source, $returnTypeHint ?? Type::empty(), $comment);
	}

	public function setFinal(): static
	{
		if ($this->abstract) {
			throw new \BadMethodCallException('Can\'t mark method "final" already marked "abstract"');
		}
		if ($this->access === self::PRIVATE_ACCESS) {
			throw new \BadMethodCallException('Can\'t mark method "final" because it\'s marked as private');
		}

		$this->final = true;
		return $this;
	}

	public function setStatic(): static
	{
		$this->static = true;
		return $this;
	}

	public function setAbstract(bool $abstract = true): static
	{
		if ($abstract && $this->access === self::PRIVATE_ACCESS) {
			throw new \BadMethodCallException('Can\'t mark "private" methods as abstract');
		}
		$this->abstract = $abstract;
		return $this;
	}

	public function setAccess(string $access): static
	{
		$this->access = $access;
		return $this;
	}


	public function getAccess(): string
	{
		return $this->access;
	}

	public function isAbstract(): bool
	{
		return $this->abstract;
	}

	public function isFinal(): bool
	{
		return $this->final;
	}

	public function isStatic(): bool
	{
		return $this->static;
	}

	public function setParent(PhpTrait|PhpInterface|PhpClass $parent): static
	{
		$this->parent = $parent;

		if ($this->constructorAutoAssign) {
			foreach ($this->params as $param) {
				$var = $param->getVariable();
				if ($var &&
					!$parent instanceof PhpInterface &&
					!$parent->hasVariable($var->getIdentifier())
				) {
					$parent->addVariable($var);
				}
			}
		}

		return $this;
	}

	public function getParent(): PhpTrait|PhpInterface|PhpClass|null
	{
		return $this->parent;
	}

	public function isConstructor(): bool
	{
		return $this->constructor;
	}

	public function doConstructorAutoAssign(): bool
	{
		return $this->constructorAutoAssign;
	}

	public function addParam(PhpParam $param): static
	{
		$var = $param->getVariable();
		if ($var && $this->parent && !$this->parent->hasVariable($var->getIdentifier())) {
			$this->parent->addVariable($var);
		}
		return parent::addParam($param); // TODO: Change the autogenerated stub
	}

	public function __clone()
	{
		parent::__clone();
	}
}
