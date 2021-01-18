<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;

final class IfCode implements CodeInterface
{
	private $if;

	private $code;

	public static function instanceOf(VariableReference $var, Identifier $identifier, array $code): self
	{
		return new self(
			$var->getSource() . ' instanceof ' . $identifier->getName(),
			$code
		);
	}

	public function __construct(string $if, array $code)
	{
		$this->if = $if;
		$this->code = $code;
	}

	public function getSource(int $currentIndent = 0): string
	{
		return FlattenSource::source($this->getSourceArray());
	}

	public function getSourceArray(int $currentIndent = 0): array
	{
		return [
			sprintf('if (%s) {', $this->if),
			$this->code,
			'}',
		];
	}
}
