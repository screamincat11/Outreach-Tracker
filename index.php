<?php
    require_once "common/base.php";
    require_once "common/header.php"; ?>

<div id="main">

   <noscript>This site just doesn't work, period, without JavaScript</noscript>

<?php
    if(isset($_SESSION['LoggedIn']) && isset($_SESSION['Username'])
        && $_SESSION['LoggedIn']==1):
        
        require_once 'inc/class.campaign.inc.php';
        $campaigns = new OutreachCampaign($db);

        $temp = $campaigns->loadCampaignsByUser();

        $dom = new DOMDocument("1.0");
        $node = $dom->createElement("overlays");
        $parnode = $dom->appendChild($node);

        $campaign = 1;

        $sql = "SELECT campaign.campaign_type, outreach.ID, outreach.title, outreach.outreach_date, outreach.comments, outreach.purpose, outreach.type, overlay_points.point_number, overlay_points.lat, overlay_points.lng, outreach.radius FROM campaign JOIN outreach ON outreach.campaign_ID = campaign.ID JOIN overlay_points ON overlay_points.outreach_ID = outreach.ID WHERE outreach.campaign_ID = :campaign ORDER BY outreach.ID, overlay_points.point_number";  

        if($stmt = $db->prepare($sql)) {
            $stmt->bindParam(":campaign", $campaign, PDO::PARAM_INT);
            $stmt->execute();
            //$row = $stmt->fetch();
            $overlayID = -1;
            $node = null;
            while($row = $stmt->fetch())
            {
               // $LID = $row['ListID'];
                //$URL = $row['ListURL'];
                //echo $this->formatListItems($row,   $order);
                //echo $row['campaign_type'];
                ///*
                if($overlayID !== $row['ID']) {
                    $node = $dom->createElement("overlay");
                    $newnode = $parnode->appendChild($node);
                    $newnode->setAttribute("ID", $row['ID']);
                    $newnode->setAttribute("title", $row['title']);
                    $newnode->setAttribute("outreach_date", $row['outreach_date']);
                    $newnode->setAttribute("title", $row['title']);
                    $newnode->setAttribute("comments", $row['comments']);
                    $newnode->setAttribute("purpose", $row['purpose']);
                    $newnode->setAttribute("type", $row['type']);
                    $newnode->setAttribute("radius", $row['radius']);
                    $overlayID = $row['ID'];
                }
                $node2 = $dom->createElement("coords");
                $coords = $node->appendChild($node2);
                $coords->setAttribute("point_number", $row['point_number']);
                $coords->setAttribute("lat", $row['lat']);
                $coords->setAttribute("lng", $row['lng']);
                 //*/
            }
            $stmt->closeCursor();
        }
        //echo $dom->saveXML();

    /*
        $sql = "SELECT COUNT(Username) AS theCount
                FROM users
                WHERE Username=:email";
        if($stmt = $db->prepare($sql)) {
            $stmt->bindParam(":campaign", $campaign, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch();
            if($row['theCount']!=0) {
                return "<h2> Error </h2>"
                    . "<p> Sorry, that email is already in use. "
                    . "Please try again. </p>";
            }
            if(!$this->sendVerificationEmail($u, $v)) {
                return "<h2> Error </h2>"
                    . "<p> There was an error sending your"
                    . " verification email. Please "
                    . "<a href='mailto:help@coloredlists.com'>contact "
                    . "us</a> for support. We apologize for the "
                    . "inconvenience. </p>";
            }
            $stmt->closeCursor();
     */
?>
                <p>You are ready to do great things!</p>
<?php else: ?>
                <p>You are NOT ready to do great things!</p>
<?php endif; ?>
   <!-- IF LOGGED IN -->

          <!-- Content here -->

   <!-- IF LOGGED OUT -->

          <!-- Alternate content to put in index.php here -->

</div>


<?php require_once "common/footer.php"; ?>
