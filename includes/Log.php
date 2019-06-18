<?php
/**
 * Log
 */
class Log {
	const log_file_name = "var/log";

	static public function message($msg) {
		try {
			$logfile = fopen(realpath(Log::log_file_name), "a+");
		} catch (Exception $e) {
		}
		if ($logfile) {
			$caller = debug_backtrace()[0];
			$msg = date("Y-m-d H:i:s")."\t".$caller["file"]."\t".$caller["line"]."\t".$msg."\n";
			fwrite($logfile, $msg);
		}
	}

	static public function debug ($val){
		if (is_object($val)) {
			if (is_subclass_of($val, "BaseException")) {
				echo "<pre>".((string) $val)."</pre>";
			} else if (is_subclass_of($val, "Throwable")) {
				echo "<pre>".get_called_class()."\nMessage: ".$val->getMessage()."\n".$val->getTraceAsString()."</pre>";
			} else {
				$last = debug_backtrace()[0];
				echo "<pre>debug\t".$last["file"]." ".$last["line"].": ";
			 	var_dump($val);
			 	echo "\n</pre>";
			}
		} else {
			$last = debug_backtrace()[0];
			echo "<pre>debug\t".$last["file"]." ".$last["line"].": ";
		 	var_dump($val);
		 	echo "\n</pre>";
		}
		ob_flush();
	 	flush();
	}

	static public function l ($message) {
		// TODO: record the file
	}
}
?>