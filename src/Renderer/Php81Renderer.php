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

class Php81Renderer extends Php8Renderer
{
	protected function formatVariableModifiers(
		PhpVariable $variable,
		?Type $type = null,
		?PhpTrait $parent = null,
	): array {
		if ($variable->isReadOnly() || ($parent instanceof PhpClass && $parent->isReadOnly())) {
			$line = [];
			$line[] = $variable->getAccess() ?: 'public';
			$line[] = 'readonly';
			$line[] = $this->formatTypeHint($type);
			return $line;
		}

		return parent::formatVariableModifiers($variable, $type, $parent);
	}

	protected function renderPromotedPropertyModifiers(
		PhpParam $param,
		PhpVariable $variable,
		PhpMethod $method,
	): string {
		$modifiers = [];
		$modifiers[] = parent::renderPromotedPropertyModifiers($param, $variable, $method);
		$cls = $method->getParent();
		if ($variable->isReadOnly() || ($cls instanceof PhpClass && $cls->isReadOnly())) {
			$modifiers[] = 'readonly';
		}
		return implode(' ', $modifiers);
	}

	public function renderEnum(PhpEnum $enum): array
	{
		$ret = FlattenSource::applySourceOn($this->renderComment($enum->getComment()), []);

		$declaration = [];
		$declaration[] = 'enum';
		$declaration[] = $enum->getIdentifier()->getName();
		if ($enum->isBacked()) {
			$declaration[array_key_last($declaration)] .= ':';
			$declaration[] = $enum->getBackedType()->getTypeHint();
		}

		$implements = $enum->getImplements();
		if ($implements) {
			$declaration[] = 'implements';
			$implementsDeclaration = [];
			$multiline = count($implements) > 2;
			foreach ($implements as $identifier) {
				if ($multiline) {
					$implementsDeclaration[] = $identifier->toString() . ',';
				}
				else {
					$implementsDeclaration[] = $identifier->toString();
				}
			}
			if ($multiline) {
				$ret[] = implode(' ', $declaration);
				$implementsDeclaration[array_key_last($implementsDeclaration)] = substr(
					$implementsDeclaration[array_key_last($implementsDeclaration)],
					0,
					-1,
				);
				$ret[] = $implementsDeclaration;
			}
			else {
				$declaration[] = implode(', ', $implementsDeclaration);
				$ret[] = implode(' ', $declaration);
			}
		}
		else {
			$ret[] = implode(' ', $declaration);
		}

		$ret[] = '{';
		$ret = FlattenSource::applySourceOn($this->renderEnumBody($enum), $ret);
		$ret[] = '}';

		return $ret;
	}

	/**
	 * @return array<int, string|array<int, string>>
	 */
	private function renderEnumBody(PhpEnum $obj): array
	{
		$classBody = [];

		$addNewLine = false;
		$traits = $obj->getTraits();
		if ($traits) {
			$addNewLine = true;
			foreach ($traits as $trait) {
				$classBody[] = ['use ' . $trait->toString() . ';'];
			}
		}

		$cases = $obj->getCases();
		if (count($cases)) {
			if ($addNewLine) {
				$classBody[] = '';
			}
			$isBacked = $obj->isBacked();
			$caseLines = [];
			foreach ($cases as $case) {
				if ($isBacked && !$case instanceof EnumBackedCase) {
					throw new \BadMethodCallException('Backed enum must only contain backed cases');
				}
				$line = 'case ' . $case->getName();
				if ($isBacked && $case instanceof EnumBackedCase) {
					// @phpstan-ignore-next-line - we know it's not an array
					$line .= ' = ' . FormatValue::format($case->getValue());
				}
				$caseLines[] = $line . ';';
			}
			$classBody[] = $caseLines;
			$addNewLine = true;
		}

		$constants = $obj->getConstants();
		if (count($constants)) {
			if ($addNewLine) {
				$classBody[] = '';
			}
			foreach ($constants as $identifier) {
				$classBody[] = $this->renderConstant($constants[$identifier]);
			}
			$addNewLine = true;
		}

		$methods = $obj->getMethods();
		if (count($methods)) {
			if ($addNewLine) {
				$classBody[] = '';
			}
			/** @var Identifier $identifier */
			foreach ($methods as $identifier) {
				if ($identifier->toString() === '__construct') {
					// render constructor separately so it's always on top
					continue;
				}
				$classBody[] = $this->renderMethod($methods[$identifier]);
				$classBody[] = '';
			}
			array_pop($classBody);
		}
		return $classBody;
	}
}
