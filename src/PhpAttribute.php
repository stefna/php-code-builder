<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\ValueObject\Identifier;

final class PhpAttribute
{
	private Identifier $identifier;
	/** @var string[] */
	private array $args;

	public function __construct(
		string|Identifier $identifier,
		string ...$args,
	) {
		$this->identifier = Identifier::fromUnknown($identifier);
		$this->args = $args;
	}

	public function getIdentifier(): Identifier
	{
		return $this->identifier;
	}

	/**
	 * @return string[]
	 */
	public function getArgs(): array
	{
		return $this->args;
	}
}
