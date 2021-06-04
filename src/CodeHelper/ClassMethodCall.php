<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\Indent;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;

final class ClassMethodCall implements CodeInterface
{
	use MethodParamsTrait;

	public static function this(string $method, array $params = []): self
	{
		return new self(VariableReference::this(), $method, $params);
	}

	public function __construct(
		private VariableReference $class,
		private string $method,
		private array $params = []
	) {
		$this->identifier = $class->toString();
	}

	public function getSourceArray(int $currentIndent = 0): array
	{
		return $this->buildSourceArray($currentIndent);
	}
}
