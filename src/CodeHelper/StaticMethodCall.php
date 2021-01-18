<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\FormatValue;
use Stefna\PhpCodeBuilder\Indent;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;

final class StaticMethodCall implements CodeInterface
{
	private $class;

	private $method;

	private $params;

	private $indentFirstLine = false;

	public function __construct(Identifier $class, string $method, array $params = [])
	{
		$this->class = $class;
		$this->method = $method;
		$this->params = $params;
	}

	/**
	 * @param bool $indentFirstLine
	 */
	public function setIndentFirstLine(bool $indentFirstLine): void
	{
		$this->indentFirstLine = $indentFirstLine;
	}

	public function getSourceArray(int $currentIndent = 0): array
	{
		$return = [];
		$firstLine = ($this->indentFirstLine ? Indent::indent($currentIndent) : '');
		$firstLine .= $this->class->getName() . '::' . $this->method . '(';
		if (count($this->params)) {
			$paramSource = $this->getSourceForParams(strlen($firstLine), $currentIndent);
			if (count($paramSource) === 1) {
				if (is_string($paramSource[0]) && strlen($paramSource[0]) < 100) {
					return [$firstLine . $paramSource[0] . ')'];
				}
				elseif (is_array($paramSource[0])) {
					$return = [$firstLine . array_shift($paramSource[0])];
					foreach ($paramSource[0] as $row) {
						$return[] = $row;
					}
					$return[count($return) - 1] .= ')';
					return $return;
				}
			}
			if (is_string($paramSource[0]) && strpos($paramSource[0], ',')) {
				$return[] = $firstLine . array_shift($paramSource);
			}
			else {
				$return[] = $firstLine;
				$params = count($paramSource);
				$renderedValues = [];
				$lastValueIsArray = is_array($paramSource[$params - 1]);
				foreach ($paramSource as $key => $row) {
					if (is_string($row)) {
						$value = $row;
						if ($key !== $params - 1) {
							$value .= ',';
						}
						$renderedValues[] = $value;
					}
					else {
						$renderedValues[] = $row;
					}
				}
				if ($lastValueIsArray) {
					$renderedValues[count($renderedValues) - 1] = ')';
				}
				$return[] = $renderedValues;
				if (!$lastValueIsArray) {
					$return[] = ')';
				}
				return $return;
			}
			foreach ($paramSource as $row) {
				$return[] = $row;
			}

			$lastRow = $return[count($return) - 1];
			// if last param is an array append closing parentis
			if ($lastRow === ']') {
				$return[count($return) - 1] .= ')';
			}
			// if last param have multiple values append closing parentis
			elseif (is_string($lastRow) && strpos($lastRow, ',')) {
				$return[count($return) - 1] .= ')';
			}
			else {
				$return[] = ')';
			}

			return $return;
		}
		else {
			$return[] = $firstLine . ')';
		}
		return $return;
	}

	public function getSource(int $currentIndent = 0): string
	{
		return FlattenSource::source($this->getSourceArray(), $currentIndent);
	}

	private function getSourceForParams(int $currentLength, int $currentIndent): array
	{
		$params = [];
		foreach ($this->params as $param) {
			if ($param instanceof CodeInterface) {
				$value = $param->getSource($currentIndent);
			}
			else {
				$value = FormatValue::format($param);
			}
			$currentLength += strlen($value);
			$params[] = $value;
		}

		if ($currentLength < 90) {
			return [implode(', ', $params)];
		}
		$return = [];
		$previousArray = false;
		$currentIndex = 0;
		foreach ($this->params as $key => $param) {
			if ($param instanceof CodeInterface) {
				if ($param instanceof ArrayCode) {
					if (isset($return[$currentIndex - 1]) && is_string($return[$key - 1])) {
						$param->setIndentFirstLine(false);
						$value = $param->getSourceArray($currentIndent);
						$return[$currentIndex - 1] .= ', ' . array_shift($value);
						foreach ($value as $x) {
							$return[] = $x;
							$currentIndex += 1;
						}
					}
					else {
						$return[] = $param->getSourceArray($currentIndent);
						$currentIndex += 1;
					}
					$previousArray = true;
				}
				else {
					$value = $param->getSource($currentIndent);
					if ($previousArray) {
						$return[$currentIndex - 1] .= ', ' . $value;
					}
					else {
						$return[] = $value;
					}

					$currentIndex += 1;
				}
			}
			else {
				$return[] = FormatValue::format($param);
				$currentIndex += 1;
			}
		}

		return $return;
	}
}
