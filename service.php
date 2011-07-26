<?php
/**
 * see http://docs.moodle.org/dev/Overview_of_the_Moodle_question_engine for documentation
 * TODO:
 * 	- authentication
 *  - quiz rather than single question
 *  - question text including images and media
 *  - all question types not just multiple choice
 *  - all question behaviours not just "deferred feedback"
 *  - look at context
 *  - Moodle renderers - ensure capturing all important functions
 *  - documentation for when new question type is created
 *  - unit testing through OAE front-end
 *  - expose other parts of Moodle functionality (authoring, etc)
 */

require_once('../../config.php');

require_once('lib.php');

$action = required_param('action', PARAM_ACTION);
$questionid = required_param('question_id', PARAM_INT);

$wrapperfn = optional_param('callback', null, PARAM_RAW);

if ($action != 'start') {
	$qubaid = required_param('quba_id', PARAM_INT);
} else {
	$qubaid = null;
}

// TODO add authentication

$server = new local_qpreviewjson($questionid, $qubaid);

switch ($action) {
	case 'get':
		break;
	
	case 'start':
		$server->start_preview();
		break;
	
	case 'process':
		$server->process_submission();
		break;
	
	case 'finish':
		$server->finish_preview();
		break;
		
	default:
		throw new coding_exception("Unknown action '$action'");
}

header('Content-Type: application/json');
//header('Content-Type: text/plain');

$json = json_encode($server->get_current_state());

if ($wrapperfn) {
	echo $wrapperfn . '(' . $json . ')';
} else {
	echo $json;
}
