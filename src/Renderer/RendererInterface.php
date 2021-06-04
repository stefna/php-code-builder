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

interface RendererInterface
{
	public function renderFile(PhpFile $file): array;

	public function renderClass(PhpClass $class): array;

	public function renderInterface(PhpInterface $interface): array;

	public function renderTrait(PhpTrait $trait): array;

	public function renderMethod(PhpMethod $method): array;

	public function renderFunction(PhpFunction $function): array;

	public function renderParams(PhpFunction $function, PhpParam ...$params): array|string;

	public function renderConstant(PhpConstant $constant): array;

	public function renderVariable(PhpVariable $variable): array|null;

	public function renderComment(PhpDocComment $comment): array;

	public function renderFunctionSignature(PhpFunction $function): array;

	public function renderObjectBody(PhpTrait|PhpClass|PhpInterface $obj): array;
}
