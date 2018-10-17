<?php

$SITE_NAME = "ListiWiki";
$ORGANISM_NAME = "<i>Listeria monocytogenes</i>";
$STRAIN = "EDG-e";

$INCLUDE_PATHS = [
	"includes", "app", "app/libs", "templates"
];

$PRE_LOAD_SCRIPTS = [
	"functions.php"
];

$DATABASE_CONNECTION_SETTINGS = [
	'type' => 'mysql',
	'host' => 'localhost',
	'charset' => 'utf8',
	'dbname' => '',
	'user' => '', // mysql account
	'password' => '' //mysql password
];

$DOCUMENT_RECORD_SETTINGS = [
	"virtual_column_name" => "data",
	"native_json_support" => true, // true if database is mysql 5.7+
];

// settings of the email address to send user invitation
$EMAIL_SETTINGS = [
	"Host" => "",
	"SMTPAuth" => true,
	"Username" => "",
	"Password" => "",
	"SMTPSecure" => "tls",
	"Port" => 587
];

$ROUTING_TABLE = [
	"/^$/i" => [null, "ApplicationFunctions\index"],
	"/^\/$/i" => [null, "ApplicationFunctions\index"],
	"/^\/FAQ$/" => [null, "ApplicationFunctions\FAQ"],
	"/^\/debug$/" => [null, "ApplicationFunctions\debug"],
	"/^\/statistics$/" => [null, "ApplicationFunctions\statistics"],
	"/^\/exports$/i" => [null, "ApplicationFunctions\\exports"],
	"/^\/people$/i" => [null, "ApplicationFunctions\\people"],
];

$GENOME_TYPE = "cyclic"; // can also be "linear"
$GENOME_LENGTH = 2944608;
$ADMIN_PASSWORD = ""; // this password is used when a user want to delete a gene

$OPEN_REGISTRATION = true; // if false, an invitation is required

?>