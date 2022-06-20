<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\ValueObject\EnumCase;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;
use Stefna\PhpCodeBuilder\ValueObject\Type;

class PhpEnum extends PhpClass
{
	protected const TYPE = 'enum';

	/** @var list<EnumCase> */
	private array $cases = [];

	private Type $backed;

	/**
	 * @param Identifier[] $implements
	 * @param EnumCase[] $cases
	 */
	public function __construct(
		Identifier|string $identifier,
		Type|null $backed = null,
		PhpDocComment|null $comment = null,
		array $implements = [],
		array $cases = [],
	) {
		parent::__construct(
			$identifier,
			comment: $comment,
			implements: $implements,
		);
		$this->backed = $backed ?? Type::empty();
		$this->cases = $cases;
	}

	public function isBacked(): bool
	{
		return !$this->backed->isEmpty();
	}

	public function getBackedType(): Type
	{
		return $this->backed;
	}

	/**
	 * @return list<EnumCase>
	 */
	public function getCases(): array
	{
		return $this->cases;
	}

	public function addCase(EnumCase $case): static
	{
		$this->cases[] = $case;
		return $this;
	}

}
