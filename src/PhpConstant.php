<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\ValueObject\Identifier;

class PhpConstant
{
	public const PRIVATE_ACCESS = 'private';
	public const PROTECTED_ACCESS = 'protected';
	public const PUBLIC_ACCESS = 'public';

	public const CASE_UPPER = 0;
	public const CASE_LOWER = 1;
	public const CASE_NONE = 2;

	public static function public(string $identifier, $value = null): self
	{
		return new self(self::PUBLIC_ACCESS, $identifier, $value);
	}

	public static function private(string $identifier, $value = null): self
	{
		return new self(self::PRIVATE_ACCESS, $identifier, $value);
	}

	public static function protected(string $identifier, $value = null): self
	{
		return new self(self::PROTECTED_ACCESS, $identifier, $value);
	}

	public function __construct(
		protected string $access,
		protected string $identifier,
		protected mixed $value = null,
		protected int $case = self::CASE_UPPER,
	) {}

	public function getName(): string
	{
		$currentName = $this->identifier;
		$name = preg_replace('/^(\d)/', '_$0', $currentName);
		$name = str_replace('-', '_', $name);

		if ($this->case === self::CASE_NONE) {
			return $name;
		}

		$name = preg_replace('/(?<!^)[A-Z]/', '_$0', $name);
		if ($this->case === self::CASE_LOWER) {
			return strtolower($name);
		}

		if ($currentName === strtoupper($currentName)) {
			return $currentName;
		}
		return strtoupper($name);
	}

	public function setValue(string $value): static
	{
		$this->value = $value;
		return $this;
	}

	public function getValue(): mixed
	{
		return $this->value === null ? $this->identifier : $this->value;
	}

	public function setCase(int $case): static
	{
		if (!in_array($case, [self::CASE_LOWER, self::CASE_UPPER, self::CASE_NONE], true)) {
			throw new \InvalidArgumentException('Invalid case');
		}
		$this->case = $case;
		return $this;
	}

	public function getAccess(): string
	{
		return $this->access;
	}

	public function setAccess(string $access): static
	{
		$this->access = $access;
		return $this;
	}

	public function getIdentifier(): Identifier
	{
		return Identifier::fromUnknown($this->identifier);
	}
}
