<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\Renderer;

use Stefna\PhpCodeBuilder\FlattenSource;

trait AssertResultTrait
{
	public function assertSourceResult(array $source, string $result)
	{
		$file = __DIR__ . '/resources/' . $result .'.result';
		$flattenSource = FlattenSource::source($source);
		if (!file_exists($file)) {
			echo $flattenSource . PHP_EOL;
		}
		$this->assertStringEqualsFile($file, $flattenSource);
	}
}
