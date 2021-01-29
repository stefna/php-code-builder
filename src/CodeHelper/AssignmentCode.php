<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\FlattenSource;

final class AssignmentCode implements CodeInterface
{
	/** @var VariableReference */
	private $variable;
	/** @var CodeInterface */
	private $assignment;

	public function __construct(VariableReference $variable, CodeInterface $assignment)
	{
		$this->variable = $variable;
		$this->assignment = $assignment;
	}

	public function getSource(int $currentIndent = 0): string
	{
		return FlattenSource::source($this->getSourceArray());
	}

	public function getSourceArray(int $currentIndent = 0): array
	{
		$firstLine = $this->variable->getSource() . ' = ';
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
