<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\Renderer;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\PhpDocComment;
use Stefna\PhpCodeBuilder\PhpDocElementFactory;
use Stefna\PhpCodeBuilder\Renderer\Php7Renderer;
use Stefna\PhpCodeBuilder\ValueObject\Type;

final class PhpDocCommentTest extends TestCase
{
	use AssertResultTrait;

	public function testVarBlock(): void
	{
		$comment = PhpDocComment::var(Type::fromString('string'));
		$renderer = new Php7Renderer();

		$this->assertSame(['/** @var string */'], $renderer->renderComment($comment));
	}

	public function testWithDescription(): void
	{
		$comment = new PhpDocComment('Test Description');
		$comment->setParams(PhpDocElementFactory::getParam('int', '$param', 'test desc'));
		$comment->setAuthor(PhpDocElementFactory::getAuthor('test', 'test@stefna.is'));

		$renderer = new Php7Renderer();
		$this->assertSourceResult($renderer->renderComment($comment), 'PhpDocCommentTest.' . __FUNCTION__);
	}

	public function testWithoutDescription(): void
	{
		$comment = new PhpDocComment();
		$comment->setParams(PhpDocElementFactory::getParam('int', '$param', 'test desc'));
		$comment->setAuthor(PhpDocElementFactory::getAuthor('test', 'test@stefna.is'));

		$renderer = new Php7Renderer();
		$this->assertSourceResult($renderer->renderComment($comment), 'PhpDocCommentTest.' . __FUNCTION__);
	}

	public function testOnlyDescription(): void
	{
		$comment = new PhpDocComment('test');

		$renderer = new Php7Renderer();
		$this->assertSourceResult($renderer->renderComment($comment), 'PhpDocCommentTest.' . __FUNCTION__);
	}

	public function testEmpty(): void
	{
		$comment = new PhpDocComment();
		$renderer = new Php7Renderer();

		$this->assertCount(0, $renderer->renderComment($comment));
	}

	public function testDeprecation(): void
	{
		$deprecated = PhpDocElementFactory::getDeprecated('use X instead');
		$comment = new PhpDocComment();
		$comment->addField($deprecated);
		$renderer = new Php7Renderer();

		$this->assertSourceResult($renderer->renderComment($comment), 'PhpDocCommentTest.' . __FUNCTION__);
	}

	public function testLicense(): void
	{
		$info = 'https://github.com/stefnadev/log/blob/develop/LICENSE.md MIT Licence';
		$licence = PhpDocElementFactory::getLicence($info);
		$comment = new PhpDocComment();
		$comment->setLicence($licence);
		$renderer = new Php7Renderer();

		$this->assertSourceResult($renderer->renderComment($comment), 'PhpDocCommentTest.' . __FUNCTION__);
	}

	public function testGenerated(): void
	{
		$licence = PhpDocElementFactory::getGenerated('From X');
		$comment = new PhpDocComment();
		$comment->setLicence($licence);
		$renderer = new Php7Renderer();

		$this->assertSame([
			'/**',
			' * @generated From X',
			' */',
		], $renderer->renderComment($comment));
	}

	public function testCheckForParamInCommentAndRemove(): void
	{
		$comment = new PhpDocComment('Test Description');
		$comment->setParams(
			PhpDocElementFactory::getParam('int', '$param', 'test desc'),
			PhpDocElementFactory::getParam('int', '$param2', 'test desc'),
			PhpDocElementFactory::getParam('int', '$param3', 'test desc'),
		);

		$this->assertTrue($comment->hasParamWithName('param3'));

		$comment->removeParamWithName('param3');
		$this->assertFalse($comment->hasParamWithName('param3'));
	}
}
