<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Renderer;

use Stefna\PhpCodeBuilder\Exception\InvalidCode;
use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\FormatValue;
use Stefna\PhpCodeBuilder\PhpDocComment;
use Stefna\PhpCodeBuilder\PhpDocElementFactory;
use Stefna\PhpCodeBuilder\PhpFunction;
use Stefna\PhpCodeBuilder\PhpMethod;
use Stefna\PhpCodeBuilder\PhpParam;
use Stefna\PhpCodeBuilder\PhpTrait;
use Stefna\PhpCodeBuilder\PhpVariable;
use Stefna\PhpCodeBuilder\ValueObject\Type;

class Php8Renderer extends Php74Renderer
{
	protected function formatTypeHint(?Type $type): ?string
	{
		if ($type?->isUnion()) {
			$typeHint = [];
			if ($type->isNullable()) {
				$typeHint[] = 'null';
			}
			foreach ($type->getUnionTypes() as $unionType) {
				$typeHint[] = $unionType->getTypeHint();
			}
			return implode('|', $typeHint);
		}

		return $type?->getTypeHint();
	}

	/**
	 * @return array<int, mixed>|null
	 */
	public function renderVariable(PhpVariable $variable, ?PhpTrait $parent = null): array|null
	{
		if ($variable->isPromoted()) {
			return null;
		}

		return parent::renderVariable($variable, $parent);
	}

	/**
	 * @return array<int, mixed>
	 */
	public function renderParams(PhpFunction $function, PhpParam ...$params): array|string
	{
		$propertyPromotion = false;
		if ($function instanceof PhpMethod &&
			$function->isConstructor() &&
			$function->doConstructorAutoAssign()
		) {
			$propertyPromotion = true;
		}

		$docBlock = $function->getComment() ?? new PhpDocComment();
		$parameterStrings = [];
		foreach ($params as $param) {
			if ($param->getType()->needDockBlockTypeHint() && !$param->getType()->isUnion()) {
				$docBlock->addParam(PhpDocElementFactory::getParam($param->getType(), $param->getName()));
			}
			$paramStr = $this->renderParam($param);
			if ($propertyPromotion && $param->getVariable()) {
				$paramStr = $this->renderPromotedPropertyModifiers(
					$param,
					$param->getVariable(),
					$function,
				) . ' ' . $paramStr;
			}
			$parameterStrings[] = $paramStr . ',';
		}

		if ($propertyPromotion || count($params) > 2) {
			return $parameterStrings;
		}

		return rtrim(implode(' ', $parameterStrings), ',');
	}

	public function renderParam(PhpParam $param): string
	{
		$type = $param->getType();
		$ret = '';
		if ($type->isUnion()) {
			$typeHint = [];
			if ($type->isNullable()) {
				$typeHint[] = 'null';
			}
			foreach ($type->getUnionTypes() as $unionType) {
				$typeHint[] = $unionType->getTypeHint();
			}

			$ret .= implode('|', $typeHint);
		}
		elseif ($type->getTypeHint()) {
			$ret .= $type->getTypeHint();
		}
		$ret .= ' $' . $param->getName();
		if ($param->getValue() !== PhpParam::NO_VALUE) {
			$value = FormatValue::format($param->getValue());
			if (is_array($value)) {
				throw new \RuntimeException('Don\'t support multiline values in params');
			}
			$ret .= ' = ' . $value;
		}

		return trim($ret);
	}

	/**
	 * @return array<int, string|array<int, string>>
	 */
	public function renderMethod(PhpMethod $method): array
	{
		$ret = $this->renderFunction($method);

		if ($method->isConstructor()) {
			if (is_array($ret[array_key_last($ret) - 1]) &&
				count($ret[array_key_last($ret) - 1]) === 0
			) {
				unset($ret[array_key_last($ret) - 1]);
				unset($ret[array_key_last($ret)]);
				$lastKey = (int)array_key_last($ret);
				if (!is_string($ret[$lastKey])) {
					throw InvalidCode::invalidType();
				}
				$ret[$lastKey] .= '}';
			}
		}

		return $ret;
	}

	public function renderComment(?PhpDocComment $comment): array
	{
		if (!$comment) {
			return [];
		}
		$parent = $comment->getParent();
		if ($comment->getVar() && $parent instanceof PhpVariable) {
			if ($parent->getType()->isUnion()) {
				$comment->removeVar();
			}
		}
		return parent::renderComment($comment);
	}

	protected function renderPromotedPropertyModifiers(
		PhpParam $param,
		PhpVariable $variable,
		PhpMethod $method,
	): string {
		return $variable->getAccess() ?? 'protected';
	}
}
