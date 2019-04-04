<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

class Indent
{
	private static $indent = "\t";

	public static function setIndent(string $indent)
	{
		self::$indent = $indent;
	}

	public static function indent(int $level): string
	{
		return str_repeat(self::$indent, $level);
	}
}
