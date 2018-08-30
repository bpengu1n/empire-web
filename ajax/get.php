<?php
require_once('../includes/check-authorize.php');
require_once('../includes/functions.php');

if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
	return NULL;
}

ob_start();

if ($_REQUEST['type'] == 'all_agents') {
	// call for agents
	$res = get_all_agents($sess_ip, $sess_port, $sess_token)['agents'];
	$resHash = crc32(serialize($res));

	if ($resHash != $_REQUEST['hash']) {
?>
<div class="row" style="text-align: center;">
<span class="col-xs-6">Agent Name</span>
<span class="col-xs-6">Last Check-in</span>
</div>
<?php
	    foreach ($res as $agent) { 
?>
<button type="button" class="btn btn-primary btn-xs list-btn agent-btn"
		data-name="<?= $agent['name'] ?>"
		data-arch="<?= $agent['arch']; ?>"
		data-ext_ip="<?= $agent['external_ip']; ?>"
		data-int_ip="<?= $agent['internal_ip']; ?>"
		data-lastseen="<?= $agent['lastseen_time']; ?>"
		data-os_details="<?= $agent['os_details']; ?>"
		data-plugins="<?= $agent['plugins']; ?>"
		data-process="<?= $agent['process_name']; ?>"
		data-username="<?= $agent['username']; ?>">
<span class="col-xs-6"><?= $agent['name'] ?></span>
<span class="col-xs-6" style="text-align: right;"><?= ($agent['lastseen_time'] != '') ? formatTimestamp($agent['lastseen_time']) : '----' ?></span>
</button>
<?php     } ?>
<script>
loadAgentContexts(); 
var svcs = [<?php foreach($res as $agent) { echo "'".$agent['name']."', "; } ?>];
commands['svc.interact'].args = svcs;
</script>
<?php
	}	
} else if ($_REQUEST['type'] == 'all_listeners') {
	// call for listeners
	$res = get_all_listeners($sess_ip, $sess_port, $sess_token)['listeners'];
	$resHash = crc32(serialize($res));

	if ($resHash != $_REQUEST['hash']) {
?>
<div class="row" style="text-align: center;">
<span class="col-xs-6">Name</span>
<span class="col-xs-3">Type</span>
<span class="col-xs-3">Port</span>
</div>
<?php
	    foreach ($res as $listener) {
?>
<button type="button" class="btn btn-primary btn-xs list-btn listener-btn"
		data-name="<?= $listener['name'] ?>"
		data-module="<?= $listener['module'] ?>"
		data-category="<?= $listener['listener_category'] ?>"
<?php		foreach ($listener['options'] as $nm => $field) { ?>
		data-<?= str_replace(" ","_",strtolower($nm)) ?>="<?= $field['Value'] ?>"
<?php		} ?>>
<span class="col-xs-6"><?= $listener['name'] ?></span>
<span class="col-xs-3"><?= $listener['module'] ?></span>
<span class="col-xs-3" style="text-align: right;"><?= $listener['options']['Port']['Value'] ?></span>
</button>
<?php
	    }
?>
<script>loadListenerContexts()</script>
<?php
	}
}

$resArr['hash'] = $resHash;
$resArr['html'] = ob_get_clean();
if ($resArr['html'] > '') 
	echo json_encode($resArr);
else
	return NULL;
