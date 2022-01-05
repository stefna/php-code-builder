<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

final class CastCode implements CodeInterface
{
	public static function string(VariableReference $reference): self
	{
		return new self($reference, 'string');
	}

	public static function int(VariableReference $reference): self
	{
		return new self($reference, 'int');
	}

	public static function bool(VariableReference $reference): self
	{
		return new self($reference, 'bool');
	}

	public static function float(VariableReference $reference): self
	{
		return new self($reference, 'float');
	}

	public function __construct(
		private VariableReference $reference,
		private string $castType,
	) {}

	public function getSourceArray(): array
	{
		return [
			'(' . $this->castType .')' . $this->reference->toString(),
		];
	}
}
