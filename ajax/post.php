<?php
require_once('../includes/check-authorize.php');
require_once('../includes/functions.php');

if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
	return NULL;
}

// input validation should go here
$agent_id = $_REQUEST['agent_id'];

switch($_REQUEST['type']) {
	// Teamserver Actions
	case 'agent_rm':
		$res = remove_agent($sess_ip, $sess_port, $sess_token, $agent_id);

		echo (($res['success'] == TRUE) ? "Removed agent ".$agent_id : "Could not remove agent ".$agent_id);
		break;

	// Agent Actions
	case 'shell_cmd':
		$cmd = $_REQUEST['cmd'];
		// call for agents
		$res = execute_shell_cmd_agent($sess_ip, $sess_port, $sess_token, $agent_id, $cmd);

		echo (($res['success'] == TRUE) ? $res['task_id'].": Tasked agent $agent_id to run command '$cmd'" : "Could not task agent $agent_id to run command '$cmd'");
		break;
	default:
		echo "Unrecognized/unimplemented API command";
		break;
}
