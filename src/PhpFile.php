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
class PhpFile implements CodeInterface
{
	/** @var string */
	private $name;
	/** @var bool */
	private $strict = false;
	/** @var string|null */
	private $namespace;
	/** @var \SplObjectStorage<Identifier, PhpClass|PhpTrait> */
	private $classes;
	/** @var PhpFunction[] */
	private $functions = [];
	/** @var string */
	private $source;
	/** @var \SplObjectStorage<Identifier>|Identifier[] */
	private $use;

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
		$self->classes[$object->getIdentifier()] = $object;
		return $self;
	}

	public function __construct($fileName)
	{
		$this->classes = new \SplObjectStorage();
		$this->use = new \SplObjectStorage();
		$this->name = $fileName;
	}

	public function setStrict(): self
	{
		$this->strict = true;
		return $this;
	}

	/**
	 * Generates the complete source code for the file
	 *
	 * @return string The source code for the file
	 */
	public function getSource(int $currentIndent = 0): string
	{
		return FlattenSource::source($this->getSourceArray());
	}

	public function getSourceArray(int $currentIndent = 0): array
	{
		$declaration = '<?php';
		if ($this->strict) {
			$declaration .= ' declare(strict_types=1);';
		}
		$ret = [];
		$ret[] = $declaration;
		$ret[] = '';

		if (count($this->classes) === 1) {
			$this->classes->rewind();
			$class = $this->classes->current();
			if ($class->getNamespace()) {
				$this->namespace .= $class->getNamespace();
				$this->namespace = trim($this->namespace, '\\');
			}
		}

		if ($this->namespace) {
			$ret[] = 'namespace ' . $this->namespace . ';';
			$ret[] = '';
		}

		$classesCode = [];
		foreach ($this->classes as $identifier) {
			/** @var PhpTrait|PhpClass $class */
			$class = $this->classes[$identifier];
			array_push($classesCode, ...$class->getSourceArray());
			foreach ($class->getUses() as $useIdentifier) {
				if ($this->use->contains($useIdentifier)) {
					continue;
				}
				$this->use->attach($useIdentifier);
			}
		}

		if (count($this->use) > 0) {
			foreach ($this->use as $identifier) {
				if ($identifier->getNamespace() === $this->namespace) {
					// don't need to add use statements for same namespace as file
					continue;
				}
				$useLine = 'use ' . ltrim($identifier->getFqcn(), '\\');
				if ($identifier->getAlias()) {
					$useLine .= ' as ' . $identifier->getAlias();
				}
				$useLine .= ';';
				$ret[] = $useLine;
			}
			$ret[] = '';
		}

		array_push($ret, ...$classesCode);

		if (count($this->functions) > 0) {
			foreach ($this->functions as $function) {
				array_push($ret, ...$function->getSourceArray());
			}
		}

		if ($this->source) {
			$ret[] = $this->source;
		}

		return $ret;
	}

	/**
	 * Set random code to be added at the bottom of file
	 *
	 * @param string $source
	 * @return PhpFile
	 */
	public function setSource(string $source): self
	{
		$this->source = $source;
		return $this;
	}

	/**
	 * Saves the source code for the file in $directory
	 *
	 * @param string $directory
	 * @return bool
	 */
	public function save(string $directory): bool
	{
		return (bool)file_put_contents($directory . DIRECTORY_SEPARATOR . $this->getName(), $this->getSource());
	}

	/**
	 * Adds a namespace
	 *
	 * @param string $namespace The namespace to add
	 * @return PhpFile
	 */
	public function setNamespace(string $namespace): self
	{
		$this->namespace = $namespace;

		return $this;
	}

	/**
	 * Checks if the file has a namespace
	 *
	 * @return bool
	 */
	public function hasNamespace(): bool
	{
		return (bool)$this->namespace;
	}

	/**
	 * Adds a class to the file
	 *
	 * @param PhpClass $class
	 * @return PhpFile
	 * @throws DuplicateValue If the class already exists
	 */
	public function addClass(PhpClass $class): self
	{
		if ($this->classes->contains($class->getIdentifier())) {
			throw new DuplicateValue('A class of the name (' . $class->getIdentifier()->getName() . ') does already exist.');
		}

		$this->classes[$class->getIdentifier()] = $class;
		return $this;
	}

	/**
	 * Adds a trait to the file
	 *
	 * @param PhpTrait $trait
	 * @return PhpFile
	 * @throws DuplicateValue If the class already exists
	 */
	public function addTrait(PhpTrait $trait): self
	{
		if ($this->classes->contains($trait->getIdentifier())) {
			throw new DuplicateValue('A trait of the name (' . $trait->getIdentifier()->getName() . ') does already exist.');
		}

		$this->classes[$trait->getIdentifier()] = $trait;
		return $this;
	}

	public function addInterface(PhpInterface $interface): self
	{
		if ($this->classExists($interface->getIdentifier())) {
			throw new DuplicateValue("A interface of the name ({$interface->getIdentifier()}) does already exist.");
		}

		$this->classes[$interface->getIdentifier()] = $interface;
		return $this;
	}

	/**
	 * Adds a global function to the file, should not be used, classes rocks :)
	 *
	 * @param PhpFunction $function
	 * @return PhpFile
	 * @throws DuplicateValue If the function already exists
	 */
	public function addFunction(PhpFunction $function): self
	{
		if ($this->functionExists($function->getIdentifier())) {
			throw new DuplicateValue('A function of the name (' . $function->getIdentifier()->getName() . ') does already exist.');
		}

		$this->functions[$function->getIdentifier()->getName()] = $function;

		return $this;
	}

	/**
	 * Add use statement after namespace declaration
	 *
	 * @param Identifier|string $identifier
	 * @param string $alias
	 * @return PhpClass
	 */
	public function addUse($identifier, string $alias = null): self
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

	/**
	 * Checks if a class with the same name does already exist
	 *
	 * @param Identifier|string $identifier
	 * @return bool
	 */
	public function classExists($identifier): bool
	{
		return $this->classes->contains(Identifier::fromUnknown($identifier));
	}

	/**
	 * Checks if a function with the same name does already exist
	 *
	 * @param string $identifier
	 * @return bool
	 */
	public function functionExists($identifier): bool
	{
		return array_key_exists($identifier, $this->functions);
	}

	public function getName(): string
	{
		if (substr($this->name, -4) === '.php') {
			return $this->name;
		}
		return $this->name . '.php';
	}
}
