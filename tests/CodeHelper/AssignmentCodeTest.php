<?php declare(strict_types=1);

namespace CodeHelper;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\CodeHelper\ArrayCode;
use Stefna\PhpCodeBuilder\CodeHelper\AssignmentCode;
use Stefna\PhpCodeBuilder\CodeHelper\InstantiateClass;
use Stefna\PhpCodeBuilder\CodeHelper\StaticMethodCall;
use Stefna\PhpCodeBuilder\CodeHelper\VariableReference;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;

final class AssignmentCodeTest extends TestCase
{
	public function testSimple()
	{
		$code = new ArrayCode(['test']);
		$var = new VariableReference('variable');

		$assignment = new AssignmentCode($var, $code);
		var_dump($assignment->getSourceArray());
	}

	public function testAssignClass()
	{
		$code = new InstantiateClass(Identifier::fromString(StaticMethodCall::class), [
			'site-bearer-token',
			new ArrayCode([
				'type' => 'http',
				'scheme' => 'bearer',
				'description' => 'Valid for site specific endpoints',
			]),
			new VariableReference('testVar'),
		]);
		$var = new VariableReference('variable');

		$assignment = new AssignmentCode($var, $code);
		var_dump($assignment->getSourceArray());
	}
}
