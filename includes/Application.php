<?php
require_once 'Config.php';
require_once 'includes/Exceptions.php';

class Application {
	static $conn;

	public static function start () {
		self::setup();
		self::connect();
		session_start();
		Router::route($GLOBALS["ROUTING_TABLE"]);
	}

	public static function connect ($user = null, $password = null) {
		// init the database connection
		$dbSettings = $GLOBALS["DATABASE_CONNECTION_SETTINGS"];
		if ($user == null) $user = $dbSettings["user"];
		if ($password == null) $password = $dbSettings["password"];
		unset($dbSettings["user"]);
		unset($dbSettings["password"]);
		$dsn = $dbSettings["type"].":";
		foreach ($dbSettings as $key => $value) {
			if ($key != "type") {
				$dsn .= "{$key}={$value};";
			}
		}
		$dsn = rtrim($dsn, ";");
		self::$conn = new DocumentRecord($dsn, $user, $password);
		self::$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	}

	public static function stop () {
		// other clean ups
		die();
	}

	public static function setup () {
		// set include paths
		$includePaths = $GLOBALS["INCLUDE_PATHS"];
		foreach ($includePaths as $path) {
			set_include_path(get_include_path().PATH_SEPARATOR.realpath($path));
		}
		// auto load all necessary classes
		spl_autoload_register(function($className){
			if (strpos($className, "\\")) {
				$className = str_replace("\\", ".", $className);
			}
			if (file_exists(stream_resolve_include_path($className.".php"))) {
				require_once $className.".php";
			} else {
				throw new ClassNotFoundException($className, 1);
			}
		});

		set_exception_handler(["Application", "handle"]);

		// set the default template dir
		View::setDefaultLoadDir(realpath("./templates"));

		// load the preload scripts
		$preload = $GLOBALS["PRE_LOAD_SCRIPTS"];
		foreach ($preload as $script) {
			require_once $script;
		}
	}

	public static function handle ($exception) {
		if (!($exception instanceof ClassNotFoundException)) {
			// Utility::sendEmail("bzhu@gwdg.de", "Bzhu", "Error captured with ".$GLOBALS["SITE_NAME"], (string) $exception);
		}
		Log::debug($exception);
		$view = View::loadFile("Error.php");
		$view->set([
			"title" => "error",
			"content" => "404, page not found"
		]);
		echo $view->generate(1,1);
		self::stop();
	}

	public static function transaction ($func) {
		self::$conn->beginTransaction();
		if ($func()) {
			self::$conn->commit();
			return true;
		} else {
			self::$conn->rollback();
			return false;
		}
	}
}
?>
