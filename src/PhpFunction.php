<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

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

	/** @var string */
	private $source;

	/** @var PhpDocComment */
	private $comment;

	/** @var string|null */
	private $returnTypeHint;

	private $isLongLine = false;

	public function __construct(
		string $identifier,
		array $params,
		string $source,
		PhpDocComment $comment = null,
		?string $returnTypeHint = null
	) {
		$this->access = '';
		$this->identifier = $identifier;
		$this->source = $source;
		$this->comment = $comment;
		$this->returnTypeHint = $returnTypeHint;
		foreach ($params as $name => $type) {
			if ($type instanceof PhpParam) {
				$this->addParam($type);
			}
			elseif (is_string($name)) {
				$this->addParam(new PhpParam($type, $name));
			}
			else {
				$this->addParam(new PhpParam('', $type));
			}
		}
	}

	/**
	 * Returns the complete source code for the function
	 *
	 * @return string
	 */
	public function getSource(): string
	{
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
		$returnTypeHint = $this->returnTypeHint ? ': ' . $this->returnTypeHint : '';
		$functionNameDefinition = $this->formatFunctionAccessors();
		$functionNameDefinition .= 'function ';
		$functionNameDefinition .= $this->identifier;

		$paramStr = self::buildParametersString(strlen($functionNameDefinition), $this->params);

		$functionNameDefinition .= "({$paramStr})";

		if ($returnTypeHint) {
			$functionNameDefinition .= $returnTypeHint;
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
		$ret .= $this->getSourceRow('}');

		return $ret;
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
