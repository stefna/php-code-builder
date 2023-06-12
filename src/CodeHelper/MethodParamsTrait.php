<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\Exception\InvalidCode;
use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\FormatValue;

trait MethodParamsTrait
{
	private string $identifier;
	private string $callIdentifier = '->';

	/**
	 * @return array<int, mixed>
	 */
	private function buildSourceArray(): array
	{
		$return = [];
		$firstLine = $this->identifier . $this->callIdentifier . $this->method . '(';
		if (!count($this->params)) {
			$return[] = $firstLine . ')';
			return $return;
		}

		$paramSource = $this->getSourceForParams();
		if (count($paramSource) === 1) {
			if (is_string($paramSource[0]) && strlen($paramSource[0]) < 100) {
				return [$firstLine . $paramSource[0] . ')'];
			}
			if (is_array($paramSource[0])) {
				$firstParamSource = array_shift($paramSource[0]);
				if (!is_string($firstParamSource)) {
					throw InvalidCode::invalidType();
				}
				$return = [$firstLine . $firstParamSource];
				foreach ($paramSource[0] as $row) {
					$return[] = $row;
				}
				$lastRowKey = (int)array_key_last($return);
				if (!is_string($return[$lastRowKey])) {
					throw InvalidCode::invalidType();
				}
				$return[$lastRowKey] .= ')';
				return $return;
			}
		}
		elseif (count($paramSource) === 3 && $paramSource[0] === '[' && $paramSource[2] === ']') {
			return [$firstLine . '[', $paramSource[1] , '])'];
		}

		if (is_string($paramSource[0]) && strpos($paramSource[0], ',')) {
			$firstParamSource = array_shift($paramSource);
			if (!is_string($firstParamSource)) {
				throw InvalidCode::invalidType();
			}
			$return[] = $firstLine . $firstParamSource;
		}
		else {
			$return[] = $firstLine;
			$params = count($paramSource);
			$renderedValues = [];
			$lastValueIsArray = is_array($paramSource[$params - 1]);
			foreach ($paramSource as $key => $row) {
				if (is_string($row)) {
					$value = $row;
					if ($key !== $params - 1 && $value !== '[') {
						$value .= ',';
					}
					$renderedValues[] = $value;
				}
				else {
					$renderedValues[] = $row;
				}
			}
			if ($lastValueIsArray) {
				$tmpArray = $renderedValues[count($renderedValues) - 1];
				unset($renderedValues[count($renderedValues) - 1]);
				$renderedValues = FlattenSource::applySourceOn($tmpArray, $renderedValues);
			}
			$return[] = $renderedValues;
			$return[] = ')';
			return $return;
		}

		foreach ($paramSource as $row) {
			$return[] = $row;
		}

		$lastRowKey = (int)array_key_last($return);
		$lastRow = $return[$lastRowKey];
		// if last param is an array append closing parentis
		if (is_string($return[$lastRowKey]) && $return[$lastRowKey] === ']') {
			$return[$lastRowKey] .= ')';
		}
		// if last param have multiple values append closing parentis
		elseif (is_string($return[$lastRowKey]) && strpos($return[$lastRowKey], ',')) {
			$return[$lastRowKey] .= ')';
		}
		else {
			$return[] = ')';
		}

		return $return;
	}

	/**
	 * @return array<int, mixed>
	 */
	private function getSourceForParams(): array
	{
		$params = [];
		$isComplex = false;
		foreach ($this->params as $param) {
			if ($param instanceof CodeInterface) {
				if ($param instanceof VariableReference) {
					$value = $param->toString();
				}
				else {
					$value = $param->getSourceArray();
					if (count($value) === 1) {
						$value = $value[0];
					}
				}
			}
			else {
				$value = FormatValue::format($param);
			}
			if (is_array($value)) {
				$isComplex = true;
			}
			$params[] = $value;
		}

		if (!$isComplex && count($params) < 3) {
			return [implode(', ', $params)];
		}

		$return = [];
		$previousArray = false;
		$currentIndex = 0;
		$paramCount = count($this->params);
		$noCustomFirstLine = $paramCount > 4;
		foreach ($this->params as $key => $param) {
			$isLast = $key - 1 === $paramCount;
			if (!$param instanceof CodeInterface) {
				$return[] = FormatValue::format($param);
				$currentIndex += 1;
				continue;
			}

			if ($param instanceof ArrayCode) {
				$previousIndex = $currentIndex - 1;
				if (!$noCustomFirstLine && $previousIndex === 0 && isset($return[$previousIndex]) && is_string($return[$key - 1])) {
					$value = $param->getSourceArray();
					$return[$currentIndex - 1] .= ', ' . array_shift($value);
					foreach ($value as $x) {
						$return[] = $x;
						$currentIndex += 1;
					}
				}
				else {
					foreach ($param->getSourceArray() as $v) {
						$return[] = $v;
					}
					$currentIndex += 1;
				}
				$previousArray = true;
				continue;
			}

			if ($param instanceof VariableReference) {
				$value = $param->toString();
			}
			$value = $param->getSourceArray();
			if ($previousArray) {
				$tmpValue = array_shift($value);
				if (!is_scalar($tmpValue)) {
					throw InvalidCode::invalidType();
				}
				if ($return[$currentIndex - 1] !== '[') {
					$return[$currentIndex - 1] .= ', ' . $tmpValue;
				}
				foreach ($value as $c) {
					$return[] = $c;
					$currentIndex += 1;
				}
			}
			elseif (count($value) === 1) {
				$return[] = $value[0];
			}
			else {
				$return[] = $value;
			}

			$currentIndex += 1;
		}
		return $return;
	}
}
