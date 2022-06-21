<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Renderer;

use Stefna\PhpCodeBuilder\PhpClass;
use Stefna\PhpCodeBuilder\PhpConstant;
use Stefna\PhpCodeBuilder\PhpDocComment;
use Stefna\PhpCodeBuilder\PhpEnum;
use Stefna\PhpCodeBuilder\PhpFile;
use Stefna\PhpCodeBuilder\PhpFunction;
use Stefna\PhpCodeBuilder\PhpInterface;
use Stefna\PhpCodeBuilder\PhpMethod;
use Stefna\PhpCodeBuilder\PhpParam;
use Stefna\PhpCodeBuilder\PhpTrait;
use Stefna\PhpCodeBuilder\PhpVariable;

/**
 * @phpstan-import-type SourceArray from RenderInterface
 */
interface FullRendererInterface extends RenderInterface
{
	/**
	 * @phpstan-return SourceArray
	 */
	public function renderFile(PhpFile $file): array;

	/**
	 * @phpstan-return SourceArray
	 */
	public function renderEnum(PhpEnum $enum): array;

	/**
	 * @phpstan-return SourceArray
	 */
	public function renderClass(PhpClass $class): array;

	/**
	 * @phpstan-return SourceArray
	 */
	public function renderInterface(PhpInterface $interface): array;

	/**
	 * @phpstan-return SourceArray
	 */
	public function renderTrait(PhpTrait $trait): array;

	/**
	 * @phpstan-return SourceArray
	 */
	public function renderMethod(PhpMethod $method): array;

	/**
	 * @phpstan-return SourceArray
	 */
	public function renderFunction(PhpFunction $function): array;

	/**
	 * @phpstan-return SourceArray
	 */
	public function renderParams(PhpFunction $function, PhpParam ...$params): array|string;

	/**
	 * @phpstan-return SourceArray
	 */
	public function renderConstant(PhpConstant $constant): array;

	/**
	 * @phpstan-return SourceArray
	 */
	public function renderVariable(PhpVariable $variable): array|null;

	/**
	 * @phpstan-return SourceArray
	 */
	public function renderComment(PhpDocComment $comment): array;

	/**
	 * @phpstan-return SourceArray
	 */
	public function renderFunctionSignature(PhpFunction $function): array;

	/**
	 * @phpstan-return SourceArray
	 */
	public function renderObjectBody(PhpTrait|PhpClass|PhpInterface $obj): array;
}
