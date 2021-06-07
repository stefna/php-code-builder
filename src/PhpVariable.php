<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\ValueObject\Identifier;
use Stefna\PhpCodeBuilder\ValueObject\Type;

/**
 * Class that represents the source code for a variable in php
 *
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @author Andreas Sundqvist <andreas@stefna.is>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class PhpVariable
{
	public const PRIVATE_ACCESS = 'private';
	public const PROTECTED_ACCESS = 'protected';
	public const PUBLIC_ACCESS = 'public';
	public const NO_VALUE = '__PhpVariable_NoValue__';

	private bool $promoted = false;
	private ?PhpMethod $setter = null;
	private ?PhpMethod $getter = null;

	public static function private(
		string $identifier,
		Type $type,
		bool $autoSetter = false,
		bool $autoGetter = false,
	): self {
		return new self(
			self::PRIVATE_ACCESS,
			Identifier::simple($identifier),
			$type,
			autoSetter: $autoSetter,
			autoGetter: $autoGetter,
		);
	}

	public static function protected(
		string $identifier,
		Type $type,
		bool $autoSetter = false,
		bool $autoGetter = false,
	): self {
		return new self(
			self::PROTECTED_ACCESS,
			Identifier::simple($identifier),
			$type,
			autoSetter: $autoSetter,
			autoGetter: $autoGetter,
		);
	}

	public static function public(
		string $identifier,
		Type $type,
		bool $autoSetter = false,
		bool $autoGetter = false,
	): self {
		return new self(
			self::PUBLIC_ACCESS,
			Identifier::simple($identifier),
			$type,
			autoSetter: $autoSetter,
			autoGetter: $autoGetter,
		);
	}

	public function __construct(
		protected string $access,
		protected Identifier $identifier,
		protected Type $type,
		protected mixed $initializedValue = self::NO_VALUE,
		protected ?PhpDocComment $comment = null,
		protected bool $static = false,
		protected bool $autoSetter = false,
		protected bool $autoGetter = false,
	) {
		if (!$this->comment && $type && $type->needDockBlockTypeHint()) {
			$this->comment = PhpDocComment::var($type);
		}
	}

	public function setGetter(PhpMethod $getter): static
	{
		$this->getter = $getter;
		return $this;
	}

	public function getGetter(): ?PhpMethod
	{
		if (!$this->getter && $this->autoGetter) {
			$this->getter = PhpMethod::getter($this);
		}
		return $this->getter;
	}

	public function setSetter(PhpMethod $setter): static
	{
		$this->setter = $setter;
		return $this;
	}

	public function getSetter(): ?PhpMethod
	{
		if (!$this->setter && $this->autoSetter) {
			$this->setter = PhpMethod::setter($this);
		}
		return $this->setter;
	}

	public function getAccess(): string
	{
		return $this->access;
	}

	public function getIdentifier(): Identifier
	{
		return $this->identifier;
	}

	public function setStatic(): static
	{
		$this->static = true;
		return $this;
	}

	public function setComment(PhpDocComment $comment): void
	{
		$comment->setParent($this);
		$this->comment = $comment;
	}

	public function setInitializedValue(mixed $initializedValue): static
	{
		$this->initializedValue = $initializedValue;
		return $this;
	}

	public function getInitializedValue(): mixed
	{
		return $this->initializedValue;
	}

	public function getType(): Type
	{
		return $this->type;
	}

	public function getComment(): ?PhpDocComment
	{
		return $this->comment;
	}

	public function isStatic(): bool
	{
		return $this->static;
	}

	public function isPromoted(): bool
	{
		return $this->promoted;
	}

	public function setAccess(string $access): static
	{
		$this->access = $access;
		return $this;
	}

	public function setPromoted(bool $promoted = true): static
	{
		$this->promoted = $promoted;
		return $this;
	}
}
