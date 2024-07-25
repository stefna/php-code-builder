<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\PhpStan;

use Stefna\PhpCodeBuilder\Contracts\HasIdentifiers;
use Stefna\PhpCodeBuilder\PhpDocElement;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;
use Stefna\PhpCodeBuilder\ValueObject\Type;

final class ExtendsField extends PhpDocElement implements HasIdentifiers
{
	use GenericSourceTrait;

	/** @var list<TemplateField|Identifier> */
	private array $templateFields;

	public function __construct(
		private Identifier $extends,
		TemplateField|Identifier ...$templateFields,
	) {
		parent::__construct('extends', Type::empty(), '', '');
		$this->templateFields = $templateFields;
	}

	public function getIdentifier(): Identifier
	{
		return $this->extends;
	}

	/**
	 * @return list<TemplateField|Identifier>
	 */
	public function getTemplateValues(): array
	{
		return $this->templateFields;
	}

	/**
	 * Returns the whole row of generated comment source
	 *
	 * @return string
	 */
	public function getSource(): string
	{
		$ret = ' * @extends ';
		$ret .= $this->extends->getName();
		$ret .= $this->generateGenericSource();

		return $ret . PHP_EOL;
	}

	public function getIdentifiers(): array
	{
		$return = [$this->extends];
		foreach ($this->templateFields as $field) {
			if ($field instanceof Identifier) {
				$return[] = $field;
			}
			elseif ($field->getOfType()) {
				$return[] = $field->getOfType();
			}
		}
		return array_filter($return);
	}
}
