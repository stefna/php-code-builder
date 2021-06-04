<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\CodeHelper\ArrayCode;

class FormatValue
{
	public static function formatArray(array $data): string|array
	{
		$arrayCode = new ArrayCode($data);
		return $arrayCode->getSourceArray();
	}

	public static function format($value): array|string
	{
		$type = gettype($value);
		if ($type === 'array') {
			return self::formatArray($value);
		}

		return match ($type) {
			'boolean' => $value ? 'true' : 'false',
			'NULL' => 'null',
			'string' => "'$value'",
			'array' => '[]',
			'integer' => (string)$value,
		};
	}
}
