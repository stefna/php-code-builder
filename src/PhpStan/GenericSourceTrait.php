<?php

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
			if ($field instanceof Identifier && $field->getAlias()) {
				$name = $field->getAlias();
			}
			$fields[] = $name;
		}
		return '<' . implode(', ', $fields) . '>';
	}
}
