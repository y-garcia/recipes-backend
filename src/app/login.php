<?php

require_once("header.inc.php");

if ($_SESSION['loggedIn'] == 1) {
    header('Location: .');
    die();
}

?>
<!doctype html>

<html lang="en">

<head>
    <title>Recipes</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
    <link rel="stylesheet" href="css/styles.css" type="text/css"/>
</head>

<body>

<p id="login-button">
    <a href="<?php echo $gAuthUrl; ?>">Sign in</a>
</p>

</body>

</html>