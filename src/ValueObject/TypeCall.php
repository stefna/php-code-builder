<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\ValueObject;

use Stefna\PhpCodeBuilder\CodeHelper\CodeInterface;

class TypeCall
{
	public function __construct(
		protected string|CodeInterface $call,
		protected array|CodeInterface $extraSource,
	) {}

	public function getCall(): string|CodeInterface
	{
		return $this->call;
	}

	public function getExtraSource(): array
	{
		if ($this->extraSource instanceof CodeInterface) {
			return $this->extraSource->getSourceArray();
		}
		return $this->extraSource;
	}
}
