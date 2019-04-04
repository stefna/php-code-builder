<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Exception;
use Stefna\PhpCodeBuilder\Exception\DuplicateValue;

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
	/** @var string */
	private $name;

	/** @var bool */
	private $strict = false;

	/** @var string */
	private $namespace;

	/** @var PhpTrait[]|PhpClass[] */
	private $classes = [];

	/** @var PhpFunction[] */
	private $functions = [];

	/** @var string */
	private $source;

	/** @var string[] */
	private $use = [];

	public function __construct($fileName)
	{
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
	public function getSource(): string
	{
		$ret = '<?php';
		if ($this->strict) {
			$ret .= ' declare(strict_types=1);';
		}
		$ret .= PHP_EOL . PHP_EOL;

		if ($this->namespace) {
			$ret .= 'namespace ' . $this->namespace . ';' . PHP_EOL . PHP_EOL;
		}

		$classesCode = '';

		if (count($this->classes) > 0) {
			foreach ($this->classes as $class) {
				$classesCode .= $class->getSource();
				$this->use = array_merge($this->use, $class->getUses());
			}
		}

		if (count($this->use) > 0) {
			foreach ($this->use as $class => $as) {
				$ret .= 'use ' . $class;
				if ($class !== $as) {
					$ret .= ' as ' . $as;
				}
				$ret .= ';' . PHP_EOL;
			}
			$ret .= PHP_EOL;
		}

		$ret .= $classesCode;

		if (count($this->functions) > 0) {
			foreach ($this->functions as $function) {
				$function->setIndentionLevel(0);
				$ret .= $function->getSource();
			}
		}

		if ($this->source) {
			$ret .= PHP_EOL . $this->source;
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
		return (bool)file_put_contents($directory . DIRECTORY_SEPARATOR . $this->name . '.php', $this->getSource());
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
		if ($this->classExists($class->getIdentifier())) {
			throw new DuplicateValue('A class of the name (' . $class->getIdentifier() . ') does already exist.');
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
		if ($this->classExists($trait->getIdentifier())) {
			throw new DuplicateValue('A trait of the name (' . $trait->getIdentifier() . ') does already exist.');
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
			throw new DuplicateValue('A function of the name (' . $function->getIdentifier() . ') does already exist.');
		}

		$this->functions[$function->getIdentifier()] = $function;

		return $this;
	}

	/**
	 * Add use statement after namespace declaration
	 *
	 * @param string $class fqcn
	 * @param string $alias optional alias
	 * @return PhpFile
	 */
	public function addUse(string $class, string $alias = null): self
	{
		$this->use[$class] = $alias ?: $class;

		return $this;
	}

	/**
	 * Checks if a class with the same name does already exist
	 *
	 * @param string $identifier
	 * @return bool
	 */
	public function classExists($identifier): bool
	{
		return array_key_exists($identifier, $this->classes);
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
}
