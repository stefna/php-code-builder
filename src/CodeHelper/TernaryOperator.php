<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

final class TernaryOperator implements CodeInterface
{
	public static function nullableCall(VariableReference $variableReference, ClassMethodCall $call): self
	{
		return new self(
			$variableReference->toString(),
			$call->getSourceArray()[0],
			'null',
		);
	}

	public function __construct(
		private string $check,
		private string $successCode,
		private string $failureCode,
	) {}

	public function getSourceArray(): array
	{
		return [
			$this->check .' ? ' . $this->successCode . ' : ' . $this->failureCode,
		];
	}
}
