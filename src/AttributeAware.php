<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

trait AttributeAware
{
	/** @var list<PhpAttribute> */
	private array $attributes = [];

	public function addAttribute(PhpAttribute $attribute): static
	{
		$this->attributes[] = $attribute;
		return $this;
	}

	public function removeAttribute(PhpAttribute $attribute): static
	{
		foreach ($this->attributes as $index => $attr) {
			if ($attr === $attribute) {
				unset($this->attributes[$index]);
			}
		}
		return $this;
	}

	/**
	 * @return list<PhpAttribute>
	 */
	public function getAttributes(): array
	{
		return $this->attributes;
	}
}
