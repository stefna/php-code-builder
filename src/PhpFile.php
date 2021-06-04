<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\CodeHelper\CodeInterface;
use Stefna\PhpCodeBuilder\Exception\DuplicateValue;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;

/**
 * Class that represents the source code for a php file
 * A file can contain namespaces, classes and global functions
 *
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @author Andreas Sundqvist <andreas@stefna.is>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class PhpFile
{
	private bool $strict = false;
	private ?string $namespace = null;
	/** @var \SplObjectStorage<Identifier, PhpClass|PhpTrait> */
	private \SplObjectStorage $classes;
	/** @var PhpFunction[] */
	private array $functions = [];
	/** @var \SplObjectStorage<Identifier>|Identifier[] */
	private array|\SplObjectStorage $use;
	private array $source = [];

	public static function createFromClass(PhpTrait $object): self
	{
		$identifier = $object->getIdentifier();
		$path = '';
		if ($identifier->getNamespace()) {
			$path = ltrim(str_replace('\\', DIRECTORY_SEPARATOR, $identifier->getNamespace()), DIRECTORY_SEPARATOR);
			$path .= DIRECTORY_SEPARATOR;
		}

		$self = new self($path . $identifier->getName());
		$self->setStrict();
		$self->addObject($object, 'class');
		return $self;
	}

	public function __construct(
		private string $name,
	) {
		$this->classes = new \SplObjectStorage();
		$this->use = new \SplObjectStorage();
	}

	public function setStrict(): static
	{
		$this->strict = true;
		return $this;
	}

	public function setNamespace(string $namespace): static
	{
		$this->namespace = $namespace;
		return $this;
	}

	public function hasNamespace(): bool
	{
		return (bool)$this->namespace;
	}

	/**
	 * Saves the source code for the file in $directory
	 */
	public function save(string $directory): bool
	{
		return (bool)file_put_contents($directory . DIRECTORY_SEPARATOR . $this->getName(), $this->getSource());
	}

	protected function addObject(PhpClass|PhpTrait|PhpInterface $class, string $type): static
	{
		if ($this->classes->contains($class->getIdentifier())) {
			throw new DuplicateValue('A ' . $type . ' of the name (' . $class->getIdentifier()->getName() . ') does already exist.');
		}

		$this->classes[$class->getIdentifier()] = $class;
		return $this;
	}

	/**
	 * Adds a class to the file
	 *
	 * @throws DuplicateValue If the class already exists
	 */
	public function addClass(PhpClass $class): self
	{
		return $this->addObject($class, 'class');
	}

	/**
	 * @throws DuplicateValue If the class already exists
	 */
	public function addTrait(PhpTrait $trait): self
	{
		return $this->addObject($trait, 'trait');
	}

	public function addInterface(PhpInterface $interface): self
	{
		return $this->addObject($interface, 'interface');
	}

	/**
	 * Adds a global function to the file, should not be used, classes rocks :)
	 *
	 * @throws DuplicateValue If the function already exists
	 */
	public function addFunction(PhpFunction $function): self
	{
		if ($this->hasFunction($function->getIdentifier())) {
			throw new DuplicateValue('A function of the name (' . $function->getIdentifier()->getName() . ') does already exist.');
		}

		$this->functions[$function->getIdentifier()->getName()] = $function;
		return $this;
	}

	/**
	 * Add use statement after namespace declaration
	 */
	public function addUse(Identifier|string $identifier, string $alias = null): self
	{
		$identifier = Identifier::fromUnknown($identifier);
		if ($alias) {
			$identifier->setAlias($alias);
		}
		if (!$this->use->contains($identifier)) {
			$this->use->attach($identifier);
		}

		return $this;
	}

	public function hasClass(Identifier|string $identifier): bool
	{
		return $this->classes->contains(Identifier::fromUnknown($identifier));
	}

	public function hasFunction(Identifier|string $identifier): bool
	{
		return array_key_exists(Identifier::fromUnknown($identifier)->getName(), $this->functions);
	}

	public function getName(): string
	{
		if (substr($this->name, -4) === '.php') {
			return $this->name;
		}
		return $this->name . '.php';
	}

	public function getClasses(): \SplObjectStorage
	{
		return $this->classes;
	}

	/**
	 * @return PhpFunction[]
	 */
	public function getFunctions(): array
	{
		return $this->functions;
	}

	public function getUse(): \SplObjectStorage
	{
		return $this->use;
	}

	public function getNamespace(): ?string
	{
		return $this->namespace;
	}

	public function getSource(): array
	{
		return $this->source;
	}

	/**
	 * Set random code to be added at the bottom of file
	 */
	public function setSource(array $source): static
	{
		$this->source = $source;
		return $this;
	}

	public function isStrict(): bool
	{
		return $this->strict;
	}
}
