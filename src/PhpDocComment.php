<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\ValueObject\Type;

/**
 * Class that represents the source code for a phpdoc comment in php
 *
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @author Andreas Sundqvist <andreas@stefna.is>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class PhpDocComment
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
	public function getSource(): string
	{
		$description = '';
		if ($this->description) {
			$preDescription = trim($this->description);
			$lines = explode(PHP_EOL, $preDescription);
			foreach ($lines as $line) {
				$description .= ' ' . trim('* ' . $line) . PHP_EOL;
			}
		}

		$tags = [];
		if ($this->var !== null) {
			if (!$description) {
				return "/** @var {$this->var->getDataType()->getDocBlockTypeHint()} */" . PHP_EOL;
			}
			$tags[] = $this->var->getSource();
		}
		if (count($this->params) > 0) {
			foreach ($this->params as $param) {
				$tags[] = $param->getSource();
			}
		}
		if (count($this->throws) > 0) {
			foreach ($this->throws as $throws) {
				$tags[] = $throws->getSource();
			}
		}
		if ($this->author !== null) {
			$tags[] = $this->author->getSource();
		}
		if ($this->return !== null) {
			$tags[] = $this->return->getSource();
		}
		foreach ($this->methods as $method) {
			$tags[] = $method->getSource();
		}

		if (!empty($description) && !empty($tags)) {
			$description .= ' *' . PHP_EOL;
		}

		$ret = $description . implode($tags);

		if (!empty($ret)) {
			$ret = PHP_EOL . '/**' . PHP_EOL . $ret . ' */' . PHP_EOL;
		}

		return $ret;
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
		$this->params[] = $param;
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
}
