<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\FormatValue;

trait MethodParamsTrait
{
	/** @var string */
	private $identifier;
	private $callIdentifier = '->';
	private $params = [];

	private function buildSourceArray(int $currentIndent): array
	{
		$return = [];
		$firstLine = $this->identifier . $this->callIdentifier . $this->method . '(';
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

	private function getSourceForParams(int $currentLength, int $currentIndent): array
	{
		$params = [];
		foreach ($this->params as $param) {
			if ($param instanceof CodeInterface) {
				$value = $param->getSourceArray($currentIndent);
				$currentLength += strlen($param->getSource());
			}
			else {
				$value = FormatValue::format($param);
				$currentLength += strlen($value);
			}
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
					$value = $param->getSourceArray($currentIndent);
					if ($previousArray) {
						$return[$currentIndex - 1] .= ', ' . array_shift($value);
						foreach ($value as $c) {
							$return[] = $c;
							$currentIndex += 1;
						}
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
