<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\FlattenSource;

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
		$return = [$firstLine . $assignmentFirstLine];
		foreach ($assignmentLines as $line) {
			$return[] = $line;
		}
		$return[count($return) - 1] .= ';';
		return $return;
	}
}
