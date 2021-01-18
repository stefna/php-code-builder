<?php declare(strict_types=1);

namespace CodeHelper;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\PhpDocComment;
use Stefna\PhpCodeBuilder\PhpDocElementFactory;
use Stefna\PhpCodeBuilder\ValueObject\Type;

final class PhpDocCommentTest extends TestCase
{
	public function testVarBlock()
	{
		$comment = PhpDocComment::var(Type::fromString('string'));

		$this->assertSame(trim($comment->getSource()), $comment->getSourceArray()[0]);
	}
	public function testWithDescription()
	{
		$comment = new PhpDocComment('Test Description');
		$comment->setParams(PhpDocElementFactory::getParam('int', '$param', 'test desc'));
		$comment->setAuthor(PhpDocElementFactory::getAuthor('test', 'test@stefna.is'));

		$this->assertSame('/**
 * Test Description
 *
 * @param int $param test desc
 * @author test <test@stefna.is>
 */
', $comment->getSource());
		$this->assertSame([
			'/**',
			' * Test Description',
			' *',
			' * @param int $param test desc',
			' * @author test <test@stefna.is>',
			' */',
		], $comment->getSourceArray());
	}

	public function testWithoutDescription()
	{
		$comment = new PhpDocComment();
		$comment->setParams(PhpDocElementFactory::getParam('int', '$param', 'test desc'));
		$comment->setAuthor(PhpDocElementFactory::getAuthor('test', 'test@stefna.is'));

		$this->assertSame('/**
 * @param int $param test desc
 * @author test <test@stefna.is>
 */
', $comment->getSource());
		$this->assertSame([
			'/**',
			' * @param int $param test desc',
			' * @author test <test@stefna.is>',
			' */',
		], $comment->getSourceArray());
	}

	public function testOnlyDescription()
	{
		$comment = new PhpDocComment('test');

		$this->assertSame('/**
 * test
 */
', $comment->getSource());
		$this->assertSame([
			'/**',
			' * test',
			' */',
		], $comment->getSourceArray());
	}

	public function testEmpty()
	{
		$comment = new PhpDocComment();

		$this->assertSame('', $comment->getSource());
		$this->assertCount(0, $comment->getSourceArray());
	}
}
