<?php
require_once("ViewAdapters.php");

class AdministrationController extends Controller {
	public function read ($input, $accept) {
		if ($accept == HTML) {
			UserController::authenticate(2,HTML);
			$view = View::loadFile("layout1.tpl");
			$view->set([
				"content" => "{{administration.view.tpl}}",
				"title" => "Administration",
				"pageTitle" => "Administration",
				"showFootNote" => "none"
			]);
			$this->respond($view, 200, HTML);	
		} else $this->error("Unaccapted", 405, $accept);
	}

	public function update ($input, $accept) {}

	public function delete ($input, $accept) {}

	public function create ($input, $accept) {}

		
	/** 
	 * import data, provide replace/merge mode
	 * @author Marie-Theres Thieme <marietheres.thieme@stud.uni-goettingen.de>
	 * deprecated
	 */
	public function import ($input, $accept, $method) {
		UserController::authenticate(2, $accept);
		switch ($method) {
			case "PUT":
			case "DELETE":
			case "PATCH":
				$this->error("Method Not Allowed", 405, HTML);
		}
		if ($method == "GET") {
			switch ($accept) {
				case HTML:
				case HTML_PARTIAL:
					$view = View::loadFile("layout1.tpl");
					$view->set([
						"pageTitle" => "Import",
						"content" => "{{gene.import.tpl}}"
					]);
					$this->respond($view, 200, HTML);
				case JSON:
					$this->error("Unaccaptable format", 404, JSON);
					break;
			}

		} elseif ($method == "POST") {
			switch ($accept) {
				case HTML:
				case HTML_PARTIAL:
					$view = View::loadFile("layout1.tpl");
					$view->set([
						"pageTitle" => "Import",
						"content" => "{{gene.import.tpl}}"
					]);
					$fileString = file_get_contents($_FILES['File']['tmp_name']);
					if(empty($fileString)){
						$this->error("No file selected!", 404, HTML);
					}
					if(!$input["tableName"]){
						$this->error("No input for table name", 404, HTML);
					}
					if ($input["check"] == "replace"){
						$fileRows = explode("\n", trim($fileString, "\n\r"));
						$count = count($fileRows);
						$keys = explode("\t", (array_shift($fileRows)));
						array_push($keys, "id");
						//array_push($keys, "genomicContext", "categories", "regulons", "The protein", "Expression and Regulation", "strain", "id");
						foreach ($fileRows as $key => $value){
							$values[$key] = explode("\t", $value);
						}
						/*for($i = 0; $i<$count-1; $i++){
							array_push($values[$i], "[[this]]", "[[this]]", "[[this]]", ["Paralogous protein(s)" => ["[[this]]"]], ['Operons' => '[[this]]', 'Other regulations' => '[[this]]'], "EGD-e", "");
						}*/
						for($i = 0; $i<$count-1; $i++){
							array_push($values[$i], " ");
							for($j = 0; $j<count($values[$i])-1; $j++){
								if($values[$i][$j] == "\\N"){
									$values[$i][$j] = NULL;
								}
							}
							$table[$i] = array_combine($keys, $values[$i]);
							$table[$i]["id"] = sha1($table[$i]["title"]);
							/*if($table[$i]["title"]==""){
								$table[$i]["title"] = $table[$i]["locus"];
							}*/
							$conn = Application::$conn;
							$result[$i] = $conn->insert($input["tableName"], $table[$i]);
							if(!$result[$i]){
								Log::debug($table[$i]);
							}
						}
					} elseif ($input["check"] == "merge"){
						$rows = explode("\n", trim($fileString, "\n\r"));
						$keys = explode("\t", trim((array_shift($rows)), "\n\r"));
						$identifierKey = $keys[0];
						$insertKey = $keys[1];
						foreach($rows as $row) {
							$rowVals = explode("\t", $row);
							$ident = [
								$identifierKey => $rowVals[0]
							];
							if($input["insertType"] == "array"){
								$vals = explode("; ", $rowVals[1]);
							} else if($input["insertType"] == "scalar"){
								$vals = $rowVals[1];
							} else $this->error("No insert data type selected!", 404, HTML);
							$gene = Application::$conn->select($input["tableName"], "*", $ident);
							if($input["after"]){
								try {
									Utility::insertAfter($gene[0], $insertKey, $vals, $input["after"]);
								} catch(BaseException $e){
									Log::debug($gene->locus." ".$e->getMessage());
									continue;
								}
							} else {
								$gene[0][$insertKey] = $vals;
							}
							if($gene[0] != null){
								$result = Application::$conn->update($input["tableName"], $gene[0], $ident);
								if(!$result){
									Log::debug($gene->locus);
								}
							}
						}

					} else $this->error("Select 'Replace' or 'Merge'", 404, HTML);
				$this->respond($view, 200, HTML);
			case JSON:
				$view = View::loadFile("layout1.tpl");
				$view->set([
					"pageTitle" => "Import",
					"content" => "{{gene.import.tpl}}"
				]);
				$this->respond($view, 200, JSON);
				break;
			}
		}
	}

	public function repair ($input, $accept, $method) {
		UserController::authenticate(3, $accept);
		if ($method == "GET" && $accept == JSON) {
			$tableName = $this->filter($input, "tableName", "has", ["Table name is required", 400, $accept]);
			$conn = Application::$conn;
			if ($conn->getColumnNames($tableName)) {
				$added = MetaData::fix($tableName);
				if ($added) {
					$this->respond(["message" => "okay"], 200, JSON);
				} else {
					$this->error("repair is not successful", 500, JSON);
				}
			} else $this->error("Table not found", 404, $accept);
		} else $this->error("Unaccepted", 406, $accept);
	}

	/**
	 * general importer for tables
	 */
	public function importer ($input, $accept, $method) {
		if ($accept != HTML) $this->error("Unaccepted", 406, $accept);
		UserController::authenticate(3, $accept);
		$errors = [];
		if ($method == "POST") {
			$tableName = $this->filter($input, "tableName", "has");
			$mode = $this->filter($input, "mode", "/^(replace)|(patch)$/i");
			// check the existence of the table
			if ($tableName) {
				$conn = Application::$conn;
				$cols = $conn->getColumnNames($tableName);
				if (!$cols) $errors[] = "Table $tableName not found, please import the database structure please";
			} else $errors[] = "Table name is required";
			if (!$mode) $errors[] = "Mode is required";
			if (!isset($_FILES["file"])) $errors[] = "No file uploaded";
			if ($_FILES["file"]["size"] > 1048576 * 2) $errors[] = "File too large, max. 2MB is accepted.";

			// no errors with the input data
			if (empty($errors)) {
				// get file content as csv
				$fileContent = file_get_contents($_FILES["file"]["tmp_name"]);
				$fileContent = str_replace("\r", "", $fileContent);
				$table = explode("\n", $fileContent);
				foreach($table as &$row) {
					$row = explode("\t", $row);
				}
				$header = array_shift($table);
				// now working on the data
				if ($mode == "replace") {
					if (!$conn->doQuery("delete from `$tableName`")) {
						$errors[] = "Cannot delete the table content, replace not successful";
					} 
				}
				if (empty($errors)) {
					foreach($table as $i => $row) {
						if (count($row) === count($header)) {
							$row = array_combine($header, $row);
							if (!$conn->insert($tableName, $row)) {
								$errors[] = "Error on line ".($i+2).": ".$conn->lastError;
							}
						} else $errors[] = "Error on line ".($i+2).": row has missing or extra cells. Row is ignored";
					}
				}
			}
			if (empty($errors)) $errors[] = "Import successful";
		}
		$view = View::loadFile("layout1.tpl");
		$view->set([
			"title" => "General importer",
			"pageTitle" => "General importer",
			"content" => "{{admin.importer.tpl}}",
			"showFootNote" => "none",
			"errors" => $errors
		]);
		$this->respond($view, 200, HTML);
	}

	/** 
	 * import expression data, source data should be in a json format with conditions and data
	 * @author Marie-Theres Thieme <marietheres.thieme@stud.uni-goettingen.de>
	 * deprecated
	 */
	public function expressionImport ($input, $accept, $method){
		UserController::authenticate(2, $accept);
		switch ($method) {
			case "PUT":
			case "DELETE":
			case "PATCH":
				$this->error("Method Not Allowed", 405, HTML);
		}
		if ($method == "GET") {
			switch ($accept) {
				case HTML:
				case HTML_PARTIAL:
					$view = View::loadFile("layout1.tpl");
					$view->set([
						"pageTitle" => "Import",
						"content" => "{{expression.import.tpl}}"
					]);
					$this->respond($view, 200, HTML);
				case JSON:
					$this->error("Unaccaptable format", 404, JSON);
					break;
			}

		} else if ($method == "POST") {
			switch ($accept) {
				case HTML:
				case HTML_PARTIAL:
					$view = View::loadFile("layout1.tpl");
					$view->set([
						"pageTitle" => "Import",
						"content" => "{{expression.import.tpl}}"
					]);

					$fileJSON = file_get_contents($_FILES['File']['tmp_name']);   //gets content of the uploaded file into the variable as a string
					if(empty($fileJSON)){
						echo "Upload error";
					} else {
						echo "Upload successfull";
						$fileArray = json_decode($fileJSON, true);
					}
					foreach($fileArray as $key => $value){
						if($key == "condition"){
							$table_name = "Condition";
							$conn = Application::$conn;
							$result[] = $conn->insert($table_name, $value);
						}else if($key == "data"){
							if($fileArray["condition"]["type"] == "T"){
								$table_name = "TranscriptomicData";
							}else if ($fileArray["condition"]["type"] == "P"){
								$table_name = "ProteomicData";
							}
							foreach ($value as $subkey => $subvalue){
								$dataArray["con".$fileArray["condition"]["id"]] = $subvalue;
								$dataArray["locus"] = $subkey;
								$where = array("locus" => $subkey);
								$conn = Application::$conn;
								$result[] = $conn->update($table_name, $dataArray, $where);
							}
						}
					}
					$this->respond($view, 200, HTML);
				case JSON:
					$view = View::loadFile("layout1.tpl");
					$view->set([
						"pageTitle" => "Import",
						"content" => "{{expression.import.tpl}}"
					]);

					$this->respond($view, 200, JSON);
					break;
			}
		}
	}

	public function userGroupSettings ($input, $accept, $method) {
		UserController::authenticate(3, $accept);
		if ($accept != HTML) {
			$this->error("Unaccaptable", 405, $accept);
		}
		$view = View::loadFile("layout1.tpl");
		$allUsers = User::getAll(1);
		$view->set([
			"showFootNote" => "none",
			"users" => $allUsers,
			"title" => "User group settings",
			"pageTitle" => "User group settings",
			"content" => "{{userGroup:users}}",
			"jsAfterContent" => ["user.update.group"],
			"navlinks" => [
				["innerHTML" => "Administration", "href" => "administration"]
			]
		]);	
		$this->respond($view, 200, HTML);
	}

	public function schema ($input, $accept, $method) {
		UserController::authenticate(3, $accept);
		if ($accept == HTML && $method == "GET") {
			$className = $this->filter($input, "className", "has", ["Class name is required", 400, $accept]);
			$meta = MetaData::get($className);
			if ($meta) {
				Utility::decodeLinkForEdit($meta);
				$view = View::loadFile("layout2.tpl");
				$view->set([
					"pageTitle" => "Edit template: ".$className,
					"headerTitle" => "Edit template: ".$className,
					"content" => "{{administration.scheme.editor.tpl}}",
					"method" => "put",
					"scheme" => str_replace("},", "},\n", json_encode($meta->scheme)),
					"vars" => [
						"tableName" => $className
					],
					"jsAfterContent" => ["administration.schema.editor","all.editor"]
				]);
				$this->respond($view, 200, HTML);	
			} else {
				try {
					$view = View::loadFile("layout2.tpl");
					$view->set([
						"pageTitle" => "Edit template: ".$className,
						"headerTitle" => "Edit template: ".$className,
						"content" => "{{administration.scheme.editor.tpl}}",
						"method" => "post",
						"scheme" => "[\n\n]",
						"jsAfterContent" => ["administration.schema.editor","all.editor"]
					]);
					$this->respond($view, 200, HTML);
				} catch (BaseException $e) {
					$this->error("$className does not exist", 404, $accept);
				}
			}
		} elseif ($accept == JSON && $method == "POST") {
			$this->filter($input, "className", "has", ["class name is required", 400, JSON]);
			$meta = MetaData::withData($input);
			if ($meta->insert()) {
				$this->respond(null, 201, JSON);
			} else {
				$this->respond("Internal error", 500, JSON);
			}

		} elseif ($accept == JSON && $method == "PUT") {
			$this->filter($input, "className", "has", ["class name is required", 400, JSON]);
			$meta = MetaData::withData($input);
			if ($meta->update()) {
				$this->respond(null, 201, JSON);
			} else {
				$this->respond("Internal error", 500, JSON);
			}
		} else $this->error("Invalid request", 400, $accept);
	}
}
?>