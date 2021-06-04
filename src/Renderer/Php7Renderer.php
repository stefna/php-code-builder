<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Renderer;

use PhpParser\Builder\Interface_;
use Stefna\PhpCodeBuilder\FlattenSource;
use Stefna\PhpCodeBuilder\FormatValue;
use Stefna\PhpCodeBuilder\PhpClass;
use Stefna\PhpCodeBuilder\PhpConstant;
use Stefna\PhpCodeBuilder\PhpDocComment;
use Stefna\PhpCodeBuilder\PhpDocElementFactory;
use Stefna\PhpCodeBuilder\PhpFile;
use Stefna\PhpCodeBuilder\PhpFunction;
use Stefna\PhpCodeBuilder\PhpInterface;
use Stefna\PhpCodeBuilder\PhpMethod;
use Stefna\PhpCodeBuilder\PhpParam;
use Stefna\PhpCodeBuilder\PhpTrait;
use Stefna\PhpCodeBuilder\PhpVariable;

class Php7Renderer implements RendererInterface
{
	public function renderFile(PhpFile $file): array
	{
		$declaration = '<?php';
		if ($file->isStrict()) {
			$declaration .= ' declare(strict_types=1);';
		}
		$ret = [];
		$ret[] = $declaration;
		$ret[] = '';

		$namespace = $file->getNamespace() ?? '';

		$classes = $file->getClasses();
		if (count($classes) === 1) {
			$classes->rewind();
			$class = $classes->current();
			if ($class->getNamespace()) {
				$namespace .= $class->getNamespace();
				$namespace = trim($namespace, '\\');
			}
		}

		if ($namespace) {
			$ret[] = 'namespace ' . $namespace . ';';
			$ret[] = '';
		}

		foreach ($classes as $identifier) {
			/** @var PhpTrait|PhpClass $class */
			$class = $classes[$identifier];
			foreach ($class->getUses() as $useIdentifier) {
				$file->addUse($useIdentifier);
			}
		}

		$uses = $file->getUse();
		if (count($uses) > 0) {
			foreach ($uses as $identifier) {
				if ($identifier->getNamespace() === $namespace) {
					// don't need to add use statements for same namespace as file
					continue;
				}
				$useLine = 'use ' . ltrim($identifier->getFqcn(), '\\');
				if ($identifier->getAlias()) {
					$useLine .= ' as ' . $identifier->getAlias();
				}
				$useLine .= ';';
				$ret[] = $useLine;
			}
			$ret[] = '';
		}

		foreach ($classes as $identifier) {
			/** @var PhpTrait|PhpClass|PhpInterface $class */
			$class = $classes[$identifier];
			if ($class instanceof PhpInterface) {
				$source = $this->renderInterface($class);
			}
			elseif ($class instanceof PhpClass) {
				$source = $this->renderClass($class);
			}
			else {
				$source = $this->renderTrait($class);
			}
			$ret = FlattenSource::applySourceOn($source, $ret);
		}

		$functions = $file->getFunctions();
		if (count($functions) > 0) {
			foreach ($functions as $function) {
				$ret = FlattenSource::applySourceOn($this->renderFunction($function), $ret);
			}
		}

		if ($file->getSource()) {
			$ret = FlattenSource::applySourceOn($file->getSource(), $ret);
		}

		return $ret;
	}

	public function renderClass(PhpClass $class): array
	{
		$ret = FlattenSource::applySourceOn($this->renderComment($class->getComment()), []);

		$declaration = [];
		if ($class->isFinal()) {
			$declaration[] = 'final';
		}
		elseif ($class->isAbstract()) {
			$declaration[] = 'abstract';
		}
		$declaration[] = 'class';
		$declaration[] = $class->getIdentifier()->getName();
		$extends = $class->getExtends();
		if ($extends) {
			$declaration[] = 'extends';
			$declaration[] = $extends->getName();
		}

		$implements = $class->getImplements();
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
		$ret = FlattenSource::applySourceOn($this->renderObjectBody($class), $ret);
		$ret[] = '}';

		return $ret;
	}

	public function renderInterface(PhpInterface $interface): array
	{
		$ret = FlattenSource::applySourceOn($this->renderComment($interface->getComment()), []);

		$declaration = 'interface ' . $interface->getIdentifier()->getName();
		$extends = $interface->getExtends();
		if ($extends) {
			$declaration .= ' extends';
			$extendDeclaration = [];
			$multiline = count($extends) > 2;
			foreach ($extends as $identifier) {
				if ($multiline) {
					$extendDeclaration[] = $identifier->toString() . ',';
				}
				else {
					$extendDeclaration[] = $identifier->toString();
				}
			}
			if ($multiline) {
				$ret[] = $declaration;
				$extendDeclaration[array_key_last($extendDeclaration)] = substr(
					$extendDeclaration[array_key_last($extendDeclaration)],
					0,
					-1,
				);
				$ret[] = $extendDeclaration;
			}
			else {
				$ret[] = $declaration . ' ' . implode(', ', $extendDeclaration);
			}
		}
		else {
			$ret[] = $declaration;
		}

		$ret[] = '{';
		$ret = FlattenSource::applySourceOn($this->renderObjectBody($interface), $ret);
		$ret[] = '}';

		return $ret;
	}

	public function renderTrait(PhpTrait $trait): array
	{
		$ret = FlattenSource::applySourceOn($this->renderComment($trait->getComment()), []);

		$ret[] = 'trait ' . $trait->getIdentifier()->getName();
		$ret[] = '{';
		$ret = FlattenSource::applySourceOn($this->renderObjectBody($trait), $ret);
		$ret[] = '}';

		return $ret;
	}

	public function renderVariable(PhpVariable $variable): array|null
	{
		$ret = [];

		$comment = $variable->getComment();
		if (!$comment && !$variable->getType()->isEmpty()) {
			$comment = PhpDocComment::var($variable->getType());
		}
		if ($comment) {
			$ret = FlattenSource::applySourceOn($this->renderComment($comment), $ret);
		}

		$line = [];
		$access = $variable->getAccess();
		if (!$access) {
			$access = 'public';
		}
		$line[] = $access;

		if ($variable->isStatic()) {
			$line[] = 'static';
		}

		$line[] = '$' . $variable->getIdentifier()->getName();

		$lineStr = implode(' ', $line);

		if ($variable->getInitializedValue() !== PhpVariable::NO_VALUE) {
			$lineStr .= ' = ';
			$value = FormatValue::format($variable->getInitializedValue());
			if (is_array($value)) {
				if (count($value) === 1) {
					$ret[] = $lineStr . $value[0] . ';';
					return $ret;
				}
				$lineStr .= array_shift($value);
				$ret[] = $lineStr;
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

	public function renderMethod(PhpMethod $method): array
	{
		if ($method->isConstructor() && $method->doConstructorAutoAssign()) {
			$body = $method->getBody();

			foreach ($method->getParams() as $param) {
				if ($param->getVariable()) {
					$body[] = sprintf('$this->%s = $%s;', $param->getName(), $param->getName());
				}
			}
			$method->setBody($body);
		}

		return $this->renderFunction($method);
	}

	public function renderFunction(PhpFunction $function): array
	{
		$ret = [];

		if (!$function->getComment()) {
			$function->setComment(new PhpDocComment(''));
		}

		if ($function->getReturnType()->needDockBlockTypeHint()) {
			$function->getComment()
				->setReturn(PhpDocElementFactory::getReturn($function->getReturnType()->getDocBlockTypeHint()));
		}

		$ret = FlattenSource::applySourceOn($this->renderFunctionSignature($function), $ret);
		$isAbstract = $function instanceof PhpMethod && (
			$function->isAbstract() ||
			$function->getParent() instanceof PhpInterface
		);

		if ($isAbstract) {
			return $ret;
		}

		$ret[] = $function->getBody();
		$ret[] = '}';

		return $ret;
	}

	public function renderConstant(PhpConstant $constant): array
	{
		$ret = [];
		$line = [];
		$access = $constant->getAccess();
		if ($access) {
			$line[] = $access;
		}

		$line[] = 'const';
		$line[] = $constant->getName();
		$line[] = '=';
		$lineStr = implode(' ', $line);

		$value = FormatValue::format($constant->getValue());
		if (is_array($value)) {
			if (count($value) > 1) {
				$lineStr .= ' ' . array_shift($value);
				$ret[] = $lineStr;
			}
			$ret = FlattenSource::applySourceOn($value, $ret);
			$ret[array_key_last($ret)] .= ';';
		}
		else {
			$lineStr .= ' ' . $value;
			$ret[] = $lineStr . ';';
		}

		return $ret;
	}

	public function renderParams(PhpFunction $function, PhpParam ...$params): array|string
	{
		$docBlock = $function->getComment() ?? new PhpDocComment();
		$parameterStrings = [];
		foreach ($params as $param) {
			if ($param->getType()->needDockBlockTypeHint()) {
				$docBlock->addParam(PhpDocElementFactory::getParam($param->getType(), $param->getName()));
			}
			$parameterStrings[] = $this->renderParam($param);
		}

		if (count($params) > 2) {
			for ($i = 0, $l = count($parameterStrings) - 1; $i < $l; $i++) {
				$parameterStrings[$i] .= ',';
			}
			return $parameterStrings;
		}
		else {
			return implode(', ', $parameterStrings);
		}
	}

	public function renderParam(PhpParam $param): string
	{
		$ret = '';
		if ($param->getType()->getTypeHint()) {
			$ret .= $param->getType()->getTypeHint();
		}
		$ret .= ' $' . $param->getName();
		if ($param->getValue() !== PhpParam::NO_VALUE) {
			$ret .= ' = ' . FormatValue::format($param->getValue());
		}

		return trim($ret);
	}

	public function renderComment(PhpDocComment $comment): array
	{
		$returnArray = ['/**'];

		$description = '';
		if ($comment->getDescription()) {
			$preDescription = trim($comment->getDescription());
			$lines = explode(PHP_EOL, $preDescription);
			foreach ($lines as $line) {
				$returnArray[] = ' ' . trim('* ' . $line);
				$description .= trim('* ' . $line) . PHP_EOL;
			}
		}

		if ($comment->getVar() !== null) {
			if (!$description) {
				return ["/** @var {$comment->getVar()->getDataType()->getDocBlockTypeHint()} */"];
			}
			$returnArray[] = ' *';
			$returnArray[] = rtrim($comment->getVar()->getSource(), PHP_EOL);
		}

		if ($description) {
			$returnArray[] = ' *';
		}

		$haveTags = false;

		if (count($comment->getParams()) > 0) {
			$haveTags = true;
			foreach ($comment->getParams() as $param) {
				$returnArray[] = rtrim($param->getSource(), PHP_EOL);
			}
		}
		if (count($comment->getThrows()) > 0) {
			$haveTags = true;
			foreach ($comment->getThrows() as $throws) {
				$returnArray[] = rtrim($throws->getSource(), PHP_EOL);
			}
		}
		if ($comment->getAuthor() !== null) {
			$haveTags = true;
			$returnArray[] = rtrim($comment->getAuthor()->getSource(), PHP_EOL);
		}
		if ($comment->getReturn() !== null) {
			$haveTags = true;
			$returnArray[] = rtrim($comment->getReturn()->getSource(), PHP_EOL);
		}
		foreach ($comment->getMethods() as $method) {
			$haveTags = true;
			$returnArray[] = rtrim($method->getSource(), PHP_EOL);
		}

		if (!$haveTags) {
			array_pop($returnArray);
		}
		if ($returnArray) {
			$returnArray[] = ' */';
		}

		return $returnArray;
	}

	public function renderFunctionSignature(PhpFunction $function): array
	{
		$isAbstract = false;
		$line = [];
		if ($function instanceof PhpMethod) {
			$isAbstract = $function->getParent() instanceof PhpInterface;
			if ($function->isAbstract()) {
				$line[] = 'abstract';
				$isAbstract = true;
			}
			if ($function->isFinal()) {
				$line[] = 'final';
			}
			$line[] = $function->getAccess();
			if ($function->isStatic()) {
				$line[] = 'static';
			}
		}

		$line[] = 'function';
		$line[] = $function->getIdentifier()->toString();
		$lineStr = implode(' ', $line);
		$paramSource = $this->renderParams($function, ...$function->getParams());
		$ret = FlattenSource::applySourceOn($this->renderComment($function->getComment()), []);

		$singleLine = false;
		if (is_string($paramSource)) {
			$lineStr .= '(' . $paramSource . ')';
			$ret[] = $lineStr;
			$singleLine = true;
		}
		else {
			$lineStr .= '(';
			$ret[] = $lineStr;
			$ret[] = $paramSource;
			$ret[] = ')';
		}

		if ($function->getReturnType()->getTypeHint()) {
			$ret[array_key_last($ret)] .= ': ' . $function->getReturnType()->getTypeHint();
		}

		if ($isAbstract) {
			$ret[array_key_last($ret)] .= ';';
		}
		elseif ($singleLine) {
			$ret[] = '{';
		}
		else {
			$ret[array_key_last($ret)] .= ' {';
		}

		return $ret;
	}

	public function renderObjectBody(PhpTrait|PhpClass|PhpInterface $obj): array
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

		$variables = $obj->getVariables();
		if (count($variables)) {
			if ($addNewLine) {
				$classBody[] = '';
			}
			foreach ($variables as $identifier) {
				$source = $this->renderVariable($variables[$identifier]);
				if ($source !== null) {
					$classBody[] = $source;
				}
			}
			$addNewLine = true;
		}

		$methods = $obj->getMethods();
		if (count($methods)) {
			if ($addNewLine) {
				$classBody[] = '';
			}
			foreach ($methods as $identifier) {
				$classBody[] = $this->renderMethod($methods[$identifier]);
				$classBody[] = '';
			}
			array_pop($classBody);
		}
		return $classBody;
	}
}
