<?php

function assertEqual($expected, $actual, $message = '') {
	if ($expected !== $actual) {
		echo "<b style='color:red;'>❌ Assertion failed:</b> $message<br>";
		echo "Expected: <pre>" . var_export($expected, true) . "</pre>";
		echo "Actual: <pre>" . var_export($actual, true) . "</pre>";
		return false;
	} else {
		echo "<b style='color:green;'>✅ Passed:</b> $message<br>";
		return true;
	}
}

function assertTrue($condition, $message = '') {
	return assertEqual(true, $condition, $message ?: 'Expected condition to be true');
}

function assertFalse($condition, $message = '') {
	return assertEqual(false, $condition, $message ?: 'Expected condition to be false');
}

function assertNull($value, $message = '') {
	return assertEqual(null, $value, $message ?: 'Expected value to be null');
}

function assertNotNull($value, $message = '') {
	if ($value === null) {
		echo "<b style='color:red;'>❌ Assertion failed:</b> $message<br>";
		echo "Value is null<br>";
		return false;
	} else {
		echo "<b style='color:green;'>✅ Passed:</b> $message<br>";
		return true;
	}
}

function assertIsArray($value, $message = '') {
	return assertEqual(true, is_array($value), $message ?: 'Expected value to be an array');
}

function assertIsObject($value, $message = '') {
	return assertEqual(true, is_object($value), $message ?: 'Expected value to be an object');
}

function assertIsString($value, $message = '') {
	return assertEqual(true, is_string($value), $message ?: 'Expected value to be a string');
}

function assertIsInt($value, $message = '') {
	return assertEqual(true, is_int($value), $message ?: 'Expected value to be an integer');
}

function assertIsFloat($value, $message = '') {
	return assertEqual(true, is_float($value), $message ?: 'Expected value to be a float');
}

function assertIsBool($value, $message = '') {
	return assertEqual(true, is_bool($value), $message ?: 'Expected value to be a boolean');
}


function assertArrayHasKey($key, $array, $message = '') {
	return assertEqual(true, array_key_exists($key, $array), $message ?: "Expected key '$key' in array");
}

function assertObjectHasProperty($property, $object, $message = '') {
	return assertEqual(true, property_exists($object, $property), $message ?: "Expected property '$property' in object");
}

function assertCount($expectedCount, $arrayOrCountable, $message = '') {
	return assertEqual($expectedCount, count($arrayOrCountable), $message ?: "Expected count of $expectedCount");
}
