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

	public function testVarBlock()
	{
		$comment = PhpDocComment::var(Type::fromString('string'));
		$renderer = new Php7Renderer();

		$this->assertSame(['/** @var string */'], $renderer->renderComment($comment));
	}

	public function testWithDescription()
	{
		$comment = new PhpDocComment('Test Description');
		$comment->setParams(PhpDocElementFactory::getParam('int', '$param', 'test desc'));
		$comment->setAuthor(PhpDocElementFactory::getAuthor('test', 'test@stefna.is'));

		$renderer = new Php7Renderer();
		$this->assertSourceResult($renderer->renderComment($comment), 'PhpDocCommentTest.' . __FUNCTION__);
	}

	public function testWithoutDescription()
	{
		$comment = new PhpDocComment();
		$comment->setParams(PhpDocElementFactory::getParam('int', '$param', 'test desc'));
		$comment->setAuthor(PhpDocElementFactory::getAuthor('test', 'test@stefna.is'));

		$renderer = new Php7Renderer();
		$this->assertSourceResult($renderer->renderComment($comment), 'PhpDocCommentTest.' . __FUNCTION__);
	}

	public function testOnlyDescription()
	{
		$comment = new PhpDocComment('test');

		$renderer = new Php7Renderer();
		$this->assertSourceResult($renderer->renderComment($comment), 'PhpDocCommentTest.' . __FUNCTION__);
	}

	public function testEmpty()
	{
		$comment = new PhpDocComment();
		$renderer = new Php7Renderer();

		$this->assertCount(0, $renderer->renderComment($comment));
	}

	public function testDeprecation()
	{
		$deprecated = PhpDocElementFactory::getDeprecated('use X instead');
		$comment = new PhpDocComment();
		$comment->addField($deprecated);
		$renderer = new Php7Renderer();

		$this->assertSourceResult($renderer->renderComment($comment), 'PhpDocCommentTest.' . __FUNCTION__);
	}

	public function testLicense()
	{
		$licence = PhpDocElementFactory::getLicence('https://github.com/stefnadev/log/blob/develop/LICENSE.md MIT Licence');
		$comment = new PhpDocComment();
		$comment->setLicence($licence);
		$renderer = new Php7Renderer();

		$this->assertSourceResult($renderer->renderComment($comment), 'PhpDocCommentTest.' . __FUNCTION__);
	}

	public function testCheckForParamInCommentAndRemove()
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
