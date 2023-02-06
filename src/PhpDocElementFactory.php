<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

use Stefna\PhpCodeBuilder\ValueObject\Identifier;
use Stefna\PhpCodeBuilder\ValueObject\Type;

/**
 * Class that contains static methods to create preset doc elements
 *
 * @author Fredrik Wallgren <fredrik.wallgren@gmail.com>
 * @author Andreas Sundqvist <andreas@stefna.is>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class PhpDocElementFactory
{
	/**
	 * Creates a param element
	 *
	 * @param string|Type $dataType The name of the datatype of the variable
	 * @throws \InvalidArgumentException Throws exception if no name is supplied
	 */
	public static function getParam(Type|string $dataType, string $name, string $description = ''): PhpDocElement
	{
		if ($name === '') {
			throw new \InvalidArgumentException('A parameter must have a name!');
		}

		if ($name[0] === '$') {
			$name = substr($name, 1);
		}
		if ($dataType === 'long') {
			$dataType = 'int';
		}
		elseif ($dataType === 'double') {
			$dataType = 'float';
		}

		return new PhpDocElement('param', $dataType, $name, $description);
	}

	public static function getThrows(string $exception, string $description): PhpDocElement
	{
		return new PhpDocElement('throws', $exception, '', $description);
	}

	public static function method(string $returnType, Identifier|string $returnClass, string $methodName): PhpDocElement
	{
		return new PhpDocElement(
			'method',
			$returnType,
			'',
			Identifier::fromUnknown($returnClass)->getName() . ' ' . $methodName . '()'
		);
	}

	public static function getVar(Type|string $dataType, string $name = '', string $description = ''): PhpDocElement
	{
		return new PhpDocElement('var', $dataType, $name, $description);
	}

	public static function getAuthor(string $author, string $email = ''): PhpDocElement
	{
		if ($email) {
			$author .= " <$email>";
		}
		return new PhpDocElement('author', $author, '', '');
	}

	public static function getReturn(Type|string $dataType, string $description = ''): PhpDocElement
	{
		return new PhpDocElement('return', $dataType, '', $description);
	}

	public static function getDeprecated(string $information = ''): PhpDocElement
	{
		return new PhpDocElement('deprecated', Type::empty(), '', $information);
	}

	public static function getLicence(string $information): PhpDocElement
	{
		return new PhpDocElement('licence', Type::empty(), '', $information);
	}

	public static function getPropertyFromVariable(PhpVariable $var, string $description = ''): PhpDocElement
	{
		return new PhpDocElement('property', $var->getType(), $var->getIdentifier()->toString(), $description);
	}

	public static function getReadPropertyFromVariable(PhpVariable $var, string $description = ''): PhpDocElement
	{
		return new PhpDocElement('property-read', $var->getType(), $var->getIdentifier()->toString(), $description);
	}
}
