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
	private bool $namespaced;
	private bool $simplified = false;
	private bool $inCheckLoop = false;
	private string $namespace = '';
	/** @var Type[] */
	private array $types = [];

	public static function empty(): self
	{
		return new self('');
	}

	public static function fromIdentifier(Identifier $identifier): self
	{
		return self::fromString($identifier->toString());
	}

	public static function fromString(string $type): self
	{
		if (!trim($type, '? ')) {
			throw new \InvalidArgumentException('No valid type hint found in string');
		}
		$arraySubTypeKey = '__ARRAY_SUB_TYPE__';
		$arraySubType = null;
		if (str_contains($type, 'array<')) {
			preg_match('/array\<.*,(\s+)?(.*)\>/', $type, $match);
			$arraySubType = $match[2] ?? '';
			if (strpos($arraySubType, '|')) {
				$type = str_replace($arraySubType, $arraySubTypeKey, $type);
			}
		}
		if (strpos($type, '|')) {
			$self = self::empty();
			$types = explode('|', $type);
			$noValidTypes = true;
			foreach ($types as $typePart) {
				if ($typePart !== 'null') {
					$noValidTypes = false;
					break;
				}
			}
			if (count($types) === 2 && in_array('null', $types)) {
				return self::fromString('?' . trim(str_replace('null', '', $type), '|'));
			}
			if ($noValidTypes) {
				throw new \InvalidArgumentException('No valid type hint found in string');
			}
			foreach ($types as $typePart) {
				if (strpos($typePart, $arraySubTypeKey)) {
					$typePart = str_replace($arraySubTypeKey, $arraySubType, $typePart);
				}
				$self->addUnion($typePart);
			}
			return $self;
		}
		if (str_starts_with($type, '?')) {
			return new self(substr($type, 1), true);
		}

		if (strpos($type, $arraySubTypeKey)) {
			$type = str_replace($arraySubTypeKey, $arraySubType, $type);
		}
		return new self($type);
	}

	public function __construct(
		private string $type,
		private bool $nullable = false,
	) {
		$this->namespaced = str_contains($type, '\\');
	}

	public function getNamespace(): string
	{
		return $this->namespace;
	}

	public function simplifyName(): void
	{
		$this->simplified = true;
		$p = explode('\\', $this->type);
		$this->type = array_pop($p);
		$this->namespace = implode('\\', $p);
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
		$this->namespaced = str_contains($type, '\\');
		$this->simplified = false;
	}

	public function addUnion(Type|string $type): void
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
			if ($this->isArray()) {
				return 'array';
			}
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
			return implode('|', array_filter($docType));
		}

		$type = self::ALIAS_MAP[$this->type] ?? $this->type;
		return ($this->namespaced && !$this->simplified ? '\\' : '') . $type . ($this->nullable ? '|null' : '');
	}

	public function isNullable(): bool
	{
		if ($this->nullable === false && $this->type === '') {
			foreach ($this->types as $type) {
				if ($type === $this) {
					continue;
				}
				if ($type->isNullable() || $type->type === 'null') {
					return true;
				}
			}
			return false;
		}
		return $this->nullable;
	}

	public function notNull(): self
	{
		if (!$this->isNullable()) {
			return $this;
		}
		foreach ($this->types as $index => $type) {
			if ($type->type === 'null') {
				$self = clone $this;
				unset($self->types[$index]);
				return $self;
			}
		}
		return $this;
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
		$returnTypes = [];
		foreach ($this->types as $type) {
			if ($type->type === '') {
				continue;
			}
			$returnTypes[] = $type;
		}
		return $returnTypes;
	}

	public function getIdentifier(): Identifier
	{
		return Identifier::fromString(
			$this->isArray(false) ?
				(string)$this->getArrayType() :
				$this->getType()
		);
	}

	public function isArray(bool $deepCheck = true): bool
	{
		if ($deepCheck && !$this->inCheckLoop && $this->isUnion()) {
			$this->inCheckLoop = true;
			foreach ($this->getUnionTypes() as $type) {
				if (!$type->isArray()) {
					$this->inCheckLoop = false;
					return false;
				}
			}
			$this->inCheckLoop = false;
			return true;
		}
		return (substr($this->type, -2) === '[]' || str_starts_with($this->type, 'array<'));
	}

	public function getArrayType(): ?string
	{
		if (!$this->isArray(false)) {
			return null;
		}
		$type = self::ALIAS_MAP[$this->type] ?? $this->type;
		if (str_contains($type, 'array<')) {
			preg_match('/array\<.*,(\s+)?(.*)\>/', $type, $match);
			return $match[2] ?? null;
		}

		return str_replace('[]', '', $type);
	}

	public function getArrayTypeObject(): ?Type
	{
		$typeStr = $this->getArrayType();
		if (!$typeStr) {
			return null;
		}
		if ($this->simplified && $this->namespaced) {
			$typeStr = $this->namespace . '\\' . $typeStr;
		}
		$type = Type::fromString($typeStr);
		if ($this->simplified) {
			$type->simplifyName();
		}
		return $type;
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

	public function isEmpty(): bool
	{
		return $this->type === '' && count($this->types) === 0;
	}

	/**
	 * @return class-string
	 */
	public function getFqcn(): string
	{
		if ($this->simplified && $this->namespaced) {
			return $this->namespace . '\\' . $this->type;
		}
		return $this->type;
	}
}
