<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

class FlattenSource
{
	public static function source(array $source, int $level = 0): string
	{
		$ret = '';
		foreach ($source as $row) {
			if (is_array($row)) {
				$ret .= self::source($row, $level + 1);
			}
			else {
				$ret .= Indent::indent($level) . $row . PHP_EOL;
			}
		}

		return $ret;
	}
}
