<?php
// include files
require_once("includes/check-authorize.php");
require_once("includes/functions.php");

function sanitizeArr($inArr) {
	$out = array_map("addslashes", $inArr);
	$out = array_map("htmlentities", $out);

	return $out;
}

$arr_result = get_all_listeners($sess_ip, $sess_port, $sess_token);
if(!empty($arr_result))
{
    $listeners = $arr_result["listeners"];
}
else
{
    $listeners = array();
}

$arr_result = get_all_agents($sess_ip, $sess_port, $sess_token);
if(!empty($arr_result))
{
    $agents = $arr_result["agents"];
}
else
{
    $agents = array();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Empire: Dashboard</title>
	<?php @require_once("includes/head-section.php"); ?>
</head>

<body>
	<style type="text/css">
.list-btn {
    width: 100%;
    text-align: left;
    border: 0px;
    border-radius: 0;
    background-color: transparent;
    color: black;
    font-weight: bold;
}
div.agent-interact-body { height: 500px; }
	</style>
    <div class="container">
        <?php @require_once("includes/navbar.php"); ?>
        <h1>Eden WebUI</h1>
        <br>
	<div class="row">
		<div class="col-lg-4">
			<div class="panel-group">
			    <div class="panel panel-primary">
				<div class="panel-heading">Listeners</div>
				<div id="listeners-body" class="panel-body" data-hash="undef">
					Loading...
				</div>
			    </div>
			    <br>
			    <div class="panel panel-primary">
				<div class="panel-heading">Agents</div>
				<div id="agents-body" class="panel-body" data-hash="undef">
					Loading...
				</div>
			    </div>
			</div>
		</div>
		<div class="col-lg-8">
			<div class="panel panel-primary">
				<div class="panel-heading">Interact</div>
				<div class="panel-body agent-interact-body">
				</div>
<script language="javascript">
var prompt_user = "<?= $_SESSION['empire_user'] ?>";
var svcs = [<?php foreach($agents as $agent) { echo "'".$agent['name']."', "; } ?>];
var current_svc = 0;
var commands;
</script>
			</div>
		</div>
	</div>
    </div>
    <script type="text/javascript" src="js/dashboard.js"></script>
    <?php @require_once("includes/footer.php"); ?>
</body>
</html>
