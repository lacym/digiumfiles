<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Digium XML Generator</title>

    <!-- Bootstrap -->
    <link href="<?php echo $_SERVER['REQUEST_URI']; ?>/css/bootstrap.min.css" rel="stylesheet">


</head>
<body>
<!-- <div class="jumbotron"> -->
<center><h1><span class="label label-default">Digium XML Generator</span></h1></center>
<center><h2>Designed by Aspendora Technologies</h2></center>
<center><h2><a href="https://www.aspendora.com">www.aspendora.com</a></h2></center>
<div class="alert alert-info" role="alert">Processing files...<?php echo $_SERVER['REQUEST_URI']; ?></div>


<?php //include 'digiumxml.php'; ?>
<?php include 'testbranch.php'; ?>
<div class="alert alert-success" role="alert">Finished...</div>
<!-- </div> -->

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="<?php echo $_SERVER['REQUEST_URI']; ?>/js/bootstrap.min.js"></script>
</body>
</html>

