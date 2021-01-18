<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\CodeHelper\CodeInterface;
use Stefna\PhpCodeBuilder\ValueObject\Type;

/**
 * Class that represents the source code for a phpdoc comment in php
 *
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @author Andreas Sundqvist <andreas@stefna.is>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class PhpDocComment implements CodeInterface
{
	/** @var PhpDocElement */
	private $var;

	/** @var PhpDocElement[] */
	private $params;

	/** @var PhpDocElement */
	private $return;

	/** @var PhpDocElement */
	private $author;

	/** @var PhpDocElement[] */
	private $throws;

	/** @var string */
	private $description;

	/** @var PhpDocElement[] */
	private $methods = [];

	/**
	 * Create single line var docblock
	 *
	 * @param string $type
	 * @return PhpDocComment
	 */
	public static function var(Type $type): self
	{
		$doc = new static();
		$doc->setVar(PhpDocElementFactory::getVar($type));

		return $doc;
	}

	public function __construct(string $description = '')
	{
		$this->description = $description;
		$this->params = [];
		$this->throws = [];
	}

	/**
	 * Returns the generated source
	 *
	 * @return string The sourcecode of the comment
	 */
	public function getSource(int $currentIndent = 0): string
	{
		$lines = $this->getSourceArray();
		if (!$lines) {
			return '';
		}

		return implode(PHP_EOL, $lines) . PHP_EOL;
	}

	public function addMethod(PhpDocElement $method)
	{
		$this->methods[] = $method;
	}

	public function setVar(PhpDocElement $var): self
	{
		$this->var = $var;
		return $this;
	}

	public function setAuthor(PhpDocElement $author): self
	{
		$this->author = $author;
		return $this;
	}

	public function setLicence(PhpDocElement $licence): self
	{
		return $this;
	}

	public function setReturn(PhpDocElement $return): self
	{
		$this->return = $return;
		return $this;
	}

	public function addParam(PhpDocElement $param): self
	{
		$this->params[$param->getHashCode()] = $param;
		return $this;
	}

	public function setParams(PhpDocElement ...$params): self
	{
		$this->params = $params;
		return $this;
	}

	public function addThrows(PhpDocElement $throws): self
	{
		$this->throws[] = $throws;
		return $this;
	}

	public function setDescription(string $description): self
	{
		$this->description = $description;
		return $this;
	}

	public function getSourceArray(int $currentIndent = 0): array
	{
		$returnArray = ['/**'];

		$description = '';
		if ($this->description) {
			$preDescription = trim($this->description);
			$lines = explode(PHP_EOL, $preDescription);
			foreach ($lines as $line) {
				$returnArray[] = ' ' . trim('* ' . $line);
				$description .= trim('* ' . $line) . PHP_EOL;
			}
		}

		if ($this->var !== null) {
			if (!$description) {
				return ["/** @var {$this->var->getDataType()->getDocBlockTypeHint()} */"];
			}
			$returnArray[] = rtrim($this->var->getSource(), PHP_EOL);
		}

		if ($description) {
			$returnArray[] = ' *';
		}

		$haveTags = false;

		if (count($this->params) > 0) {
			$haveTags = true;
			foreach ($this->params as $param) {
				$returnArray[] = rtrim($param->getSource(), PHP_EOL);
			}
		}
		if (count($this->throws) > 0) {
			$haveTags = true;
			foreach ($this->throws as $throws) {
				$returnArray[] = rtrim($throws->getSource(), PHP_EOL);
			}
		}
		if ($this->author !== null) {
			$haveTags = true;
			$returnArray[] = rtrim($this->author->getSource(), PHP_EOL);
		}
		if ($this->return !== null) {
			$haveTags = true;
			$returnArray[] = rtrim($this->return->getSource(), PHP_EOL);
		}
		foreach ($this->methods as $method) {
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
}
