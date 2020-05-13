<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder;

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
	 * @param string $dataType The name of the datatype of the variable
	 * @param string $name
	 * @param string $description
	 * @return PhpDocElement
	 * @throws \InvalidArgumentException Throws exception if no name is supplied
	 */
	public static function getParam(string $dataType, string $name, string $description = ''): PhpDocElement
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

	/**
	 * Creates a throws element
	 *
	 * @param string $exception
	 * @param string $description
	 * @return PhpDocElement
	 */
	public static function getThrows(string $exception, string $description): PhpDocElement
	{
		return new PhpDocElement('throws', $exception, '', $description);
	}

	/**
	 * Creates a throws element
	 *
	 * @param Type|string $dataType The name of the datatype
	 * @param Type|string $name The name of the variable
	 * @param string $description Description of the variable
	 * @return PhpDocElement
	 */
	public static function getVar($dataType, string $name = '', string $description = ''): PhpDocElement
	{
		return new PhpDocElement('var', $dataType, $name, $description);
	}

	/**
	 * Creates a author element
	 *
	 * @param string $author The name of the author
	 * @param string $email Optional email of author
	 * @return PhpDocElement
	 */
	public static function getAuthor(string $author, string $email = ''): PhpDocElement
	{
		if ($email) {
			$author .= " <$email>";
		}
		return new PhpDocElement('author', $author, '', '');
	}

	/**
	 * Creates a return element
	 *
	 * @param string $dataType The name of the datatype
	 * @param string $description The description of the return value
	 * @return PhpDocElement
	 */
	public static function getReturn(string $dataType, string $description = ''): PhpDocElement
	{
		return new PhpDocElement('return', $dataType, '', $description);
	}

	/**
	 * Creates a deprecated element
	 *
	 * @param string $information The description of why the element is deprecated etc.
	 * @return PhpDocElement
	 */
	public static function getDeprecated(string $information = ''): PhpDocElement
	{
		return new PhpDocElement('deprecated', '', '', $information);
	}

	/**
	 * Creates a licence element
	 *
	 * @param string $information Information about the licence
	 * @return PhpDocElement
	 */
	public static function getLicence(string $information): PhpDocElement
	{
		return new PhpDocElement('licence', '', '', $information);
	}
}
