<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper\IfHelper;

enum IfType
{
	case If;
	case ElseIf;
	case Else;

	public function toStmt(): string
	{
		return match ($this) {
			self::If => 'if',
			self::ElseIf => 'elseif',
			self::Else => 'else',
		};
	}
}
