<?php
    require_once "common/base.php";
    if(isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1):
        $pageTitle = "Save Outreaches";
        //require_once "common/header.php";
        include_once "inc/class.campaign.inc.php";
        $campaignObj = new OutreachCampaign();

        $input = file_get_contents('php://input');
        if($input){
           // exists
            //echo $input;
            $value = json_decode(file_get_contents('php://input'), true);
            //$title = $value[0]['title'];
            //echo $title;
            echo $campaignObj->saveOverlaysInDB($value);
        } else {
           // not exists
            echo "no data passed!";
        }
?>


<?php
    else:
        header("Location: /Outreach/");
        exit;
    endif;
?>
