<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\PhpParam;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;

final class StaticMethodCall implements CodeInterface
{
	use MethodParamsTrait;

	/**
	 * @param list<string|string[]|PhpParam|VariableReference|CodeInterface> $params
	 */
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
