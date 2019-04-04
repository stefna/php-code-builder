<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

/**
 * Class that represents the source code for a method in php
 *
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @author Andreas Sundqvist <andreas@stefna.is>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class PhpMethod extends PhpFunction
{
	private $final = false;
	private $static = false;
	private $abstract = false;

	public function __construct(
		string $access,
		string $identifier,
		array $params,
		string $source,
		PhpDocComment $comment = null,
		?string $returnTypeHint = null
	) {
		parent::__construct($identifier, $params, $source, $comment, $returnTypeHint);
		$this->access = $access;
	}

	public function setFinal(): self
	{
		$this->final = true;
		return $this;
	}

	public function setStatic(): self
	{
		$this->static = true;
		return $this;
	}

	public function setAbstract(): self
	{
		$this->abstract = true;
		return $this;
	}

	public function setAccess(string $access): self
	{
		$this->access = $access;
		return $this;
	}

	protected function formatFunctionAccessors(): string
	{
		$ret = '';
		$ret .= $this->abstract ? 'abstract ' : '';
		$ret .= $this->final ? 'final ' : '';
		$ret .= $this->access ? $this->access . ' ' : '';
		$ret .= $this->static ? 'static ' : '';

		return $ret;
	}

	protected function formatFunctionBody(string $ret): string
	{
		if (!$this->abstract) {
			return parent::formatFunctionBody($ret);
		}

		return rtrim(str_replace(' {', '', $ret)) . ';' . PHP_EOL;
	}
}
