<?php
require_once('../includes/check-authorize.php');
require_once('../includes/functions.php');

if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
	return NULL;
}

// input validation should go here
$agent_id = $_REQUEST['agent_id'];
$listener_id = $_REQUEST['listener_id'];

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
	case 'listener_rm':
		$res = kill_listener($sess_ip, $sess_port, $sess_token, $listener_id);

		echo (($res['success'] == TRUE) ? "Stopped listener ".$listener_id : "Could not stop listener ".$listener_id);
		break;
	case 'file_download':
		$path = $_REQUEST['path'];
		$res = agent_file_download($sess_ip, $sess_port, $sess_token, $agent_id, $path);

		echo (($res['success'] == TRUE) ? "Sent command to download file ".$path : "Could not download file ".$path);
		break;
	case 'file_upload':
		$path = $_REQUEST['path'];
		$res = agent_file_upload($sess_ip, $sess_port, $sess_token, $agent_id, $path);
		
		echo (($res['success'] == TRUE) ? "Sent command to upload file ".$path : "Could not upload file ".$path);
		break;
	default:
		echo "Unrecognized/unimplemented API command";
		break;
}
