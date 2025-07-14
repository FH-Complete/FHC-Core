<?php

function lineBreak()
{
	return IS_CLI ? PHP_EOL : '<br>';
}

function preFormat($text)
{
	if (IS_CLI) {
		return $text; // Plain text in CLI
	} else {
		return "<pre>$text</pre>"; // HTML preformatted in browser
	}
}

function colorText($text, $color)
{
	if (IS_CLI) {
		// ANSI color codes
		$colors = [
			'red' => "\033[31m",
			'green' => "\033[32m",
			'reset' => "\033[0m",
		];
		return $colors[$color] . $text . $colors['reset'];
	} else {
		// HTML styles
		$styles = [
			'red' => "<b style='color:red;'>$text</b>",
			'green' => "<b style='color:green;'>$text</b>",
		];
		return $styles[$color];
	}
}

function assertEqual($expected, $actual, $message = '')
{
	if ($expected !== $actual) {
		echo colorText('❌ Assertion failed:', 'red') . ' ' . $message . lineBreak();
		echo "Expected: " . preFormat(var_export($expected, true)) . lineBreak();
		echo "Actual: " . preFormat(var_export($actual, true)) . lineBreak();
		return false;
	} else {
		echo colorText('✅ Passed:', 'green') . ' ' . $message . lineBreak();
		return true;
	}
}


function assertTrue($condition, $message = '')
{
	return assertEqual(true, $condition, $message ?: 'Expected condition to be true');
}

function assertFalse($condition, $message = '')
{
	return assertEqual(false, $condition, $message ?: 'Expected condition to be false');
}

function assertNull($value, $message = '')
{
	return assertEqual(null, $value, $message ?: 'Expected value to be null');
}

function assertNotNull($value, $message = '')
{
	if ($value === null) {
		echo colorText('❌ Assertion failed:', 'red') . ' ' . $message . lineBreak();
		echo 'Value is null' . lineBreak();
		return false;
	} else {
		echo colorText('✅ Passed:', 'green') . ' ' . $message . lineBreak();
		return true;
	}
}


function assertIsArray($value, $message = '')
{
	return assertEqual(true, is_array($value), $message ?: 'Expected value to be an array');
}

function assertIsObject($value, $message = '')
{
	return assertEqual(true, is_object($value), $message ?: 'Expected value to be an object');
}

function assertIsString($value, $message = '')
{
	return assertEqual(true, is_string($value), $message ?: 'Expected value to be a string');
}

function assertIsInt($value, $message = '')
{
	return assertEqual(true, is_int($value), $message ?: 'Expected value to be an integer');
}

function assertIsFloat($value, $message = '')
{
	return assertEqual(true, is_float($value), $message ?: 'Expected value to be a float');
}

function assertIsBool($value, $message = '')
{
	return assertEqual(true, is_bool($value), $message ?: 'Expected value to be a boolean');
}


function assertArrayHasKey($key, $array, $message = '')
{
	return assertEqual(true, array_key_exists($key, $array), $message ?: "Expected key '$key' in array");
}

function assertObjectHasProperty($property, $object, $message = '')
{
	return assertEqual(true, property_exists($object, $property), $message ?: "Expected property '$property' in object");
}

function assertCount($expectedCount, $arrayOrCountable, $message = '')
{
	return assertEqual($expectedCount, count($arrayOrCountable), $message ?: "Expected count of $expectedCount");
}
