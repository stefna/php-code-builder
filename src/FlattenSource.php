<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\CodeHelper\CodeInterface;

class FlattenSource
{
	public static function source(array $source, int $level = 0): string
	{
		$ret = '';
		foreach ($source as $row) {
			if ($row instanceof CodeInterface) {
				$lines = $row->getSourceArray($level);
				if (count($lines) === 1 && is_string($lines[0])) {
					$ret .= $row->getSource($level);
				}
				else {
					foreach ($lines as $line) {
						if (is_array($line)) {
							$ret .= self::source($line, $level + 1);
						}
						else {
							$ret .= Indent::indent($level) . $line . PHP_EOL;
						}
					}
				}
			}
			elseif (is_array($row)) {
				$ret .= self::source($row, $level + 1);
			}
			else {
				$ret .= Indent::indent($level) . $row . PHP_EOL;
			}
		}

		return $ret;
	}
}
