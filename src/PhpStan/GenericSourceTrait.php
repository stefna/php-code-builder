<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\PhpStan;

use Stefna\PhpCodeBuilder\ValueObject\Identifier;

trait GenericSourceTrait
{
	private function generateGenericSource(): string
	{
		if (!$this->templateFields) {
			return '';
		}

		$fields = [];
		foreach ($this->templateFields as $field) {
			$name = $field->getName();
			if ($field instanceof Identifier) {
				if ($field->getAlias()) {
					$name = $field->getAlias();
				}
				if ($field->isGeneric()) {
					$name .= '<' . $field->getGenericIdentifier()?->toString() . '>';
				}
			}
			$fields[] = $name;
		}
		return '<' . implode(', ', $fields) . '>';
	}
}
