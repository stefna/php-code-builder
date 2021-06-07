<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\CodeHelper\ArrayCode;
use Stefna\PhpCodeBuilder\CodeHelper\CodeInterface;

class FormatValue
{
	/**
	 * @param mixed[] $data
	 * @return mixed[]
	 */
	public static function formatArray(array $data): string|array
	{
		$arrayCode = new ArrayCode($data);
		return $arrayCode->getSourceArray();
	}

	/**
	 * @return mixed[]|string
	 */
	public static function format(mixed $value): array|string
	{
		if ($value instanceof CodeInterface) {
			return $value->getSourceArray();
		}
		$type = gettype($value);
		if ($type === 'array') {
			return self::formatArray($value);
		}

		return match ($type) {
			'array' => self::formatArray($value),
			'boolean' => $value ? 'true' : 'false',
			'NULL' => 'null',
			'integer', 'double' => (string)$value,
			'object', 'resource', 'resource (closed)', 'unknown type' => throw new \RuntimeException('Not supported value type'),
			'string' => "'$value'",
		};
	}
}
