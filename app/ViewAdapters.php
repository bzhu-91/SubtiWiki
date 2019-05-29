<?php

/**
 * general
 */

View::registerAdapter("css", function($data){
	$str = "";
	if ($data) {
		foreach ($data as $css) {
			$str .= "<link rel='stylesheet' type='text/css' href='css/{$css}.css'>";
		}
	}
	return $str;
});

View::registerAdapter("js", function($data){
	$str = "";
	if ($data) foreach ($data as $js) {
		$str .= "<script type='text/javascript' src='js/{$js}.js'></script>";
	}
	return $str;
});

View::registerAdapter("jsvar", function($data){
	$str = json_encode($data);
	$str = str_replace("'", "\'", $str);
	return $str;
});

View::registerAdapter("jsvars", function($data){
	$str = '<script type="text/javascript">';
	foreach ($data as $key => $value) {
		$value = str_replace("\\", "\\\\", json_encode($value));
		$value = str_replace("'", "\'", $value);
		$str .= "var $key = JSON.parse('$value');";
	}
	$str .= "</script>";
	return $str;
});

View::registerAdapter("ul", function($data){
	$str = "<ul>";
	if($data) foreach ($data as $row) {
		$str .= "<li>$row</li>";
	}
	$str .= "</ul>";
	return $str;
});

View::registerAdapter("list", function($data) {
	$str = "";
	if($data) foreach ($data as $row) {
		$str .= "<p>$row</p>";
	}
	return $str;
});

View::registerAdapter("objectList", function ($data){
	$str = "";
	if($data) foreach ($data as $row) {
		$str .= "<p>".$row->toLinkMarkup()."</p>";
	}
	return $str;
});

View::registerAdapter("objectGrid", function ($data){
	$str = "";
	if ($data) {
		$c = count($data);
		$str .= "<div style='width:100%'><div style='width:30%;display:inline-block;vertical-align:top'>";
		for ($i=0; $i < ceil($c / 3); $i++) { 
			$str .= "<p>".$data[$i]->toLinkMarkup()."</p>";
		}
		$str .= "</div>";
		$str .= "<div style='width:30%;display:inline-block;vertical-align:top'>";
		for ($i=ceil($c / 3); $i < ceil($c / 3) * 2 && $i < $c; $i++) { 
			$str .= "<p>".$data[$i]->toLinkMarkup()."</p>";
		}
		$str .= "</div>";
		$str .= "<div style='width:30%;display:inline-block;vertical-align:top'>";
		for ($i=ceil($c / 3) * 2; $i < $c; $i++) { 
			$str .= "<p>".$data[$i]->toLinkMarkup()."</p>";
		}
		$str .= "</div></div>";
		return $str;
	}
});

View::registerAdapter("floatButton", function($data){
	$str = "";
	if ($data) foreach ($data as $button) {
		$button = (object) $button;
		$str .= "<a href='{$button->href}'><img src='img/{$button->icon}'/></a>";
	}
	return $str;
});

/**
 * layout1
 */
View::registerAdapter("navlink", function($data){
	if ($data == null) {
		$data = [];
	}
	$str = "<ul>";
	$user = User::getCurrent();
	if ($user) {
		$data[] = [
			"innerHTML" => "Hello, ".$user->name,
			"href" => "user?name=".$user->name
		];
		$data[] = [
			"innerHTML" => "Log out",
			"onclick" => "user.logout()"
		];
	} else {
		$data[] = [
			"innerHTML" => "Log in",
			"onclick" => "user.login()"
		];
	}
	foreach ($data as $link) {
		$link = (object) $link;
		if (property_exists($link, "href")) {
			$str .= "<li><a href='{$link->href}' target='_blank'>{$link->innerHTML}</a></li>";
		} elseif (property_exists($link, "onclick")) {
			$str .= "<li><a href='javascript:void(0);' onclick='{$link->onclick}'>{$link->innerHTML}</a></li>";
		}
	}
	$str.= "</ul>";
	return $str;
});

/**
 * gene related
 */

View::registerAdapter("geneTable", function($data) {
	if ($data) {
		$str .= "<table class='m_table' cellspacing='0'><tr><td>Name</td><td>Function</td></tr>";
		foreach ($data as $gene) {
			Utility::decodeLinkForView($gene->function);
			$str .= "<tr id='{$gene->id}'><td><i>".$gene->toLinkMarkup()."</i></td><td>{$gene->function}</td></tr>";
		}
		$str .= "</table>";
	}
	return $str;
});

View::registerAdapter("relationGeneTable", function($data){
	$str = "";
	if ($data) {
		$str .= "<table class='m_table' cellspacing='0' rewrite=true><tr><td>Name</td><td>Function</td></tr>";
		foreach ($data as $row) {
			$gene = $row->gene;
			Utility::decodeLinkForView($gene->function);
			$str .= "<tr id='{$row->id}'><td><i>".$gene->toLinkMarkup()."</i></td><td>{$gene->function}</td></tr>";
		}
		$str .= "</table>";
	}
	return $str;
});

View::registerAdapter("relationGeneTableEdit", function ($data) {
	$str = "";
	if ($data) {
		$str .= "<table class='m_table' cellspacing='0'><tr><td>Name</td><td>Function</td><td>Operation</td></tr>";
		$str .= "<tr><td><input type='text'></td><td></td><td><button class='button addBtn' target='gene'>Add</td></tr>";

		foreach ($data as $row) {
			$gene = $row->gene;
			Utility::decodeLinkForView($gene->function);
			$str .= "<tr id='{$row->id}'><td><i>".$gene->toLinkMarkup()."</i></td><td>{$gene->function}</td><td><button class='button delBtn' target='gene' id='{$gene->id}'>Delete</button></td></tr>";
		}
		$str .= "</table>";
	}
	return $str;
});

View::registerAdapter("structure", function($data){
	Log::debug($data);
	return "<h3>Structure</h3>
	<p><a href='http://www.rcsb.org/structure/{$data}' target='_blank'>
		<img id='structure' src='http://www.rcsb.org/pdb/images/{$data}_bio_r_500.jpg' style='width: 98%' />
	</a></p>";
});

/**
 * category related
 */

View::registerAdapter("categoryTree", function($data){
	$str = "";
	if ($data) {
		foreach ($data as $category) {
			$dept = $category->getDepth();
			$str .= '<p style="margin-left: '.($dept*30).'px" class="category" id="'.$category->id.'">'.$category->toLinkMarkup().'</p>';
		}
	}
	return $str;
});

View::registerAdapter("categoryList", function($data){
	$str = "";
	if ($data) {
		foreach ($data as $category) {
			$str .= '<p class="category" id="'.$category->id.'">'.$category->toLinkMarkup().'</p>';
		}
	}
	return $str;
});

/**
 * user related
 */

View::registerAdapter("userTable", function($data){
	$str = "";
	if ($data) {
		$str .= "<table class='m_table' cellspacing='0'><tr><td>Name</td><td>Edit</td></tr>";
		foreach ($data as $user) {
			$str .= "<tr><td><a href='user?name={$user->name}'>".$user->name."</a></td><td><a href=''>Edits</a></td></tr>";
		}
		$str .= "</table>";
	}
	return $str;
});

View::registerAdapter("userGroup", function($data){
	$str = "";
	if ($data) {
		$str .= "<table class='m_table' cellspacing='0'><tr><td>Name</td><td>Group</td><td>Operation</td></tr>";
		foreach ($data as $user) {
			$str .= "<tr><td><a href='user?name={$user->name}' name='name'>".$user->name."</a><input type='hidden' value='{$user->name}' name='name'/></td><td>";
			$defs = [
				"Normal user" => 1,
				"Admin" => 2,
				"Database Admin" => 3,
			];
			$str .= "<select name='privilege'>";
			$options = "";
			foreach ($defs as $key => $value) {
				if ($user->privilege == $value) {
					$options .= "<option value='$value' selected>$key</option>";
				} else {
					$options .= "<option value='$value'>$key</option>";
				}
			}
			$str .= $options."</select>";
			$str .= "</td><td><button class='submit'>Update</button></tr>";
		}
		$str .= "</table>";
	}
	return $str;
});

/**
 * operon related
 */

View::registerAdapter("operonTable", function($data) {
	$str = "";
	if ($data) {
		$str .= "<table class='m_table' cellspacing='0' rewrite=true><tr><td>Operon</td><td>Operation</td></tr>";
		foreach ($data as $operon) {
			Utility::decodeLinkForView($operon->title);
			$str .= "<tr><td>$operon->title</td><td><a href='operon?id={$operon->id}' class='button'>View</a></td></tr>";
		}
		$str .= "</table>";
	}
	return $str;
});
/**
 * regulation edit table
 */

View::registerAdapter("regulationTableEdit", function($data) {
	$str = "";
	if ($data !== null) {
		$str .= "<table class='m_table' cellspacing='0'>";
		$str .= "<tr><th>Regulator</th><th>Mode</th><th>Description</th><th>Operation</th>\n\t{{regulation.blank.tpl}}";
		if ($data) {
			foreach ($data as $regulation) {
				$row = View::loadFile("regulation.editor.tpl");
				$row->set($regulation);
				$row->set([
					"type" => lcfirst(get_class($regulation->regulator)),
					"description" => $regulation->description ? $regulation->description : "[pubmed|]",
				]);
				$str .= $row->generate(1);
			}
		}
	}
	$str .= "</table>";
	return $str;
});

View::registerAdapter("history", function ($data){
	if ($data) {
		$size = count($data);
		$table = "<table class='m_table'>";
		$table .= "<tr><th>Time</th><th>User</th><th>Operation</th><th>Link</th><tr>";
		for ($i=0; $i < $size; $i++) { 
			$record = $data[$i];
			$table .= "<tr>";
			$table .= "<td>".$record->time."</td>";
			$table .= "<td>".$record->user."</td>";
			$table .= "<td>".$record->lastOperation."</td>";
			// if ($i == $size - 1) {
			// 	$table .= "<td><a class='button withCurrent' commit='".$record->commit."'>with current</a></td>";
			// } else {
			// 	$table .= "<td><a class='button withCurrent' commit='".$record->commit."'>with current</a><a class='button withPrevious' commit='".$record->commit."' previous='".$data[$i+1]->commit."'>with previous</a></td>";
			// }
			if ($i == 0) {
				$previous = "current";
			} else {
				$previous = $data[$i-1]->commit;
			}
			$table .= "<td><a class='button viewEdit' commit='".$record->commit."' previous='$previous'>View</a></td>";
			$table .= "</tr>";
		}
		$table .= "</table>";
		return $table;
	} else return "No records";
});

View::registerAdapter("table", function($data){
	$str = "";
	if ($data) {
		$str .= "<table class='m_table' rewrite=true>";
		foreach ($data as $row) {
			$str .= "<tr>";
			foreach ($row as $td) {
				$str .= "<td>$td</td>";
			}
			$str .= "</tr>";
		}
		$str .= "</table>";
	}
	return $str;
});

View::registerAdapter("metaboliteEditor", function($data){
	$str = "";
	if ($data) {
		foreach ($data as $metabolite) {
			$view = View::loadFile("metabolite.editor.each.tpl");
			$view->set($metabolite);
			$str .= $view->generate(1,1);
		}
	}
	return $str;
});

View::registerAdapter("metaboliteTable", function($data){
	$str = "";
	if ($data) {
		$str = "<table class='m_table'><tr><th>Id</th><th>Name</th><th>Synonym</th><th>PubChem</th><th>Operation</th></tr>";
		foreach($data as $metabolite) {
			$str .= "<tr>";
			$str .= "<td>".$metabolite->id."</td>";
			$str .= "<td>".$metabolite->title."</td>";
			$str .= "<td>".$metabolite->synonym."</td>";
			if($metabolite->pubchem) $str .= "<td><a href='https://pubchem.ncbi.nlm.nih.gov/compound/{$metabolite->pubchem}'>PubChem</a></td>";
			else $str .= "<td></td>";
			$str .= "<td><a class='button' href='metabolite/editor?id={$metabolite->id}'>Edit</a></td>";
			$str .= "</tr>";
		}
		$str .= "</table>";
	}
	return $str;
});

View::registerAdapter("reactionTable",function($data){
	$str = "";
	if($data){
		$str .= "<table class='m_table'><tr><th>Id</th><th>Equation</th><th>KEGG</th><th>Operation</th></tr>";
		foreach($data as $reaction){
			$str .= "<tr>";
			$str .= "<td>{$reaction->id}</td>";
			$str .= "<td>{$reaction->equation}</td>";
			$str .= "<td>{$reaction->KEGG}</td>";
			$str .= "<td><a href='reaction/editor?id={$reaction->id}' class='button'>Edit</a></td>";
			$str .= "</tr>";
		}
		$str .= "</table>";
		return $str;
	}
});

View::registerAdapter("reactionMetabolites",function($data){
	$str = "";
	if($data) {
		foreach($data as $hasMetabolite) {
			$view = View::loadFile("reaction.metabolite.editor.tpl");
			$view->set($hasMetabolite);
			if ($hasMetabolite->metabolite->type == "complex") {
				$view->set("complexEditBtn", "<a href='complex/editor?id={$hasMetabolite->metabolite->id}' class='button'>Edit complex</a>");
			}
			$str .= $view->generate(1,1);
		}
	}
	return $str;
});

View::registerAdapter("reactionCatalysts",function($data){
	$str = "";
	if($data) {
		foreach($data as $hasCatalyst){
			$view = View::loadFile("reaction.catalyst.editor.tpl");
			$view->set($hasCatalyst);
			$view->set("isNovel", $hasCatalyst->novel?"yes":"no");
			if ($hasCatalyst->catalyst->type == "complex") {
				$view->set("complexEditBtn", "<a href='complex/editor?id={$hasCatalyst->catalyst->id}' class='button'>Edit complex</a>");
			}
			$str .= $view->generate(1,1);
		}
		return $str;
	}
});

View::registerAdapter("complexMember", function($data){
	$str = "";
	if ($data) {
		foreach($data as $hasMember) {
			$view = View::loadFile("complexMember.editor.tpl");
			$view->set($hasMember);
			$view->set("memberMarkup", (string) $hasMember->member);
			$str .= $view->generate(1,1);
		}
	}
	return $str;
});

View::registerAdapter("relationCategoryEdit", function($data){
	$str = "<table class='m_table'>";
	$str .= "<tr><th>Category</th><th>Operation</th></tr>";
	if ($data) {
		foreach ($data as $hasCategory) {
			$presentation = [];
			$category = $hasCategory->category;
			$category->fetchParentCategories();
			foreach ($category->parents as $parent) {
				$presentation[] = $parent->title;
			}
			$presentation[] = $category->title;
			$str .= "<tr><td>".implode(" → ", $presentation)."</td><td><button class='delBtn' target='category' id='{$category->id}'>Delete</button></td>";
		}
	}
	$str .= "<tr><td class='category-selector'></td><td><button class='addBtn' target='category'>Add</td></tr>";
	$str .= "</table>";
	return $str;
});

View::registerAdapter("wikiList", function($data){
	$str = "";
	if ($data) {
		$str = "<table class='m_table'><tr><th>Title</th><th>Last edit</th><th>Last author</th>";
		foreach($data as $article) {
			$str .= "<tr>";
			$str .= "<td>".$article->toLinkMarkup()."</td>";
			$str .= "<td>".$article->lastUpdate."</td>";
			$str .= "<td>".$article->lastAuthor."</td>";
			$str .= "</tr>";
		}
		$str .= "</table>";
	}
	return $str;
});


View::registerAdapter("dataSetTable", function($data) {
	$str = "";
	if ($data) {
		$str = "<table class='m_table'><tr><th>Id</th><th>Title</th><th>Type</th><th>Category</th><th>Citation</th><th>Operation</th>";
		foreach($data as $dataSet) {
			$str .= "<tr>";
			$str .= "<td>".$dataSet->id."</td>";
			$str .= "<td>".$dataSet->title."</td>";
			$str .= "<td>".$dataSet->type."</td>";
			$str .= "<td>".$dataSet->category."</td>";
			$str .= $dataSet->pubmed ? "<td>[pubmed|".$dataSet->pubmed."]</td>": "<td></td>";
			$str .= "<td><p><a href='expression/viewer?id={$dataSet->id}' class='button'>View</a></p>";
			if (User::getCurrent()) $str .= "<p><a href='expression/editor?id={$dataSet->id}' class='button'>Edit</a></p></td>";
			else $str .= "</td>";
			$str .= "</tr>";
		}
		$str .= "</table>";
	}
	return $str;
});

View::registerAdapter("memberList", function($data) {
	$str = "";
	if ($data) {
		$str .= "<h2>Members</h2>";
		$str .= "<table class='m_table'>";
		$str .= "<tr><th>Coefficient</th><th>Member<th>Desciption</th></tr>";
		foreach($data as $complexMember) {
			$str .= "<tr>";
			$str .= "<td>".$complexMember->coefficient."</td>";
			$str .= "<td>".$complexMember->member->title."</td>";
			$str .= "<td>".$complexMember->description."</td>";
			$str .= "</tr>";
		}
		$str .= "</table>";
	}
	return $str;
});

View::registerAdapter("interactionTable", function($data) {
	$str = "";
	if ($data) {
		$str = "<table class='m_table'>";
		$str .= "<tr><th>Interaction</th><th>Operation</th></tr>";
		foreach ($data as $interaction) {
			$str .= "<tr>";
			$str .= "<td>".$interaction->prot1->toLinkMarkup()." - ".$interaction->prot2->toLinkMarkup()."</td>";
			$str .= "<td><a href='interaction/editor?id={$interaction->id}' class='button'>Edit</a></td>";
			$str .= "</tr>";
		}
		$str .= "</table>";
	}
	return $str;
});

View::registerAdapter("regulationTable", function($data) {
	$str = "";
	if ($data) {
		$str = "<table class='m_table'>";
		$str .= "<tr><th>Regulator</th><th>Mode</th><th>Regulated</th><th>Operation</th></tr>";
		foreach ($data as $regulation) {
			$str .= "<tr>";
			$str .= "<td>".$regulation->regulator->toLinkMarkup()."</td>";
			$str .= "<td>".$regulation->mode."</td>";
			$str .= "<td>".$regulation->regulated->toLinkMarkup()."</td>";
			$str .= "<td><a href='".lcfirst(get_class($regulation->regulated))."/editor?id={$regulation->regulated->id}' class='button'>Edit</a></td>";
			$str .= "</tr>";
		}
		$str .= "</table>";
	}
	return $str;
});
?>