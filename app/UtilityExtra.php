<?php
require_once("PHPMailer/PHPMailer.php");
require_once("PHPMailer/Exception.php");
require_once("PHPMailer/OAuth.php");
require_once("PHPMailer/POP3.php");
require_once("PHPMailer/SMTP.php");

// use namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

trait UtilityExtra {
	public static function sendEmail ($email_addr, $name, $subject, $content) {
		$mail = new PHPMailer(true);
		try {
			$mail->isSMTP();
			foreach ($GLOBALS["EMAIL_SETTINGS"] as $key => $value) {
				$mail->{$key} = $value;
			}
			$site_name = $GLOBALS['SITE_NAME'];
			$mail->setFrom($mail->Username, "$site_name Team");
			$mail->addAddress($email_addr, $name);
			$mail->isHTML(true);
			$mail->Subject = $subject;
			$mail->Body = str_replace("\n", "<br/>", $content);
			$mail->AltBody = $content;
			$mail->send();
			return true;
		} catch (Exception $e) {
			debug($e->errorMessage());
			Log::message($e->errorMessage());
			return false;
		}
	}
	
	public static function isAssociateArray ($arr) {
		if (is_array($arr)) {
			if (array() === $arr) return false;
			   return array_keys($arr) !== range(0, count($arr) - 1);
		} else return false;
	}

	

	public static function toObject (&$data) {
		if (is_array($data) && self::isAssociateArray($data)) {
			$data = (object) $data;
		}
		if (is_object($data) || is_array($data)) {
			foreach ($data as $key => &$value) {
				self::toObject($value);
			}
		}
	}

	public static function encodeCSV ($table, $delimiter = ",") {
		$rows = [];
		foreach ($table as $row) {
			foreach ($row as &$cell) {
				$cell = '"'.addslashes($cell).'"';
			}
			$rows[] = implode($delimiter, $row);
		}
		return implode("\n", $rows);
	}
}
?>