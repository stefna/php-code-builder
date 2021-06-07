<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Renderer;

use Stefna\PhpCodeBuilder\PhpClass;
use Stefna\PhpCodeBuilder\PhpConstant;
use Stefna\PhpCodeBuilder\PhpDocComment;
use Stefna\PhpCodeBuilder\PhpFile;
use Stefna\PhpCodeBuilder\PhpFunction;
use Stefna\PhpCodeBuilder\PhpInterface;
use Stefna\PhpCodeBuilder\PhpMethod;
use Stefna\PhpCodeBuilder\PhpParam;
use Stefna\PhpCodeBuilder\PhpTrait;
use Stefna\PhpCodeBuilder\PhpVariable;

interface FullRendererInterface extends RenderInterface
{
	/**
	 * @return array<int, string|array<int, string>>
	 */
	public function renderFile(PhpFile $file): array;

	/**
	 * @return array<int, string|array<int, string>>
	 */
	public function renderClass(PhpClass $class): array;

	/**
	 * @return array<int, string|array<int, string>>
	 */
	public function renderInterface(PhpInterface $interface): array;

	/**
	 * @return array<int, string|array<int, string>>
	 */
	public function renderTrait(PhpTrait $trait): array;

	/**
	 * @return array<int, string|array<int, string>>
	 */
	public function renderMethod(PhpMethod $method): array;

	/**
	 * @return array<int, string|array<int, string>>
	 */
	public function renderFunction(PhpFunction $function): array;

	/**
	 * @return array<int, string|array<int, string>>|string
	 */
	public function renderParams(PhpFunction $function, PhpParam ...$params): array|string;

	/**
	 * @return array<int, string|array<int, string>>
	 */
	public function renderConstant(PhpConstant $constant): array;

	/**
	 * @return array<int, string|array<int, string>>|null
	 */
	public function renderVariable(PhpVariable $variable): array|null;

	/**
	 * @return array<int, string|array<int, string>>
	 */
	public function renderComment(PhpDocComment $comment): array;

	/**
	 * @return array<int, string|array<int, string>>
	 */
	public function renderFunctionSignature(PhpFunction $function): array;

	/**
	 * @return array<int, string|array<int, string>>
	 */
	public function renderObjectBody(PhpTrait|PhpClass|PhpInterface $obj): array;
}
