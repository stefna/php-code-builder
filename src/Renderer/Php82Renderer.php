<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Renderer;

use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\FormatValue;
use Stefna\PhpCodeBuilder\PhpClass;
use Stefna\PhpCodeBuilder\PhpEnum;
use Stefna\PhpCodeBuilder\PhpMethod;
use Stefna\PhpCodeBuilder\PhpParam;
use Stefna\PhpCodeBuilder\PhpTrait;
use Stefna\PhpCodeBuilder\PhpVariable;
use Stefna\PhpCodeBuilder\ValueObject\EnumBackedCase;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;
use Stefna\PhpCodeBuilder\ValueObject\Type;

class Php82Renderer extends Php81Renderer
{
	protected function formatVariableModifiers(
		PhpVariable $variable,
		?Type $type = null,
		?PhpTrait $parent = null,
	): array {
		if ($variable->isReadOnly() && $parent instanceof PhpClass && $parent->isReadOnly()) {
			$line = [];
			$line[] = $variable->getAccess() ?: 'public';
			$line[] = $this->formatTypeHint($type);
			return $line;
		}

		return parent::formatVariableModifiers($variable, $type);
	}

	protected function formatClassModifiers(PhpClass $class): array
	{
		$modifiers = parent::formatClassModifiers($class);
		if ($class->isReadOnly()) {
			$modifiers[] = 'readonly';
		}
		return $modifiers;
	}

	protected function renderPromotedPropertyModifiers(
		PhpParam $param,
		PhpVariable $variable,
		PhpMethod $method,
	): string {
		$cls = $method->getParent();
		if (!$cls instanceof PhpClass || !$cls->isReadOnly()) {
			return parent::renderPromotedPropertyModifiers($param, $variable, $method);
		}
		$modifiers = [];
		$modifiers[] = $variable->getAccess() ?? 'protected';
		return implode(' ', $modifiers);
	}
}
