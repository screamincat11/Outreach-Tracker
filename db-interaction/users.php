<?php

session_start();

require_once "../inc/constants.inc.php";
require_once "../inc/class.users.inc.php";
$userObj = new ColoredListsUsers();

if(!empty($_POST['action'])
&& isset($_SESSION['LoggedIn'])
&& $_SESSION['LoggedIn']==1)
{
    switch($_POST['action'])
    {
        case 'changeemail':
            $status = $userObj->updateEmail() ? "changed" : "failed";
            header("Location: /Outreach/account.php?email=$status");
            break;
        case 'changepassword':
            $status = $userObj->updatePassword() ? "changed" : "nomatch";
            header("Location: /Outreach/account.php?password=$status");
            break;
        case 'deleteaccount':
            $userObj->deleteAccount();
            break;
        default:
            header("Location: /");
            break;
    }
}
elseif($_POST['action']=="resetpassword")
{
    if($resp=$userObj->resetPassword()===TRUE)
    {
        header("Location: /Outreach/resetpending.php");
    }
    else
    {
        echo $resp;
    }
    exit;
}
else
{
    header("Location: /Outreach/");
    exit;
}

?>
