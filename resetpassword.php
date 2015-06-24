<?php
    require_once "common/base.php";

    if(isset($_GET['v']) && isset($_GET['e']))
    {
        require_once "inc/class.users.inc.php";
        $users = new ColoredListsUsers($db);
        $ret = $users->verifyAccount();
    }
    elseif(isset($_POST['v']))
    {
        require_once "inc/class.users.inc.php";
        $users = new ColoredListsUsers($db);
        $status = $users->updatePassword() ? "changed" : "failed";
        header("Location: account.php?password=$status");
        exit;
    }
    else
    {
        header("Location: login.php");
        exit;
    }

    $pageTitle = "Reset Your Password";
    require_once "common/header.php";

    if(isset($ret[0])):
        echo isset($ret[1]) ? $ret[1] : NULL;

        if($ret[0]<3):
?>

        <h2>Reset Your Password</h2>

        <form method="post" action="accountverify.php">
            <div>
                <label for="p">Choose a New Password:</label>
                <input type="password" name="p" id="p" /><br />
                <label for="r">Re-Type Password:</label>
                <input type="password" name="r" id="r" /><br />
                <input type="hidden" name="v" value="<?php echo $_GET['v'] ?>" />
                <input type="submit" name="verify" id="verify" value="Reset Your Password" />
            </div>
        </form>

<?php
        endif;
    else:
        echo '<meta http-equiv="refresh" content="0;/Outreach/">';
    endif;

    require_once 'common/footer.php';
?>
