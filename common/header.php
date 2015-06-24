<!DOCTYPE html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />  
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />

    <title>Outreach Tracker | <?php echo $pageTitle ?></title>

    <link rel="stylesheet" href="common/styles.css" type="text/css" />
    <!--<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />-->

    <!--<script type='text/javascript' src='//ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js?ver=1.3.2'></script>-->
</head>

<body>

    <header>


        <h1>Outreach Tracker</h1>


            <div id="control">
<?php
    if(isset($_SESSION['LoggedIn']) && isset($_SESSION['Username'])
        && $_SESSION['LoggedIn']==1):
        echo $_SESSION['Username'];
?>
                <p><a href="logout.php" class="button">Log out</a> <a href="account.php" class="button">Your Account</a></p>
<?php else: ?>
                <p><a class="button" href="signup.php">Sign up</a> &nbsp; <a class="button" href="login.php">Log in</a></p>
<?php endif; ?>

            </div>
    </header>
