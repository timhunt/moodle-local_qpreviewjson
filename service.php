<?php
require_once('../../config.php');

require_once('lib.php');

$action = required_param('action', PARAM_ACTION);
$questionid = required_param('question_id', PARAM_INT);

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
echo json_encode($server->get_current_state());
