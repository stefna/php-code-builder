<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Renderer;

use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\FormatValue;
use Stefna\PhpCodeBuilder\PhpClass;
use Stefna\PhpCodeBuilder\PhpDocComment;
use Stefna\PhpCodeBuilder\PhpDocElementFactory;
use Stefna\PhpCodeBuilder\PhpFunction;
use Stefna\PhpCodeBuilder\PhpInterface;
use Stefna\PhpCodeBuilder\PhpMethod;
use Stefna\PhpCodeBuilder\PhpParam;
use Stefna\PhpCodeBuilder\PhpTrait;
use Stefna\PhpCodeBuilder\PhpVariable;

class Php8Renderer extends Php74Renderer
{
	public function renderVariable(PhpVariable $variable): array|null
	{
		if ($variable->isPromoted()) {
			return null;
		}
		$type = $variable->getType();
		if ($type->isUnion()) {
			$ret = [];

			$line = [];
			$line[] = $variable->getAccess() ?: 'public';

			if ($variable->isStatic()) {
				$line[] = 'static';
			}

			$typeHint = [];
			if ($type->isNullable()) {
				$typeHint[] = 'null';
			}
			foreach ($type->getUnionTypes() as $unionType) {
				$typeHint[] = $unionType->getTypeHint();
			}

			$comment = $variable->getComment();
			$comment->removeVar();
			if ($comment) {
				$ret = FlattenSource::applySourceOn($this->renderComment($comment), $ret);
			}

			$line[] = implode('|', $typeHint);
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

		return parent::renderVariable($variable);
	}

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
				$access = $param->getVariable()?->getAccess() ?? 'protected';
				$paramStr = $access . ' ' . $paramStr;
			}
			$parameterStrings[] = $paramStr . ',';
		}

		if (count($params) > 2 || $propertyPromotion) {
			return $parameterStrings;
		}
		else {
			return rtrim(implode(' ', $parameterStrings), ',');
		}
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
			$ret .= ' = ' . FormatValue::format($param->getValue());
		}

		return trim($ret);
	}

	public function renderMethod(PhpMethod $method): array
	{
		$ret = $this->renderFunction($method);

		if ($method->isConstructor()) {
			if (is_array($ret[array_key_last($ret) - 1]) && count($ret[array_key_last($ret) - 1]) === 0) {
				unset($ret[array_key_last($ret) - 1]);
				unset($ret[array_key_last($ret)]);
				$ret[array_key_last($ret)] .= '}';
			}
		}

		return $ret;
	}
}
