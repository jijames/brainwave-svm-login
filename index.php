<?php
$submitType = $_GET['submit'];
$username = $_GET['username'];
$type = $_GET['type'];
$predicted = "";

switch ($submitType){
case "":
    break;
case "meteorNotTrain1Multi":
    $predicted = svmPredictMulti("g1.test.scale");
    break;
case "meteorNotTrain2Multi":
    $predicted = svmPredictMulti("g2.test.scale");
    break;
case "rumbaNotTrain1Multi":
    $predicted = svmPredictMulti("t1.test.scale");
    break;
case "rumbaNotTrain2Multi":
    $predicted = svmPredictMulti("t2.test.scale");
    break;
case "":
    break;
case "meteorNotTrain1One":
    $predicted = svmPredictOne("g1.test.scale", $username);
    break;
case "meteorNotTrain2One":
    $predicted = svmPredictOne("g2.test.scale", $username);
    break;
case "rumbaNotTrain1One":
    $predicted = svmPredictOne("t1.test.scale", $username);
    break;
case "rumbaNotTrain2One":
    $predicted = svmPredictOne("t2.test.scale", $username);
    break;
}

function svmPredictMulti( $brainData ){
    exec("./svm-predict $brainData train.multi.scale.model predict.out");
    $predicted = exec("awk '{s+=$1}END{print s/NR}' RS=\" \" predict.out");
    return $predicted;
}

function svmPredictOne($brainData, $username){
    $model = "";
    if ($username == "meteor"){
       $model = "g1.train.single.scale.model";
    } elseif ($username == "rumba"){
       $model = "t1.train.single.scale.model";
    }
    if ($model != ""){
        exec("./svm-predict $brainData $model predict.out");
        $predicted = exec("awk '{s+=$1}END{print s/NR}' RS=\" \" predict.out");
        return $predicted;
    }
}
 ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Brain-Based Login Demo</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
</head>
<body>
<div id="container" class="container">
<?php
if ($type == "multi"){
if ($predicted == 1 && $username == "rumba"){
echo "<div class='alert alert-success'><strong>Success!</strong> Logged in as user rumba with brainwave data.</div>";
} elseif ($predicted == 1 && $username == "meteor"){
echo "<div class='alert alert-danger'><strong>Fail!</strong> Tried to log in as meteor but rumba data detected.</div>";
} elseif ($predicted == 2 && $username == "rumba"){
echo "<div class='alert alert-danger'><strong>Fail!</strong> Tried to log in as rumba but meteor data detected.</div>";
} elseif ($predicted == 2 && $username == "meteor"){
echo "<div class='alert alert-success'><strong>Success!</strong> Logged in as user meteor with brainwave data.</div>";
} else {
echo "<div class='alert alert-warning'>Username or classification unkown. Predicted: $predicted</div>";
}} elseif ($type == "one"){
if ($predicted > 0){
echo "<div class='alert alert-success'><strong>Success!</strong> Logged in as user $username with brainwave data.</div>";
} elseif ($predicted <= 0){
echo "<div class='alert alert-danger'><strong>Fail!</strong> Tried to log in as $username but other data detected.</div>";
}
}
?>
<h2>Brain-Based Login Demo</h2>
<div id="multiClassLoginUsername">
Username:
<input id="username" type="text" class="form-control"></input>
<ul>
<li><strong>Login with Brain Data, Multi-Class SVM and Username</strong></li>
<li>Username: <strong>meteor</strong></li>
<li>Replay g1 Brain Data: <a id="meteorNotTrain1Multi">NotTrain1</a> | <a id="meteorNotTrain2Multi">NotTrain2</a></li>
<li>Username: <strong>rumba</strong></li>
<li>Replay t1 Brain Data:  <a id="rumbaNotTrain1Multi">NotTrain1</a> | <a id="rumbaNotTrain2Multi">NotTrain2</a></li>
</ul>
<hr />
<ul>
<li><strong>Login with Brain Data, One-Class SVM and Username</strong></li>
<li>Username: <strong>meteor</strong></li>
<li>Replay g1 Brain Data: <a id="meteorNotTrain1One">NotTrain1</a> | <a id="meteorNotTrain2One">NotTrain2</a></li>
<li>Username: <strong>rumba</strong></li>
<li>Replay t1 Brain Data:  <a id="rumbaNotTrain1One">NotTrain1</a> | <a id="rumbaNotTrain2One">NotTrain2</a></li>
</ul>
</div>


</div>
<script src="https://code.jquery.com/jquery-3.1.1.min.js" integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8=" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<script>
    $("a").on('click', function(){
        var classType = "";
        if ($(this).attr('id').indexOf('Multi') != -1){
            $classType = "multi";
        } else {
            $classType = "one";
        }
        var username = $(this).closest("div").children().first().val();
        if (username == ""){
            $(this).closest("div").append('<div class="alert alert-danger" role="alert">Enter a username. Usernames: meteor and rumba are valid users</div>');
        } else {
            $(this).closest("div").append('<div class="alert alert-info" role="alert">Data Submitted. Classification will take a while.</div>');
            window.location.replace("index.php?submit=" + $(this).attr('id') + "&username=" + username + "&type=" + $classType);
        }
    });
</script>
</body>
</html>

