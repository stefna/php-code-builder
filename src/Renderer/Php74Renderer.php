<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Renderer;

use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\FormatValue;
use Stefna\PhpCodeBuilder\PhpDocComment;
use Stefna\PhpCodeBuilder\PhpVariable;

class Php74Renderer extends Php7Renderer
{
	public function renderVariable(PhpVariable $variable): array|null
	{
		$ret = [];

		$comment = $variable->getComment();
		if ($comment) {
			$ret = FlattenSource::applySourceOn($this->renderComment($comment), $ret);
		}

		$line = [];
		$line[] = $variable->getAccess() ?: 'public';

		if ($variable->isStatic()) {
			$line[] = 'static';
		}

		$typeStr = $variable->getType()->getTypeHint();
		if ($typeStr) {
			$line[] = $typeStr;
		}

		$line[] = '$' . $variable->getIdentifier()->getName();
		$lineStr = implode(' ', $line);

		if ($variable->getInitializedValue() !== PhpVariable::NO_VALUE) {
			$lineStr .= ' = ';
			$value = FormatValue::format($variable->getInitializedValue());
			if (is_array($value)) {
				if (count($value) > 1) {
					$lineStr .= array_shift($value);
					$ret[] = $lineStr;
				}
				$ret = FlattenSource::applySourceOn($value, $ret);
				$ret[array_key_last($ret)] .= ';';
			}
			else {
				$lineStr .= $value;
				$ret[] = $lineStr . ';';
			}
		}
		else {
			$ret[] = $lineStr . ';';
		}

		return $ret;
	}

	public function renderComment(PhpDocComment $comment): array
	{
		$parent = $comment->getParent();
		if ($comment->getVar() && $parent instanceof PhpVariable) {
			if (!$parent->getType()->needDockBlockTypeHint()) {
				$comment->removeVar();
			}
		}
		return parent::renderComment($comment);
	}
}
