<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\ValueObject;

final class Identifier
{
	/** @var array<string, Identifier> */
	private static array $instances = [];

	private ?string $alias = null;

	private ?Identifier $generic = null;

	public static function fromObject(string|object $object): self
	{
		if (is_object($object)) {
			$object = get_class($object);
		}
		return self::fromString($object);
	}

	public static function fromString(string $identifier): self
	{
		$namespace = '';
		if (str_contains($identifier, '\\')) {
			$ns = explode('\\', $identifier);
			$identifier = array_pop($ns);
			$namespace  = implode('\\', $ns);
		}
		$identifierKey = $identifier . $namespace;
		if (isset(self::$instances[$identifierKey])) {
			return self::$instances[$identifierKey];
		}

		return self::$instances[$identifierKey] = new self($identifier, $namespace);
	}

	public static function simple(string $name): self
	{
		if (isset(self::$instances[$name])) {
			return self::$instances[$name];
		}

		return self::$instances[$name] = new self($name, '');
	}

	public static function fromUnknown(mixed $identifier): self
	{
		if ($identifier instanceof Identifier) {
			return $identifier;
		}
		if (is_string($identifier)) {
			return self::fromString($identifier);
		}

		return new self(random_bytes(6), '');
	}

	private function __construct(
		private string $name,
		private string $namespace,
	) {}

	public function withNamespace(string $namespace): self
	{
		$identifierKey = $this->name . $namespace;
		if (isset(self::$instances[$identifierKey])) {
			return self::$instances[$identifierKey];
		}

		return self::$instances[$identifierKey] = new self($this->name, $namespace);
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getNamespace(): string
	{
		return $this->namespace;
	}

	public function getFqcn(): string
	{
		return $this->namespace . '\\' . $this->name;
	}

	public function toString(): string
	{
		return $this->alias ?? $this->name;
	}

	public function setAlias(string $alias): void
	{
		$this->alias = $alias;
	}

	public function getAlias(): ?string
	{
		return $this->alias;
	}

	public function isGeneric(): bool
	{
		return $this->generic !== null;
	}

	public function getGenericIdentifier(): ?Identifier
	{
		return $this->generic;
	}

	public function genericOf(Identifier $generic): self
	{
		$self = clone $this;
		$self->generic =  $generic;
		return $self;
	}

	public function equal(Identifier $identifier): bool
	{
		return $this->namespace === $identifier->namespace && $this->name === $identifier->name;
	}
}
