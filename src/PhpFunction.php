<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\CodeHelper\CodeInterface;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;
use Stefna\PhpCodeBuilder\ValueObject\Type;

/**
 * Class that represents the source code for a function in php
 *
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @author Andreas Sundqvist <andreas@stefna.is>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class PhpFunction extends PhpElement implements CodeInterface
{
	/** @var PhpParam[] */
	private $params = [];
	/** @var string|array */
	private $source;
	/** @var PhpDocComment */
	protected $comment;
	/** @var Type */
	protected $returnTypeHint;
	/** @var bool */
	protected $renderBody = true;

	/**
	 * @param string $identifier
	 * @param array $params
	 * @param array|string $source
	 * @param Type $returnTypeHint
	 * @param PhpDocComment|null $comment
	 */
	public function __construct(
		string $identifier,
		array $params,
		$source,
		Type $returnTypeHint = null,
		PhpDocComment $comment = null
	) {
		$this->access = '';
		$this->identifier = Identifier::simple($identifier);
		$this->source = $source;
		$this->returnTypeHint = $returnTypeHint ?? Type::empty();
		$this->comment = $comment ?? new PhpDocComment();
		foreach ($params as $name => $type) {
			if ($type instanceof PhpParam) {
				$this->addParam($type);
			}
			elseif (is_string($name)) {
				$type = is_string($type) ? Type::fromString($type) : $type;
				$this->addParam(new PhpParam($name, $type));
			}
			else {
				$this->addParam(new PhpParam($type, Type::empty()));
			}
		}
	}

	/**
	 * Set function body
	 *
	 * @param array|string $source
	 */
	public function setSource($source): void
	{
		$this->source = $source;
	}

	/**
	 * Set function docblock
	 *
	 * @param PhpDocComment $comment
	 */
	public function setComment(PhpDocComment $comment): void
	{
		$this->comment = $comment;
	}

	public function getComment(): PhpDocComment
	{
		return $this->comment;
	}

	public function getReturnType(): Type
	{
		return $this->returnTypeHint;
	}

	public function setReturnTypeHint(Type $returnTypeHint): void
	{
		$this->returnTypeHint = $returnTypeHint;
	}

	/**
	 * @return PhpParam[]
	 */
	public function getParams(): array
	{
		return $this->params;
	}

	/**
	 * Returns the complete source code for the function
	 *
	 * @return string
	 */
	public function getSource(int $currentIndent = 0): string
	{
		$lines = $this->getSourceArray();
		if (!$lines) {
			return '';
		}

		return FlattenSource::source($lines);
	}

	public function addParam(PhpParam $param): self
	{
		$this->params[$param->getName()] = $param;

		return $this;
	}

	public function removeParam(string $name): self
	{
		if (isset($this->params[$name])) {
			unset($this->params[$name]);
		}

		return $this;
	}

	protected function formatFunctionAccessors(): string
	{
		return '';
	}

	private function formatFunctionName(): string
	{
		$functionNameDefinition = $this->formatFunctionAccessors();
		$functionNameDefinition .= 'function ';
		$functionNameDefinition .= $this->identifier->toString();
		return $functionNameDefinition;
	}

	private function buildParamsArray(int $baseLength, array $parameters): array
	{
		$parameterStrings = [];
		foreach ($parameters as $param) {
			$parameterStrings[] = $param->getSource();
		}

		$str = implode(', ', $parameterStrings);
		if (strlen($str) + $baseLength > 100) {
			for ($i = 0, $l = count($parameterStrings) - 1; $i < $l; $i++) {
				$parameterStrings[$i] .= ',';
			}
			return $parameterStrings;
		}
		return [$str];
	}

	public function getSourceArray(int $currentIndent = 0): array
	{
		$comment = $this->comment;
		if ($this->returnTypeHint->needDockBlockTypeHint()) {
			$comment->setReturn(PhpDocElementFactory::getReturn($this->returnTypeHint->getDocBlockTypeHint()));
		}

		foreach ($this->params as $param) {
			if ($param->getType()->needDockBlockTypeHint()) {
				$comment->addParam(PhpDocElementFactory::getParam($param->getType(), $param->getName()));
			}
		}

		$ret = [];
		foreach ($comment->getSourceArray() as $line) {
			$ret[] = $line;
		}

		$functionName = $this->formatFunctionName();
		$parameters = $this->buildParamsArray(strlen($functionName), $this->params);
		$isAbstract = !$this->renderBody;
		if (count($parameters) === 1) {
			$declaration = $functionName . '(' . $parameters[0] .')';
			$typeHint = $this->returnTypeHint->getTypeHint();
			if ($typeHint) {
				$declaration .= ': ' . $typeHint;
			}
			$ret[] = $declaration . ($isAbstract ? ';' : '');
			if (!$isAbstract) {
				$ret[] = '{';
			}
		}
		else {
			$ret[] = $functionName . '(';
			$ret[] = $parameters;
			$endDeclaration = ')';
			$typeHint = $this->returnTypeHint->getTypeHint();
			if ($typeHint) {
				$endDeclaration .= ': ' . $typeHint . ($isAbstract ? ';' : ' {');
			}
			elseif ($isAbstract) {
				$endDeclaration .= ';';
			}
			$ret[] = $endDeclaration;
		}
		if ($isAbstract) {
			return $ret;
		}

		if (is_array($this->source) || $this->source instanceof CodeInterface) {
			$ret[] = $this->source;
		}
		else {
			$ret[] = [$this->source];
		}
		$ret[] = '}';

		return $ret;
	}
}
