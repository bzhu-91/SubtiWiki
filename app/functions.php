<?php
namespace ApplicationFunctions;

require_once 'app/ViewAdapters.php';


function index ($input, $accpet, $method) {
	$view = \Kiwi\View::loadFile("layout2.tpl");
	\Statistics::increment("index");
	$view->set([
		"headerTitle" => $GLOBALS["SITE_NAME"],
		"content" => "{{index.tpl}}",
		"styles" => ["index"],
		"jsAfterContent" => ["index"]
	]);
	echo $view->generate(1,1);
}

function FAQ () {
	$view = \Kiwi\View::loadFile("layout2.tpl");
	$view->set([
		"content" => "{{FAQ.tpl}}",
	]);
	echo $view->generate(1,1);
}

function debug ($input, $accept, $method) {
	$reactions = \Reaction::getAll(1);
	foreach ($reactions as $reaction) {
		if (!$reaction->updateEquation()) {
			\Kiwi\Log::debug("R".$reaction->id.\Kiwi\Application::$conn->lastError);
		}
	}
	\Log::debug("done");
}

function exports () {
	$view = \Kiwi\View::loadFile("layout1.tpl");
	$view->set([
		"pageTitle" => "Exports",
		"title" => "Exports",
		"content" => "{{table:exports}}",
		"exports" => [
			["Item", "Link"],
			["Regulations", "<a href='regulation/exporter' download='regulations-".date("Y-m-d")."'>here</a>"],
			["Operons", "<a href='operon/exporter' download='operons-".date("Y-m-d")."'>here</a>"],
			["Interaction", "<a href='interaction/exporter' download='interaction-".date("Y-m-d")."'>here</a>"],
			["Categories", "<a href='category/exporter' download='categories-".date("Y-m-d")."'>here</a>"],
			["Gene categories", "<a href='category/assignmentExporter' download='geneCategories-".date("Y-m-d").".csv'>here</a>"],
			["Genes", "<a href='gene/exporter'>here</a>"]
		],
		"showFootNote" => "none"
	]);
	echo $view->generate(1,1);
}

function statistics () {
	$view = \Kiwi\View::loadFile("layout1.tpl");
	\Statistics::increment("statistics");
	$view->set([
		"title" => "Statistics",
		"pageTitle" => "Statistics",
		"content" => "{{statistics.tpl}}",
		"geneCount" => \Gene::count(),
		"operonCount" => \Operon::count(),
		"interactionCount" => \Gene::hasPrototype("interaction")->count(),
		"regulationCount" => \Statistics::getCount("MaterialViewGeneRegulation"),
		"regulonCount" => \Regulon::count(),
		"referenceCount" => \Pubmed::count(),
		"editsCount" => \History::count(),
		"userCount" => \User::count(),
		"indexCount" => \Statistics::get("index"),
		"geneSum" => \Statistics::getSum("Gene"),
		"categorySum" => \Statistics::getSum("Category") + \Statistics::get("categoryIndex"),
		"regulonSum" => \Statistics::getSum("Regulon") + \Statistics::get("regulonIndex"),
		"regulationCount" => \Statistics::get("regulationBrowser"),
		"interactionCount" => \Statistics::get("interactionBrowser"),
		"pathwayCount" => \Statistics::get("pathwayBrowser"),
		"genomeCount" => \Statistics::get("genomeBrowser"),
		"expressionCount" => \Statistics::get("expressionBrowser"),
		"geneExports" => \Statistics::get("geneExport"),
		"interactionExports" => \Statistics::get("interactionExport"),
		"regulationExports" => \Statistics::get("regulationExport"),
		"operonExports" => \Statistics::get("operonExport"),
		"categoryExports" => \Statistics::get("categoryExport"),
		"geneCategoryExports" => \Statistics::get("geneCategoryExport"),
		"showFootNote" => "none",
		"statistics" => \Statistics::get("statistics")
	]);
	echo $view->generate(1,1);
}

?>