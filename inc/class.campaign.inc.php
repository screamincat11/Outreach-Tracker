<?php

/**
 * Handles user interactions within the app
 *
 * PHP version 5
 *
 * @author Jason Lengstorf
 * @author Chris Coyier
 * @copyright 2009 Chris Coyier and Jason Lengstorf
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 *
 */
class OutreachCampaign
{

    /**
     * The database object
     *
     * @var object
     */
    private $_db;

    /**
     * Checks for a database object and creates one if none is found
     *
     * @param object $db
     * @return void
     */
    public function __construct($db=NULL)
    {
        if(is_object($db))
        {
            $this->_db = $db;
        }
        else
        {
            $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME;
            $this->_db = new PDO($dsn, DB_USER, DB_PASS);
        }
    }

    /**
     * Loads all list items associated with a user ID
     *
     * This function both outputs <li> tags with list items and returns an
     * array with the list ID, list URL, and the order number for a new item.
     *
     * @return array    an array containing list ID, list URL, and next order
     */
    public function loadCampaignsByUser()
    {
        $sql = "SELECT campaign.ID, title, campaign_type, start_date, end_date, campaign.comments 
            FROM campaign 
            JOIN campaign_owner ON campaign.ID = campaign_owner.campaign_ID 
            JOIN users ON users.UserID = campaign_owner.user_ID 
            WHERE campaign_owner.user_ID =( 
                SELECT users.UserID FROM users WHERE users.Username=:user)";
        
        if($stmt = $this->_db->prepare($sql))
        {
            $stmt->bindParam(':user', $_SESSION['Username'], PDO::PARAM_STR);
            $stmt->execute();

            //$order = 0;
            echo "<table><tr><th>Camaign</th><th>Type</th><th>Start</th><th>End</th><th>Comments</th><th>Actions</th></tr>";
            while($row = $stmt->fetch())
            {
                echo "<tr><td>{$row['title']}</td><td>{$row['campaign_type']}</td><td>{$row['start_date']}</td><td>{$row['end_date']}</td><td>{$row['comments']}</td><td><form method='post' action='viewmap.php' id='viewmap'><input type='hidden' name='campaignID' value='{$row['ID']}' /><input type='submit' name='buttonViewMap' id='buttonViewMap' value='View' /></form></td></tr>";
                $CID = $row['ID'];
                //$URL = $row['ListURL'];
                //echo $this->formatListItems($row,   $order);
            }
            echo "</table>";
            $stmt->closeCursor();

            // If there aren't any list items saved, no list ID is returned
/*
            if(!isset($LID))
            {
                $sql = "SELECT ListID, ListURL
                        FROM lists
                        WHERE UserID = (
                            SELECT UserID
                            FROM users
                            WHERE Username=:user
                        )";
                if($stmt = $this->_db->prepare($sql))
                {
                    $stmt->bindParam(':user', $_SESSION['Username'], PDO::PARAM_STR);
                    $stmt->execute();
                    $row = $stmt->fetch();
                    $LID = $row['ListID'];
                    $URL = $row['ListURL'];
                    $stmt->closeCursor();
                }
            }
 */
        }
        else
        {
            echo "tttt<li> Something went wrong. ", $db->errorInfo, "</li>n";
        }

        return array($CID);//, $URL, $order);
    }
    public function generateCampaignXML()
    {
        if(isset($_POST['cID'])) {
            //echo $_POST['campaignID'];
            $dom = new DOMDocument("1.0");
            $node = $dom->createElement("overlays");
            $parnode = $dom->appendChild($node);


            $sql = "SELECT campaign.campaign_type, outreach.ID, outreach.title, outreach.outreach_date, outreach.comments, outreach.purpose, outreach.type, overlay_points.point_number, overlay_points.lat, overlay_points.lng, outreach.radius FROM campaign JOIN outreach ON outreach.campaign_ID = campaign.ID JOIN overlay_points ON overlay_points.outreach_ID = outreach.ID WHERE outreach.campaign_ID = :campaign ORDER BY outreach.ID, overlay_points.point_number";  

            if($stmt = $this->_db->prepare($sql)) {
                $stmt->bindParam(":campaign", $_POST['cID'], PDO::PARAM_INT);
                $stmt->execute();
                //$row = $stmt->fetch();
                $overlayID = -1;
                $node = null;
                while($row = $stmt->fetch())
                {
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
                }
                $stmt->closeCursor();
            }
            return $dom->saveXML();
        } 
        else {
            return "Somthing went wrong!!";
        }
    }

    public function saveOverlaysInDB($data)
    {
        //$value = json_decode(file_get_contents('php://input'));    //
        //return $data[0]['title'] . " -- " . count($data);
        $sql = "INSERT INTO `outreach_tracker`.`outreach` (`ID`, `outreach_date`, `title`, `comments`, `radius`, `purpose`, `type`, `campaign_ID`) VALUES (NULL, '2015-06-03', 'blah', 'blahblah', NULL, 'complete', 'POLYGON', '3')";
        $sql = "UPDATE `outreach_tracker`.`outreach` SET `outreach_date` = '2015-06-05', `title` = 'blah!', `comments` = 'blahblah!!', `purpose` = 'target', `type` = 'MARKER' WHERE `outreach`.`ID` = 5";
        //"DELETE FROM `outreach_tracker`.`outreach` WHERE `outreach`.`ID` = 5"
        //INSERT INTO table (id,Col1,Col2) VALUES (1,1,1),(2,2,3),(3,9,3),(4,10,12) ON DUPLICATE KEY UPDATE Col1=VALUES(Col1),Col2=VALUES(Col2);
        $delsql = "DELETE FROM outreach_tracker.outreach WHERE outreach.ID IN (";
        $delIDs = "";
        $delCounter = 0;

        $insupdsql = "INSERT INTO outreach (`ID`, `outreach_date`, `title`, `comments`, `radius`, `purpose`, `type`, `campaign_ID`) VALUES ";
        $insupdsqlValues = "";
        $insupdsql2 = " ON DUPLICATE KEY UPDATE outreach_date=VALUES(outreach_date), title=VALUES(title), comments=VALUES(comments), radius=VALUES(radius), purpose=VALUES(purpose), type=VALUES(type), campaign_ID=VALUES(campaign_ID)"; 
        $insupdCounter = 0;

        $insertCounter = 0;

        for ($i = 0, $l = count($data); $i < $l; $i++) {
            if ($data[$i]["deleted"] == true) {
                if ($data[$i]["ID"] != -1) {
                    // an existing row in outreach needs to be deleted
                    $tempDelID = $data[$i]["ID"];
                    if ($delCounter === 0) {
                        $delIDs .= $tempDelID;
                    } else {
                        $delIDs .= ", " . $tempDelID;
                    }
                    $delCounter++;
                } else {
                    // do nothing, wasn't saved in outreach table
                }
            } else { 
                if ($data[$i]["changed"] == true) {
                    // create INSERT/UPDATE query
                    $tempID = $data[$i]["ID"];
                    if ($tempID == -1) {
                        $tempID = "NULL";
                        $insertCounter++;
                    }
                    $tempinsupdsqlValues = "(".$tempID.",'".$data[$i]["overlayDate"]
                                        ."','".$data[$i]["title"]."','".$data[$i]["comments"]
                                        ."',".$data[$i]["radius"].",'".$data[$i]["purpose"]
                                        ."','".$data[$i]["type"]."',".$data[$i]["campaign"].")";
                    if ($insupdCounter === 0) {
                        $insupdsqlValues =  $tempinsupdsqlValues;
                    } else {
                        $insupdsqlValues .= ", " . $tempinsupdsqlValues; 
                    }
                    $insupdCounter++;

                    // points!
                    //INSERT INTO `outreach_tracker`.`overlay_points` (`outreach_ID`, `point_number`, `lat`, `lng`) VALUES ('9', '0', '10', '11');
                    // Insert outreach
                    // Get new outreach ID
                    // point_number = vertices array index: 0, 1, ...
                    //$dbh->query($sql);
                    //echo $dbh->lastInsertId();  
                    //   = 20, and I inserted 5 elements, then IDs are 20, 19, 18, ... 16
                    $pointsStr = $data[$i]["vertices"][0][0] . "," . $data[$i]["vertices"][0][1];

                }
                else {
                    //nothing has changed or been added--do nothing
                }
            }
        }
// run insupd querry, get last ID
        $lastID = 20; //->lastInsertID();
        $tempCounter = 1;
        for ($i = 0, $l = count($data); $i < $l; $i++) {
            if ($data[$i]["deleted"] !== true && $data[$i]["ID"] === -1 && $insertCounter!==0) {
                $data[$i]["ID"] = $lastID - $insertCounter + $tempCounter; 
                $tempCounter++;
            }
            //else do nothing
        }

        // for each changed overlay, need to DELETE all points; include deleted outreachs here
        // INSERT new points for changed and new overlays


        $delsql .= $delIDs . ")";
        $insupdsql .= $insupdsqlValues . $insupdsql2;
        return "Vertices: " . $pointsStr . "\nCount: " . count($data) . "\nDEL SQL:\n" . $delsql . "\nINS/UPD SQL:\n" . $insupdsql;
        //return $data[0]['title'] . " -- " . count($data);
    } 



    /**
     * Generates HTML markup for each list item
     *
     * @param array $row    an array of the current item's attributes
     * @param int $order    the position of the current list item
     * @return string       the formatted HTML string
     */
    /*
    private function formatListItems($row, $order)
    {
        $c = $this->getColorClass($row['ListItemColor']);
        if($row['ListItemDone']==1)
        {
            $d = '<img class="crossout" src="/assets/images/crossout.png" '
                . 'style="width: 100%; display: block;"/>';
        }
        else
        {
            $d = NULL;
        }

        // If not logged in, manually append the <span> tag to each item
        if(!isset($_SESSION['LoggedIn'])||$_SESSION['LoggedIn']!=1)
        {
            $ss = "<span>";
            $se = "</span>";
        }
        else
        {
            $ss = NULL;
            $se = NULL;
        }

        return "tttt<li id="$row[ListItemID]" rel="$order" "
            . "class="$c" color="$row[ListItemColor]">$ss"
            . htmlentities(strip_tags($row['ListText'])).$d
            . "$se</li>n";
    }

    /**
     * Returns the CSS class that determines color for the list item
     *
     * @param int $color    the color code of an item
     * @return string       the corresponding CSS class for the color code
     */
    /*
    private function getColorClass($color)
    {
        switch($color)
        {
            case 1:
                return 'colorBlue';
            case 2:
                return 'colorYellow';
            case 3:
                return 'colorRed';
            default:
                return 'colorGreen';
        }
    }

     */
    public function createCampaign() {
    }

    public function deleateCampaign() {
    }

    public function updateCampaign() {
    }

    public function displayCampaign() {
    }
}

?>
