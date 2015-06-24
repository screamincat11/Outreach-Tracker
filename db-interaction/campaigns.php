<?php
session_start();
include_once "../inc/constants.inc.php";
include_once "../inc/class.campaign.inc.php";
$campaignObj = new OutreachCampaign();
/*function PHPconsole($data) {
    if(is_array($data) || is_object($data))
	{
		echo("<script>console.log('PHP: ".json_encode($data)."');</script>");
	} else {
		echo("<script>console.log('PHP: ".$data."');</script>");
	}
}*/
//PHPconsole("campaigns");
if(!empty($_POST['action'])
&& isset($_SESSION['LoggedIn'])
&& $_SESSION['LoggedIn']==1)
{
    switch($_POST['action'])
    {
        case 'getoverlays':
            echo $campaignObj->generateCampaignXML();
            break;
        case 'saveoverlays':
            echo $campaignObj->saveOverlaysInDB();
            break;
        case 'update':
            $campaignObj->updateListItem();
            break;
        case 'sort':
            $campaignObj->changeListItemPosition();
            break;
        case 'color':
            echo $campaignObj->changeListItemColor();
            break;
        case 'done':
            echo $campaignObj->toggleListItemDone();
            break;
        case 'delete':
            echo $campaignObj->deleteListItem();
            break;
        default:
            header("Location: /Outreach/");
            break;
    }
}
else
{
    header("Location: /Outreach/");
    exit;
}
?>
