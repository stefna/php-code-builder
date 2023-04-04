<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\Renderer\RenderInterface;

/**
 * @phpstan-import-type SourceArray from RenderInterface
 */
final class TernaryOperator implements CodeInterface
{
	public static function nullableCall(
		VariableReference $variableReference,
		MethodCallInterface&CodeInterface $call,
	): self {
		return new self(
			$variableReference->toString(),
			$call->getSourceArray()[0],
			'null',
		);
	}

	public function __construct(
		private string|VariableReference $check,
		/** @phpstan-var string|CodeInterface|SourceArray */
		private string|CodeInterface|array $successCode,
		/** @phpstan-var string|CodeInterface|SourceArray */
		private string|CodeInterface|array $failureCode,
	) {}

	public function getSourceArray(): array
	{
		$check = $this->check;
		if ($check instanceof VariableReference) {
			$check = $check->toString();
		}

		/** @var list<string> $source */
		$source = [
			$check . ' ? ',
		];

		if ($this->successCode instanceof CodeInterface) {
			$this->successCode = $this->successCode->getSourceArray();
		}
		if ($this->failureCode instanceof CodeInterface) {
			$this->failureCode = $this->failureCode->getSourceArray();
		}

		if (is_string($this->successCode)) {
			$source[0] .= $this->successCode;
			$source[0] .= ' : ';
		}
		elseif (count($this->successCode) === 1 && is_string($this->successCode[0])) {
			$source[0] .= $this->successCode[0];
			$source[0] .= ' : ';
		}
		else {
			$code = $this->successCode;
			if (is_string($code[array_key_last($code)])) {
				$code[array_key_last($code)] .= ' : ';
			}
			if (!is_array($code[array_key_first($code)])) {
				/** @var string $firstLine */
				$firstLine = array_shift($code);
				$source[0] .= $firstLine;
			}
			$source = FlattenSource::applySourceOn($code, $source);
		}

		if (is_string($this->failureCode)) {
			if (is_string($source[array_key_last($source)])) {
				$source[array_key_last($source)] .= $this->failureCode;
			}
			else {
				$source[] = $this->failureCode;
			}
		}
		elseif (count($this->failureCode) === 1) {
			if (is_string($source[array_key_last($source)])) {
				$source[array_key_last($source)] .= $this->failureCode[0];
			}
			else {
				$source[] = $this->failureCode;
			}
		}
		else {
			$code = $this->failureCode;
			if (!is_array($code[array_key_first($code)])) {
				$firstLine = array_shift($code);
				$source[array_key_last($source)] .= $firstLine;
			}
			$source = FlattenSource::applySourceOn($code, $source);
		}

		return $source;
	}
}
