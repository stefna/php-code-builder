<?php declare(strict_types=1);

namespace Stefna\PhpCodeBuilder\Tests\CodeHelper;

use PHPUnit\Framework\TestCase;
use Stefna\PhpCodeBuilder\CodeHelper\ArrayCode;
use Stefna\PhpCodeBuilder\CodeHelper\AssignmentCode;
use Stefna\PhpCodeBuilder\CodeHelper\InstantiateClass;
use Stefna\PhpCodeBuilder\CodeHelper\StaticMethodCall;
use Stefna\PhpCodeBuilder\CodeHelper\VariableReference;
use Stefna\PhpCodeBuilder\ValueObject\Identifier;

final class AssignmentCodeTest extends TestCase
{
	public function testSimple(): void
	{
		$code = new ArrayCode(['test']);
		$var = new VariableReference('variable');

		$assignment = new AssignmentCode($var, $code);
		$this->assertSame([
			'$variable = [',
			[
				'\'test\','
			],
			'];',
		], $assignment->getSourceArray());
	}

	public function testAssignClass(): void
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
		$this->assertSame([
			'$variable = new StaticMethodCall(\'site-bearer-token\', [',
			[
				'\'type\' => \'http\',',
				'\'scheme\' => \'bearer\',',
				'\'description\' => \'Valid for site specific endpoints\',',
			],
			'], $testVar);',
		], $assignment->getSourceArray());
	}
}
