<?php
class BaseException extends Exception {
	public function __toString () {
		return get_called_class()."\nMessage: ".$this->getMessage()."\n".$this->getTraceAsString();
	}
}
class ClassNotFoundException extends BaseException {};
class MethodNotFoundException extends BaseException {};
class MethodNotPublicException extends BaseException {};
class ConstraintViolatedException extends BaseException {};
?>