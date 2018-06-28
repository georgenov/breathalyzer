<?php
define('VOCABULARY_FILE', __DIR__.'/'.'desc/vocabulary.txt');
require_once __DIR__."/Breathalyzer.php";
array_shift($argv);
if(!$argv) {
	die('Please provide text file path!');
}
if(!is_file($argv[0])) {
	die('File does not exists');
}

//Get files
$words = file_get_contents($argv[0]);
$vocabulary = file_get_contents(VOCABULARY_FILE);
try
{
	//Initializing class
	$breathalyzer = new Breathalyzer($words, $vocabulary);
	$summary = $breathalyzer->getDistanceSummary();
} catch (\Exception $e) {
	die('ERROR: '.$e->getMessage().PHP_EOL.$e->getTraceAsString());
}
$time_finish = microtime(true);
echo $summary;
echo PHP_EOL;