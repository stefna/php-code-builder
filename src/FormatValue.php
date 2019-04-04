<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

class FormatValue
{
	public static function format($value): string
	{
		switch (gettype($value)) {
			case 'boolean':
				$value = $value ? 'true' : 'false';
				break;
			case 'NULL':
				$value = 'null';
				break;
			case 'string':
				//todo better escaping needed
				$value = "'$value'";
				break;
			case 'array':
				$value = '[]';
				break;
		}

		return (string)$value;
	}
}
