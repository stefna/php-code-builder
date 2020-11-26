<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\ValueObject\Type;

/**
 * Class that represents the source code for a function in php
 *
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @author Andreas Sundqvist <andreas@stefna.is>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class PhpFunction extends PhpElement
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
	private $isLongLine = false;

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
		$this->identifier = $identifier;
		$this->source = $source;
		$this->returnTypeHint = $returnTypeHint ?? Type::empty();
		$this->comment = $comment;
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
	public function getSource(): string
	{
		if (!$this->comment && $this->returnTypeHint->needDockBlockTypeHint()) {
			$this->comment = new PhpDocComment();
			$this->comment->setReturn(PhpDocElementFactory::getReturn($this->returnTypeHint->getDocBlockTypeHint()));
		}

		$ret = '';
		if ($this->comment !== null) {
			$ret .= $this->getSourceRow($this->comment->getSource());
		}

		$ret .= $this->formatFunctionDefinition();

		$ret = $this->formatFunctionBody($ret);

		return $ret;
	}

	public function addParam(PhpParam $param): self
	{
		$this->params[$param->getName()] = $param;

		return $this;
	}

	protected function formatFunctionAccessors(): string
	{
		return '';
	}

	protected function formatFunctionDefinition(): string
	{
		$functionNameDefinition = $this->formatFunctionAccessors();
		$functionNameDefinition .= 'function ';
		$functionNameDefinition .= $this->identifier;

		$paramStr = self::buildParametersString(strlen($functionNameDefinition), $this->params);

		$functionNameDefinition .= "({$paramStr})";

		$typeHint = $this->returnTypeHint->getTypeHint();
		if ($typeHint) {
			$functionNameDefinition .= ': ' . $typeHint;
		}

		if (strpos($paramStr, PHP_EOL) !== false) {
			$this->isLongLine = true;
			$functionNameDefinition .= ' {';
		}

		return $this->getSourceRow($functionNameDefinition);
	}

	protected function formatFunctionBody(string $ret): string
	{
		if (!$this->isLongLine) {
			$ret .= $this->getSourceRow('{');
		}

		$this->indentionLevel++;
		$ret .= $this->getSourceRow($this->source);
		$this->indentionLevel--;
		return $ret . $this->getSourceRow('}');
	}

	/**
	 * @param int $baseLength
	 * @param PhpParam[] $parameters
	 * @param int $indent
	 * @return string
	 */
	public static function buildParametersString(
		int $baseLength,
		array $parameters,
		int $indent = 1
	): string {
		$parameterStrings = [];
		foreach ($parameters as $param) {
			$parameterStrings[] = $param->getSource();
		}

		$str = implode(', ', $parameterStrings);
		if (strlen($str) + $baseLength > 100) {
			$str = PHP_EOL . Indent::indent($indent);
			$str .= implode(',' . PHP_EOL . Indent::indent($indent), $parameterStrings) . PHP_EOL;
			if ($indent > 1) {
				$str .= Indent::indent($indent - 1);
			}
		}

		return $str;
	}
}
