<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\FormatValue;
use Stefna\PhpCodeBuilder\Indent;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;

final class StaticMethodCall implements CodeInterface
{
	use MethodParamsTrait;

	public function __construct(
		private Identifier $class,
		private string $method,
		private array $params = []
	) {
		$this->identifier = $class->getName();
		$this->callIdentifier = '::';
	}

	public function getSourceArray(): array
	{
		return $this->buildSourceArray();
	}
}
