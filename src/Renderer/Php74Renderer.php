<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Renderer;

use Stefna\PhpCodeBuilder\Exception\InvalidCode;
use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\FormatValue;
use Stefna\PhpCodeBuilder\PhpClass;
use Stefna\PhpCodeBuilder\PhpDocComment;
use Stefna\PhpCodeBuilder\PhpTrait;
use Stefna\PhpCodeBuilder\PhpVariable;
use Stefna\PhpCodeBuilder\ValueObject\Type;

class Php74Renderer extends Php7Renderer
{
	public function renderVariable(PhpVariable $variable, ?PhpTrait $parent = null): array|null
	{
		$ret = [];

		if ($variable->getType()->isNullable() &&
			$variable->getInitializedValue() === PhpVariable::NO_VALUE &&
			$variable->getType()->getTypeHint()
		) {
			$variable->setInitializedValue(null);
		}

		$comment = $variable->getComment();
		if ($comment) {
			$ret = FlattenSource::applySourceOn($this->renderComment($comment), $ret);
		}
		$attributes = $variable->getAttributes();
		if ($attributes) {
			foreach ($attributes as $attr) {
				$ret = FlattenSource::applySourceOn($this->renderAttribute($attr), $ret);
			}
		}

		$line = $this->formatVariableModifiers($variable, $variable->getType(), $parent);
		$line[] = '$' . $variable->getIdentifier()->getName();

		return $this->formatVariableValue($variable, implode(' ', $line), $ret);
	}

	/**
	 * @return array<int, mixed>
	 */
	protected function formatVariableModifiers(
		PhpVariable $variable,
		?Type $type = null,
		?PhpTrait $parent = null,
	): array {
		$line = [];
		$line[] = $variable->getAccess() ?: 'public';

		if ($variable->isStatic()) {
			$line[] = 'static';
		}

		$typeStr = $this->formatTypeHint($type);
		if ($typeStr) {
			$line[] = $typeStr;
		}

		return $line;
	}

	protected function formatTypeHint(?Type $type): ?string
	{
		return $type?->getTypeHint();
	}

	public function renderComment(?PhpDocComment $comment): array
	{
		if (!$comment) {
			return [];
		}
		$parent = $comment->getParent();
		if ($comment->getVar() && $parent instanceof PhpVariable) {
			if (!$parent->getType()->needDockBlockTypeHint()) {
				$comment->removeVar();
			}
		}
		return parent::renderComment($comment);
	}
}
