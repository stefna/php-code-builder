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
		if (!trim($type, '? ')) {
			throw new \InvalidArgumentException('No valid type hint found in string');
		}
		$arraySubTypeKey = '__ARRAY_SUB_TYPE__';
		$arraySubType = null;
		if (strpos($type, 'array<') !== false) {
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
		if (strpos($type, '?') === 0) {
			return new self(substr($type, 1), true);
		}

		if (strpos($type, $arraySubTypeKey)) {
			$type = str_replace($arraySubTypeKey, $arraySubType, $type);
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
		return $this->nullable;
	}

	public function notNull(): self
	{
		if (!$this->isNullable()) {
			return $this;
		}
		foreach ($this->types as $index => $type) {
			if ($type === 'null') {
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
		return Identifier::fromString($this->isArray(false) ? $this->getArrayType() : $this->getType());
	}

	private $inCheckLoop = false;
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
		return (substr($this->type, -2) === '[]' || strpos($this->type, 'array<') === 0);
	}

	public function getArrayType(): ?string
	{
		if (!$this->isArray(false)) {
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
