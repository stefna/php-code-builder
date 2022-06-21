<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\CodeHelper\CodeInterface;
use Stefna\PhpCodeBuilder\Renderer\RenderInterface;

/**
 * @phpstan-import-type SourceArray from RenderInterface
 */
class FlattenSource
{
	/**
	 * @phpstan-param string|SourceArray $source
	 * @phpstan-param SourceArray $on
	 * @phpstan-return SourceArray
	 */
	public static function applySourceOn(string|array $source, array $on): array
	{
		if (is_string($source)) {
			$on[] = $source;
			return $on;
		}

		foreach ($source as $line) {
			$on[] = $line;
		}
		return $on;
	}

	/**
	 * @param array<int, array<int, string>|string|CodeInterface>|string $source
	 * @param int $level
	 * @return string
	 */
	public static function source(array|string $source, int $level = 0): string
	{
		if (is_string($source)) {
			return $source;
		}
		$ret = '';
		foreach ($source as $row) {
			if ($row instanceof CodeInterface) {
				$lines = $row->getSourceArray();
				if (count($lines) === 1 && is_string($lines[0])) {
					$ret .= Indent::indent($level) . $lines[0] . PHP_EOL;
				}
				else {
					foreach ($lines as $line) {
						if (is_array($line)) {
							$ret .= self::source($line, $level + 1);
						}
						elseif ($line === '') {
							$ret .= PHP_EOL;
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
			elseif ($row === '') {
				$ret .= PHP_EOL;
			}
			else {
				$ret .= Indent::indent($level) . $row . PHP_EOL;
			}
		}

		return $ret;
	}
}
