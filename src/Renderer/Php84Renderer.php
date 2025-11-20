<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Renderer;

use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\FormatValue;
use Stefna\PhpCodeBuilder\PhpClass;
use Stefna\PhpCodeBuilder\PhpConstant;
use Stefna\PhpCodeBuilder\PhpEnum;
use Stefna\PhpCodeBuilder\PhpMethod;
use Stefna\PhpCodeBuilder\PhpParam;
use Stefna\PhpCodeBuilder\PhpTrait;
use Stefna\PhpCodeBuilder\PhpVariable;
use Stefna\PhpCodeBuilder\ValueObject\EnumBackedCase;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;
use Stefna\PhpCodeBuilder\ValueObject\Type;

class Php84Renderer extends Php82Renderer
{
	public function renderConstant(PhpConstant $constant): array
	{
		$ret = [];
		$line = [];
		$access = $constant->getAccess();
		if ($access) {
			$line[] = $access;
		}

		$line[] = 'const';
		$type = $this->formatTypeHint($constant->getType());
		if ($type) {
			$line[] = $type;
		}
		$line[] = $constant->getName();
		$line[] = '=';
		$lineStr = implode(' ', $line);

		$value = FormatValue::format($constant->getValue());
		if (is_array($value)) {
			if (count($value) > 1) {
				$lineStr .= ' ' . array_shift($value);
				$ret[] = $lineStr;
			}
			$ret = FlattenSource::applySourceOn($value, $ret);
			$lastKey = (int)array_key_last($ret);
			if (is_string($ret[$lastKey])) {
				$ret[$lastKey] .= ';';
			}
		}
		else {
			$lineStr .= ' ' . $value;
			$ret[] = $lineStr . ';';
		}

		return $ret;
	}
}
