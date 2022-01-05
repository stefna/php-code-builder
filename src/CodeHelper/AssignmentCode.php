<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\Exception\InvalidCode;

final class AssignmentCode implements CodeInterface
{
	public function __construct(
		private VariableReference $variable,
		private CodeInterface $assignment,
	) {}

	public function getSourceArray(): array
	{
		$firstLine = $this->variable->toString() . ' = ';
		$assignmentLines = $this->assignment->getSourceArray();
		$assignmentFirstLine = array_shift($assignmentLines);
		if (!is_string($assignmentFirstLine)) {
			throw InvalidCode::invalidType();
		}
		$return = [$firstLine . $assignmentFirstLine];
		foreach ($assignmentLines as $line) {
			$return[] = $line;
		}

		$lastKey = (int)array_key_last($return);
		if (!is_string($return[$lastKey])) {
			throw InvalidCode::invalidType();
		}
		$return[$lastKey] .= ';';
		return $return;
	}
}
