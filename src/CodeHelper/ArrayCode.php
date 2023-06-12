<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\FormatValue;

/**
 * @implements \IteratorAggregate<array-key, mixed>
 * @implements \ArrayAccess<array-key, mixed>
 */
final class ArrayCode implements CodeInterface, \ArrayAccess, \IteratorAggregate
{
	/**
	 * @param array<array-key, mixed> $data
	 */
	public function __construct(
		private array $data = [],
	) {}

	/**
	 * @return list<mixed>
	 */
	public function getSourceArray(): array
	{
		if (!$this->data) {
			return ['[]'];
		}

		$return = [];
		$return[] = '[';
		$isAssoc = false;
		foreach ($this->data as $key => $value) {
			if ($isAssoc === false && is_string($key)) {
				$isAssoc = true;
				break;
			}
		}
		$rows = [];
		foreach ($this->data as $key => $value) {
			if (is_array($value)) {
				$tmpValue = new ArrayCode($value);
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

			$formattedValue = FormatValue::format($value);
			if ($value instanceof CodeInterface &&
				is_array($formattedValue) &&
				count($formattedValue) === 1 &&
				is_string($formattedValue[0])
			) {
				$formattedValue = $formattedValue[0];
			}
			if ($isAssoc) {
				if (is_string($formattedValue)) {
					$rows[] = sprintf(
						"'%s' => %s,",
						$key,
						$formattedValue
					);
				}
				elseif (is_array($formattedValue)) {
					$rows[] = sprintf(
						"'%s' => %s",
						$key,
						$formattedValue[0]
					);
					for ($i = 1; $i < count($formattedValue); $i++) {
						$rows[] = $formattedValue[$i];
					}
					$rows[array_key_last($rows)] .= ',';
				}
			}
			elseif (is_string($formattedValue)) {
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

	public function offsetExists($offset): bool
	{
		return array_key_exists($offset, $this->data);
	}

	public function offsetGet($offset): mixed
	{
		return $this->data[$offset] ?? null;
	}

	public function offsetSet(mixed $offset, mixed $value): void
	{
		if (is_string($offset)) {
			$this->data[$offset] = $value;
		}
		else {
			$this->data[] = $value;
		}
	}

	public function offsetUnset(mixed $offset): void
	{
		// TODO: Implement offsetUnset() method.
	}

	/**
	 * @return \ArrayIterator<array-key, mixed>
	 */
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->data);
	}
}
