<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

final class ForeachCode implements CodeInterface
{
	/** @var callable(VariableReference $keyName, VariableReference $valueName): mixed[] */
	private $loopSourceCallback;

	/**
	 * @param callable(VariableReference $keyName, VariableReference $valueName): mixed[] $loopSourceCallback
	 */
	public function __construct(
		private VariableReference $reference,
		callable $loopSourceCallback,
		private string $variablePrefix = '',
	) {
		$this->loopSourceCallback = $loopSourceCallback;
	}

	/**
	 * @return array<int, mixed>
	 */
	public function getSourceArray(): array
	{
		$keyName = 'key';
		$valueName = 'value';
		if ($this->variablePrefix) {
			$keyName = $this->variablePrefix . ucfirst($keyName);
			$valueName = $this->variablePrefix . ucfirst($valueName);
		}

		$loopSource = ($this->loopSourceCallback)(
			new VariableReference($keyName),
			new VariableReference($valueName)
		);
		return [
			'foreach (' . $this->reference->toString() . ' as $' . $keyName . ' => $' . $valueName . ') {',
			$loopSource,
			'}',
		];
	}
}
