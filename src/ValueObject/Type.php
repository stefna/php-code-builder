<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\ValueObject;

final class Type
{
	private const ALIAS_MAP = [
		'boolean' => 'bool',
		'double' => 'float',
		'integer' => 'int',
		'number' => 'float',
	];

	private const INVALID_RETURN_TYPES = [
		'mixed',
		'resource',
		'static',
		'object',
	];
	/** @var bool */
	private $namespaced;
	/** @var bool */
	private $simplified = false;
	/** @var bool */
	private $nullable;
	/** @var string */
	private $type;
	/** @var Type[] */
	private $types = [];

	public static function empty(): self
	{
		return new self('');
	}

	public static function fromString(string $type): self
	{
		if (!trim($type)) {
			throw new \InvalidArgumentException('No valid type hint found in string');
		}

		if (strpos($type, '|')) {
			$self = null;
			$types = explode('|', $type);
			foreach ($types as $typePart) {
				if ($typePart !== 'null') {
					$self = new self($typePart);
					break;
				}
			}
			if (!$self) {
				throw new \InvalidArgumentException('No valid type hint found in string');
			}
			foreach ($types as $typePart) {
				$self->addUnion($typePart);
			}
			return $self;
		}
		if (strpos($type, '?') === 0) {
			return new self(substr($type, 1), true);
		}
		return new self($type);
	}

	public function __construct(string $type, bool $nullable = false)
	{
		$this->type = $type;
		$this->namespaced = strpos($type, '\\') !== false;
		$this->nullable = $nullable;
	}

	public function simplifyName(): void
	{
		$this->simplified = true;
		$p = explode('\\', $this->type);
		$this->type = array_pop($p);
	}

	public function isSimplified(): bool
	{
		return $this->simplified;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function setType(string $type): void
	{
		$this->type = $type;
		$this->namespaced = strpos($type, '\\') !== false;
		$this->simplified = false;
	}

	/**
	 * @param string|Type $type
	 */
	public function addUnion($type): void
	{
		if ($type === 'null') {
			$this->nullable = true;
			return;
		}
		if (!$type instanceof Type) {
			$type = Type::fromString($type);
		}
		if (!count($this->types)) {
			$this->types[] = $this;
		}
		foreach ($this->types as $currentType) {
			if ($currentType->type === $type->type) {
				return;
			}
		}
		$this->types[] = $type;
	}

	public function getTypeHint(): ?string
	{
		if (count($this->types) > 1) {
			return null;
		}
		$type = self::ALIAS_MAP[$this->type] ?? $this->type;

		if (in_array($type, self::INVALID_RETURN_TYPES, true)) {
			return null;
		}
		if ($this->isArray()) {
			return 'array';
		}

		return ($this->nullable ? '?' : '') . ($this->namespaced && !$this->simplified ? '\\' : '') . $type;
	}

	public function needDockBlockTypeHint(): bool
	{
		return $this->getTypeHint() === null || $this->isArray();
	}

	public function getDocBlockTypeHint(): ?string
	{
		if (count($this->types)) {
			$docType = [];
			foreach ($this->types as $type) {
				if (strpos($type->type, '\\')) {
					$docType[] = '\\' . $type->type;
				}
				else {
					$docType[] = $type->type;
				}
			}
			if ($this->nullable) {
				$docType[] = 'null';
			}
			return implode('|', $docType);
		}

		$type = self::ALIAS_MAP[$this->type] ?? $this->type;
		return ($this->namespaced && !$this->simplified ? '\\' : '') . $type . ($this->nullable ? '|null' : '');
	}

	public function isNullable(): bool
	{
		return $this->nullable;
	}

	public function isUnion(): bool
	{
		return count($this->types) > 1;
	}

	/**
	 * @return Type[]
	 */
	public function getUnionTypes(): array
	{
		return $this->types;
	}

	public function isArray(): bool
	{
		return (substr($this->type, -2) === '[]' || strpos($this->type, 'array<') === 0);
	}

	public function getArrayType(): ?string
	{
		if (!$this->isArray()) {
			return null;
		}
		$type = self::ALIAS_MAP[$this->type] ?? $this->type;
		if (strpos($type, 'array<') !== false) {
			preg_match('/array\<.*,(\s+)?(.*)\>/', $type, $match);
			return $match[2] ?? null;
		}

		return str_replace('[]', '', $type);
	}

	public function isNative(): bool
	{
		$type = self::ALIAS_MAP[$this->type] ?? $this->type;
		if ($this->isArray()) {
			$type = $this->getArrayType();
		}

		return in_array($type, [
			'string',
			'float',
			'bool',
			'int',
			'resource',
			'callable',
			'object',
		], true);
	}

	public function isTypeNamespaced(): bool
	{
		return $this->namespaced;
	}

	public function is(string $type): bool
	{
		return (self::ALIAS_MAP[$this->type] ?? $this->type) === $type;
	}
}
