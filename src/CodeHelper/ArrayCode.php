<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Exception;
use Stefna\PhpCodeBuilder\FlattenSource;
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
		$indent = ($this->indentFirstLine ? Indent::indent($currentIndent) : '');
		return $indent . FlattenSource::source($this->getSourceArray($currentIndent));
	}

	public function getSourceArray(int $currentIndent = 0): array
	{
		$return = [];
		$return[] = '[';
		$isAssoc = false;
		foreach ($this->data as $key => $value) {
			if (!$isAssoc && is_string($key)) {
				$isAssoc = true;
				break;
			}
		}
		$rows = [];
		foreach ($this->data as $key => $value) {
			if (is_array($value)) {
				$tmpValue = new ArrayCode($value);
				$tmpValue->indentFirstLine = false;
				if ($isAssoc) {
					$rows[] = sprintf("'%s' => [", $key);
				}
				else {
					$rows[] = '[';
				}
				$formattedValue = $tmpValue->getSourceArray();
				array_shift($formattedValue);
				$lastValue = array_pop($formattedValue);
				foreach ($formattedValue as $z) {
					$rows[] = $z;
				}
				$rows[] = $lastValue .',';
				continue;
			}
			else {
				$formattedValue = FormatValue::format($value);
			}
			if ($isAssoc) {
				$rows[] = sprintf(
					"'%s' => %s,",
					$key,
					$formattedValue
				);
			}
			else {
				$rows[] = sprintf(
					"%s,",
					$formattedValue
				);
			}
		}
		$return[] = $rows;
		$return[] = ']';

		return $return;
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

	/**
	 * @param bool $indentFirstLine
	 */
	public function setIndentFirstLine(bool $indentFirstLine): void
	{
		$this->indentFirstLine = $indentFirstLine;
	}
}