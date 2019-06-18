<?php
namespace Monkey;

/**
 * BaseException
 */
class BaseException extends \Exception {
	public function __toString () {
		return get_called_class()."\nMessage: ".$this->getMessage()."\n".$this->getTraceAsString();
	}
}
/**
 * ClassNotFoundException
 */
class ClassNotFoundException extends BaseException {};
/**
 * MethodNotFoundException
 */
class MethodNotFoundException extends BaseException {};
/**
 * MethodNotPublicException
 */
class MethodNotPublicException extends BaseException {};
/**
 * ConstraintViolatedException
 */
class ConstraintViolatedException extends BaseException {};
?>