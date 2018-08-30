<?php
// include files
require_once("includes/check-authorize.php");
require_once("includes/functions.php");

$listeners = get_loaded_listeners($sess_ip, $sess_port, $sess_token);

$listener_options = array();

foreach ($listeners as $name => $listener) {
    $listener_options[ $name ] = '';
    if(array_key_exists("options", $listener))
    {
        $listener_options[ $name] .= '<table class="table table-hover table-striped"><thead><tr><th>Name</th>';
        foreach($listener["options"] as $key => $value)
        {
            foreach($value as$key1 => $value1)
            {
                $key1 = ucfirst(htmlentities($key1));
                $listener_options[ $name] .= "<th>$key1</th>";
            }
            break;
        }
        $listener_options[ $name ] .= '</thead><tbody>';
        foreach($listener["options"] as $key => $value)
        {
            $key = htmlentities($key);
            if($key  != "Name")
            {
                $listener_options[ $name ] .= "<tr><td>$key</td>";
            }
            foreach($value as $key1 => $value1)
            {
                if($key  != "Name")
                {
                    $value1 = htmlentities($value1);
                    if($key1 == "Value")
                    {
                        $listener_options[ $name ] .= "<td><div class='form-group'><input type='text' class='form-control' id='$key' name='$key' value='$value1'></div></td>";
                    }
                    elseif($key1 == "Required")
                    {
                        if($value1 == True)
                        {
                            $listener_options[ $name ] .= "<td>Yes</td>";
                        }
                        else
                        {
                            $listener_options[ $name ] .= "<td>No</td>";
                        }
                    }
                    else
                    {
                        $listener_options[ $name ] .= "<td>".$value1."</td>";
                    }
                }
            }
            $listener_options[ $name ] .= "</tr>";
        }
        $listener_options[ $name ] .= '</tbody></table>';
    }
    else
    {
        $listener_options[ $name ] = "<div class='alert alert-danger'>Unexpected response</div>";
    }
}


$empire_create_listener = "";
if(isset($_POST) && !empty($_POST))
{
    $arr_data = array();
    //Remove "CertPath" item from $_POST if it is not set
    //If it exists and listener is of HTTP then it is converted into HTTPS without any error
    if(isset($_POST["CertPath"]) && strlen($_POST["CertPath"])<=0)
    {
        unset($_POST["CertPath"]);
    }
    foreach($_POST as $key => $value)
    {
        if ($key == 'listenerType') // Retrieve listener type for API call
            $listener_type = html_entity_decode(urldecode($value));
        else
            $arr_data[$key] = html_entity_decode(urldecode($value));
    }
    
    $arr_result = create_listener($sess_ip, $sess_port, $sess_token, $listener_type, $arr_data);
    if(array_key_exists("success", $arr_result))
    {
        if($arr_result["success"] == True)
        {
            if(array_key_exists("msg", $arr_result))
            {
                $empire_create_listener = "<div class='alert alert-success'><span class='glyphicon glyphicon-ok'></span> ".ucfirst(htmlentities($arr_result["msg"]))."</div>";
            }
            else
            {
                $empire_create_listener = "<div class='alert alert-success'><span class='glyphicon glyphicon-ok'></span> Listener created successfully.</div>";
            }
        }
        else
        {
            if(array_key_exists("msg", $arr_result))
            {
                $empire_create_listener = "<div class='alert alert-danger'><span class='glyphicon glyphicon-remove'></span> ".ucfirst(htmlentities($arr_result["msg"]))."</div>";
            }
            else
            {
                $empire_create_listener = "<div class='alert alert-danger'><span class='glyphicon glyphicon-remove'></span> Listener creation failed.</div>";
            }
        }
    }
    elseif(array_key_exists("error", $arr_result))
    {
        $empire_create_listener = "<div class='alert alert-danger'><span class='glyphicon glyphicon-remove'></span> ".ucfirst(htmlentities($arr_result["error"]))."</div>";
    }
    else
    {
        $empire_create_listener = "<div class='alert alert-danger'><span class='glyphicon glyphicon-remove'></span> Unexpected response.</div>";
    }

    $activeIdx = array_search($listener_type, array_keys($listeners));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>WebUI: Create Listener</title>
	<?php @require_once("includes/head-section.php"); ?>
</head>
<body>
    <div class="container">
        <?php @require_once("includes/navbar.php"); ?>
        <br>
        <div class="panel-group">
            <div class="panel panel-primary">
                <div class="panel-heading">Create Listener</div>
                <div class="panel-body">
                    <div id="tabs">
                        <ul>    <!-- TABS BEGIN -->
<?php foreach (array_keys($listeners) as $name) { ?>
                            <li>
                                <a href="#<?= $name ?>"><?= $name ?></a>
                            </li>
<?php } ?>
                        </ul>   <!-- TABS END -->
<?php foreach (array_keys($listeners) as $name) { ?> 
                        <div id="<?= $name ?>">
                            <form role="form" method="post" action="create-listener.php" class="form-inline">
                                <input type="hidden" name="listenerType" value="<?= $name ?>" />
                                <div class="form-group">
                                    <input type="text" class="form-control" id="listener-name" placeholder="Listener Name" name="Name" required />
                                </div>
                                <button type="submit" class="btn btn-success">Create</button><?php if ($listener_type && $name == $listener_type) echo $empire_create_listener; ?>
                                <br><br>
                                <b>Additional Options:</b>
                                <br><br>
                                <?php echo $listener_options[$name]; ?>
                            </form>
                        </div>
<?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <br>
    </div>
    <script>
    $( function() {
        $('div#tabs').tabs({
            active: <?= (($activeIdx)?$activeIdx:0) ?>
        });
    })
    </script>
    <?php @require_once("includes/footer.php"); ?>
</body>
</html>
