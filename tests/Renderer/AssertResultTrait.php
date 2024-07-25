<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\Renderer;

use Stefna\PhpCodeBuilder\FlattenSource;

trait AssertResultTrait
{
	public function assertSourceResult(array|string $source, string $result): void
	{
		$file = __DIR__ . '/resources/' . $result . '.result';
		$flattenSource = is_array($source) ? FlattenSource::source($source) : $source;
		if (!file_exists($file)) {
			echo $flattenSource . PHP_EOL;
		}
		$this->assertStringEqualsFile($file, $flattenSource);
	}
}
