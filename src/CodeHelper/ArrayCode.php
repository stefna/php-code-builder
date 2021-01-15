<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\CodeHelper;

use Stefna\PhpCodeBuilder\FormatValue;
use Stefna\PhpCodeBuilder\Indent;

final class ArrayCode implements CodeInterface
{
	private $data;
	private $indentFirstLine = true;

	public function __construct(array $data)
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
}
