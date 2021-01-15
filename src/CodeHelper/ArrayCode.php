<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Exception;
use Stefna\PhpCodeBuilder\FormatValue;
use Stefna\PhpCodeBuilder\Indent;
use Traversable;

final class ArrayCode implements CodeInterface, \ArrayAccess, \IteratorAggregate
{
	private $data;
	private $indentFirstLine = true;

	public function __construct(array $data = [])
	{
		$this->data = $data;
	}

	public function getSource(int $currentIndent = 0): string
	{
		$return = [];
		$return[] = ($this->indentFirstLine ? Indent::indent($currentIndent) : '') . '[';
		$isAssoc = false;
		foreach ($this->data as $key => $value) {
			if (!$isAssoc && is_string($key)) {
				$isAssoc = true;
				break;
			}
		}
		foreach ($this->data as $key => $value) {
			if (is_array($value)) {
				$tmpValue = new ArrayCode($value);
				$tmpValue->indentFirstLine = false;
				$formattedValue = $tmpValue->getSource($currentIndent + 1);
			}
			else {
				$formattedValue = FormatValue::format($value);
			}
			if ($isAssoc) {
				$return[] = sprintf(
					"%s'%s' => %s,",
					Indent::indent($currentIndent + 1),
					$key,
					$formattedValue
				);
			}
			else {
				$return[] = sprintf(
					"%s%s,",
					Indent::indent($currentIndent + 1),
					$formattedValue
				);
			}
		}
		$return[] = Indent::indent($currentIndent) . ']';
		return implode(PHP_EOL, $return);
	}

	public function offsetExists($offset)
	{
		return array_key_exists($offset, $this->data);
	}

	public function offsetGet($offset)
	{
		return $this->data[$offset] ?? null;
	}

	public function offsetSet($offset, $value)
	{
		if (is_string($offset)) {
			$this->data[$offset] = $value;
		}
		else {
			$this->data[] = $value;
		}
	}

	public function offsetUnset($offset)
	{
		// TODO: Implement offsetUnset() method.
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->data);
	}
}
