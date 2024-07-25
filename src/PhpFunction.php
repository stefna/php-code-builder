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
class PhpFunction
{
	/** @var PhpParam[] */
	protected array $params = [];
	private Identifier $identifier;

	/**
	 * @param PhpParam[] $params
	 * @param array<int, string|string[]>|CodeInterface $body
	 */
	public function __construct(
		Identifier|string $identifier,
		array $params,
		protected array|CodeInterface $body,
		protected ?Type $returnTypeHint = null,
		protected ?PhpDocComment $comment = null,
	) {
		$this->identifier = Identifier::fromUnknown($identifier);
		foreach ($params as $param) {
			$this->addParam($param);
		}
	}

	/**
	 * @param array<int, string|string[]>|CodeInterface $source
	 */
	public function setBody(array|CodeInterface $source): void
	{
		$this->body = $source;
	}

	public function setComment(PhpDocComment $comment): void
	{
		$this->comment = $comment;
	}

	public function getComment(): ?PhpDocComment
	{
		return $this->comment;
	}

	public function getReturnType(): Type
	{
		return $this->returnTypeHint ?? Type::empty();
	}

	public function setReturnTypeHint(Type $returnTypeHint): static
	{
		$this->returnTypeHint = $returnTypeHint;
		return $this;
	}

	/**
	 * @return PhpParam[]
	 */
	public function getParams(): array
	{
		return $this->params;
	}

	public function getParam(string $name): ?PhpParam
	{
		return $this->params[$name] ?? null;
	}

	public function addParam(PhpParam $param): static
	{
		$param->setParent($this);
		$this->params[$param->getName()] = $param;
		return $this;
	}

	public function removeParam(string $name): static
	{
		if (isset($this->params[$name])) {
			unset($this->params[$name]);
		}

		return $this;
	}

	public function __clone()
	{
		$params = [];
		foreach ($this->params as $paramName => $param) {
			$params[$paramName] = clone $param;
			$params[$paramName]->setParent($this);
		}

		$this->params = $params;
	}

	public function getIdentifier(): Identifier
	{
		return $this->identifier;
	}

	public function setIdentifier(Identifier $identifier): static
	{
		$this->identifier = $identifier;
		return $this;
	}

	/**
	 * @return array<int, string|string[]>|CodeInterface
	 */
	public function getBody(): array|CodeInterface
	{
		return $this->body;
	}
}
