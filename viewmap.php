<?php
    require_once "common/base.php";
    if(isset($_SESSION['LoggedIn']) && $_SESSION['LoggedIn']==1):
        $pageTitle = "Map View";
        require_once "common/header.php";

        if(isset($_POST['campaignID'])) {
            //echo $_POST['campaignID'];
        } 
    
?>


<style type="text/css">
html, body { height: 100%; width: 100%; margin: 0; padding: 0;}
</style>
<style type="text/css">
#map-canvas, html, body {
    padding: 0;
    margin: 0;
    height: 100%;
}

#panel {
    width: 200px;
    font-family: Arial, sans-serif;
    font-size: 13px;
    float: right;
    margin: 10px;
}

#color-palette {
    clear: both;
}

.color-button {
    width: 14px;
    height: 14px;
    font-size: 0;
    margin: 2px;
    float: left;
    cursor: pointer;
}

#delete-button {
    margin-top: 5px;
}
</style>
<script type="text/javascript"
src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCKVCkaoBI4uPY9I5OJXy0656OaYdCX_KE&libraries=geometry,drawing">
</script>
<script type="text/javascript">
"use strict"; // helps keep me from making dumb mistakes!


/* *************************************************************************************************
   *                                Global Variables                                               *
   ************************************************************************************************* */
var map;
var drawingManager;
var selectedShape = null;
var selectedPurpose = getRadioValue("overlayPurpose");
var colors = ['#1E90FF', '#FF1493', '#32CD32', '#FF8C00', '#4B0082'];
var selectedColor;
var colorButtons = {};
var polyOptions = {};
var overlayArray = [];
//var i = 0; //my primary loop iterator
var polyOptionsPlannedMagenta = {
    zIndex: 15,
    strokeWeight: 0,
    fillColor: '#FF3399',
    fillOpacity: 0.35
};
var polyOptionsPlannedBlue = {
    zIndex: 15,
    strokeWeight: 0,
    fillColor: '#0000FF',
    fillOpacity: 0.35
};
var polyOptionsPlannedPurple = {
    zIndex: 15,
    strokeWeight: 0,
    fillColor: '#6600CC',
    fillOpacity: 0.35
};
var polyOptionsComplete = {
    zIndex: 10,
    strokeWeight: 0,
    fillColor: '#00FF00',
    fillOpacity: 0.35
};
var polyOptionsAvoid = {
    zIndex: 5,
    strokeColor: '#FF0000',
    strokeOpacity: 0.6,
    strokeWeight: 3,
    fillColor: '#FF0000',
    fillOpacity: 0.35
};
var polyOptionsTarget = {
    zIndex: 1,
    strokeColor: '#FFFF55',
    strokeOpacity: 0.6,
    strokeWeight: 3,
    fillColor: '#FFFF55',
    fillOpacity: 0.15
};


/* *************************************************************************************************
   *                            Augmenting Overlays                                                *
   *   adds title, purpose, comments, date                                                         *
   ************************************************************************************************* */


function augmentMapOverlay(inID = -1, inTitle = "temp title", comments = "temp comments", purpose = "temp purpose", inDate = "", inChanged = false, overlay = null) {
    if (overlay !== null) {
        overlay.overlayID = inID;
        overlay.title = inTitle;
        overlay.overlayComments = comments;
        overlay.overlayPurpose = purpose;
        overlay.overlayDate = inDate;
        overlay.overlayChanged = inChanged;
        overlay.deleted = false;
        overlay.campaign = <?php echo $_POST['campaignID']; ?>;
    } else {alert("missing overlay!"); }
}

// Polygon - path (array of LatLng)
// Polyline - path (array of LatLng)
// Marker - position (LatLng)
// Cicle - center (LatLng) and radius
// Rectangle - bounds (2 x LatLng)
var tempGetInfo = function() {
    var vertices = [];
    var vertexStr = 'Coordinates:\n';
    if (this.type == google.maps.drawing.OverlayType.MARKER) {
        vertices = this.getPosition(); 
        vertexStr += ' -- ' + vertices.lat() + ',' + vertices.lng();
    }
    else { 
        vertices = this.getPath(); 
        for (var i =0; i < vertices.length; i++) {
            var xy = vertices.getAt(i);
            vertexStr += ' -- ' + i + ': ' + xy.lat() + ',' + xy.lng();
        }
    }
    return 'Overlay ID: ' + this.overlayID + '; Title: ' + this.title + '; Overlay Comments: ' + this.overlayComments + '; Overlay Type: ' + this.type + '; Purpose: ' + 
        this.overlayPurpose + '; Changed? ' + this.overlayChanged + '; Deleted? ' + this.deleted + '; ' + vertexStr;
}
google.maps.Polygon.prototype.getInfo = tempGetInfo;
google.maps.Marker.prototype.getInfo = tempGetInfo;


var tempToJSON = function() {
    var JSONObj = {
        ID: parseInt(this.overlayID),
        title: this.title,
        comments: this.overlayComments,
        purpose: this.overlayPurpose,
        overlayDate: this.overlayDate,
        changed: this.overlayChanged,
        deleted: this.deleted,
        radius: 1,
        campaign: this.campaign,
        type: this.type};
    var tempVertices = [];
    var path = this.getPath();
    for (var i = 0, l = path.length; i < l; i++) {
        tempVertices[i] = [path.getAt(i).lat(), path.getAt(i).lng()];
    }
    JSONObj.vertices = tempVertices;
    return JSONObj;
}
google.maps.Polygon.prototype.toJSON = tempToJSON;
google.maps.Marker.prototype.toJSON = tempToJSON;

function test(targetID, op, str) {


/*
   var url = "server_script.php";
var params = "q="+str;
xmlhttp.open("POST", url, true);

//Send the proper header information along with the request
xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
xmlhttp.setRequestHeader("Content-length", params.length);
xmlhttp.setRequestHeader("Connection", "close");

xmlhttp.onreadystatechange = function() {//Call a function when the state changes.
    if(xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        alert(xmlhttp.responseText);
    }
}
xmlhttp.send(params);  
 */
    //
	//alert("call test: "+targetID+", "+op+", "+str+", "+formName);
	var xmlhttp;    
	xmlhttp=new XMLHttpRequest();
	xmlhttp.onreadystatechange=function() {	
		//alert(xmlhttp.status);
  		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
    		//alert("ready");
    		document.getElementById(targetID).innerHTML = xmlhttp.responseText;
    		if (document.getElementById("maintable") != null) {formatTable();}
    		if (op==11) {
    			if (parseFloat(document.getElementById(targetID).innerHTML) < 0) {
    				document.getElementById(targetID).style.color="Red";
    			}
    			document.getElementById(targetID).innerHTML = 
    			formatAsDollar(document.getElementById(targetID).innerHTML);}
    	}
  	}
	xmlhttp.open("GET","budget.asp?o="+op+"&q="+str,true);
	xmlhttp.send();
}

// Prepare vertices for storage in database; returns string
function packVertices(inOverlay) {
    var vertices = [];
    var vertexStr = "";
    if (inOverlay.type == google.maps.drawing.OverlayType.MARKER) {
        vertices = inOverlay.getPosition(); 
        vertexStr += Math.round(vertices.lat() * 1000000) + ',' + Math.round(vertices.lng() * 1000000);
    }
    else { 
        vertices = inOverlay.getPath(); 
        var l = vertices.length;
        for (var i =0; i < l; i++) {
            var xy = vertices.getAt(i);
            vertexStr += Math.round(xy.lat() * 1000000) + ',' + Math.round(xy.lng() * 1000000);
            if (i < l - 1) {
                vertexStr += ";"
            } 
        }
    }
    return vertexStr;
}

// Receives a vertices string and returns whatever the overlayType requries
    //var complete1c = [ new google.maps.LatLng(29.456815, -98.559908), new google.maps.LatLng(29.456460, -98.556014), new google.maps.LatLng(29.455245, -98.556067), new google.maps.LatLng(29.454446, -98.555356), new google.maps.LatLng(29.454259, -98.556783), new google.maps.LatLng(29.453689, -98.558231), new google.maps.LatLng(29.453866, -98.560259) ];
function unpackVertices(overlayType, vertexStr) {
    // vertexStr.split(";");
    var outputArray = [];
    if (overlayType === "POLYGON" || overlayType === "POLYLINE" || overlayType === "MARKER") { // 2 or more vertices
        var vertices = vertexStr.split(";");
        var l = vertices.length;
        var comma, x, y = 0;
        for (var i = 0; i < l; i++) {
            comma = vertices[i].search(",");
            x = parseInt(vertices[i].substring(0, comma)) / 1000000;
            y = parseInt(vertices[i].substring(comma+1)) / 1000000;
            outputArray.push(new google.maps.LatLng(x, y));
        }
    //} else if (overlayType === "MARKER") {
    } else if (overlayType === "CIRCLE") {
    } else if (overlayType === "RECTANGLE") {
    } else {
        alert ("Not an overlay type!");
    } 
    
    return outputArray;
}

// receives database data and returns an overlay
function overlayBuilder(overlayID, overlayDate, overlayTitle, overlayComments, vertexArray, overlayPurpose, overlayType, overlayChanged) {
    var overlayCoords = vertexArray; //unpackVertices(overlayType, vertexStr);
    var polyOptionsPurpose = {};
    var tempOverlay= null;

    // if else to determin which overlay contructor
    // construct overlay with overlayCoords, black colors
    console.log("oB type: " + overlayType);
    if (overlayType.toUpperCase() === "POLYGON") {
        tempOverlay = new google.maps.Polygon({
            paths: overlayCoords,
            draggable: true
        });
        tempOverlay.type = google.maps.drawing.OverlayType.POLYGON;
    } else if (overlayType.toUpperCase() === "MARKER") {
        tempOverlay = new google.maps.Marker({
            position: overlayCoords[0],
            title: overlayTitle,
            draggable: true
        });
        tempOverlay.type = google.maps.drawing.OverlayType.MARKER;
    } else if (overlayType.toUpperCase() === "CIRCLE") {
    } else if (overlayType.toUpperCase() === "RECTANGLE") {
    } else if (overlayType.toUpperCase() === "POLYLINE") {
    } else {
        alert("not an overlay type");
    }
    // setUpPoly()
    setUpPoly(tempOverlay);
    // augmetMapOverlay(args from overlayBuilder)
    augmentMapOverlay(overlayID, overlayTitle, overlayComments, overlayPurpose, overlayDate, overlayChanged, tempOverlay); 
    // setOptions(polyOPtionsPurpose)
    switch (overlayPurpose) {
    case "complete":
        tempOverlay.setOptions(polyOptionsComplete);
        break;
    case "target":
        tempOverlay.setOptions(polyOptionsTarget);
        break;
    case "avoid":
        tempOverlay.setOptions(polyOptionsAvoid);
        break;
    case "planned_magenta":
        tempOverlay.setOptions(polyOptionsPlannedMagenta);
        break;
    case "planned_blue":
        tempOverlay.setOptions(polyOptionsPlannedBlue);
        break;
    case "planned_purple":
        tempOverlay.setOptions(polyOptionsPlannedPurple);
        break;
    default:
        // if not MARKER...
        console.log("must be marker");
        tempOverlay.setOptions({fillColor: '000000', fillOpacity: 1});
    }
    tempOverlay.setOptions(polyOptionsPurpose);
    return tempOverlay;

}
/*
function getOverlaysFromDatabase() {
    // connect to db
    // execut query
    //  ---  SELECT * from Outreach WHERE Campaign = [current campaign];
    // loop through db rows {
    //      var anOverlay = overlayBuilder([db row]);
    //      anOverlay.setMap(map);
    //      overlayArray.push(anOverlay);
    // }
}
 */
function uploadUrl(url,callback) {
    var request = window.ActiveXObject ?  new ActiveXObject('Microsoft.XMLHTTP') : new XMLHttpRequest;
    /*
    request.onreadystatechange = function() {
        if (request.readyState == 4) {
            request.onreadystatechange = doNothing;
            //alert(request.responseText);
            callback(request.responseText, request.status);
        }
    };
     */
    request.open('POST', url, true);
    request.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
    request.send(JSON.stringify(overlayArray));
    request.onloadend = function() {
        alert("Upload completed!");
    };
}

function saveOverlaysIntoDatabase() {
    console.log("Saving...");
    var JSONtest = JSON.stringify(overlayArray);
    console.log("JSON: " + JSONtest);
    var request = window.ActiveXObject ?  new ActiveXObject('Microsoft.XMLHTTP') : new XMLHttpRequest;
    /*
    request.onreadystatechange = function() {
        if (request.readyState == 4) {
            request.onreadystatechange = doNothing;
            //alert(request.responseText);
            callback(request.responseText, request.status);
        }
    };
     */
    request.open('POST', 'saveoutreaches.php', true);
    request.setRequestHeader('Content-Type', 'application/json; charset=UTF-8');
    request.onreadystatechange = function() {
        if (request.readyState == 4) {
            request.onreadystatechange = doNothing;
            console.log(request.responseText);
            //callback(request.responseText, request.status);
        }
    };
    request.send(JSON.stringify(overlayArray));
    request.onloadend = function() {
        console.log("Upload completed!");
    };
    /*
    uploadUrl("/Outreach/db-interaction/campaigns.php", function(data) {
    var request = window.ActiveXObject ?  new ActiveXObject('Microsoft.XMLHTTP') : new XMLHttpRequest;
        //alert("boo");
    var xml = parseXml(data);



    var newOverlays = [];
    var changedOverlays = [];

    for (var i = 0, l = overlayArray.length; i < l; i++) {
        if (overlayArray[i].changed) {
            if (overlayArray[i].overlayID === -1) {
                newOverlays.push(overlayArray[i]);
            } else {
                changedOverlays.push(overlayArray[i]);
            }
        }
    }

    //
    // connect to db
    // loop through overlayArray {
    //      if (changed === true && ID != -1) {
    //          execute update query;
    //      } else if (ID === -1) {
    //          execute INSERT INTO query (must include campaign);
//          }
    // }
    })
     */
}

function downloadUrl(url,callback) {
    var request = window.ActiveXObject ?  new ActiveXObject('Microsoft.XMLHTTP') : new XMLHttpRequest;
    request.onreadystatechange = function() {
        if (request.readyState == 4) {
            request.onreadystatechange = doNothing;
            //alert(request.responseText);
            callback(request.responseText, request.status);
        }
    };

    request.open('POST', url, true);
    request.setRequestHeader("Content-type","application/x-www-form-urlencoded");
    request.send("cID=<?php echo $_POST['campaignID']; ?>&action=getoverlays");
}

function getOverlaysFromDatabase() {

    downloadUrl("/Outreach/db-interaction/campaigns.php", function(data) {
        //alert("boo");
    var xml = parseXml(data);

    console.log("-------" + data + "-------");
    console.log(xml);
    var outreachNodes = xml.documentElement.getElementsByTagName("overlay");
    var l = outreachNodes.length;    
    for (var i = 0; i < l; i++) {
        var tempOverlay;
        var overlayID = outreachNodes[i].getAttribute("ID");
        var overlayTitle = outreachNodes[i].getAttribute("title");
        var overlayDate = outreachNodes[i].getAttribute("outreach_date");
        var overlayComments = outreachNodes[i].getAttribute("comments");
        var overlayPurpose = outreachNodes[i].getAttribute("purpose");
        var overlayType = outreachNodes[i].getAttribute("type");
        var overlayRadius = outreachNodes[i].getAttribute("radius");
        //console.log(overlayTitle+overlayComments);
        var vertexArray = [];
        var XMLVertices = outreachNodes[i].childNodes;
        var l2 = XMLVertices.length;
        for (var j = 0; j < l2; j++) {
            var x = XMLVertices[j].getAttribute("lat");
            var y = XMLVertices[j].getAttribute("lng");
            //console.log(x + ", " + y);
            vertexArray.push(new google.maps.LatLng(XMLVertices[j].getAttribute("lat"), XMLVertices[j].getAttribute("lng")));
        }
        tempOverlay = overlayBuilder(overlayID, overlayDate, overlayTitle, overlayComments, vertexArray, overlayPurpose, overlayType, false);
        overlayArray.push(tempOverlay); 
        console.log(tempOverlay.getInfo());
        //function augmentMapOverlay(inID = -1, inTitle = "temp title", comments = "temp comments", purpose = "temp purpose", inDate = "", overlay = null) {
    }
    for (var i = 0, l = overlayArray.length; i < l; i++) {
        overlayArray[i].setMap(map);
        console.log("set map");
    }
/*
    var markerNodes = xml.documentElement.getElementsByTagName("marker");
    var bounds = new google.maps.LatLngBounds();
    for (var i = 0; i < markerNodes.length; i++) {
    var name = markerNodes[i].getAttribute("name");
    var address = markerNodes[i].getAttribute("address");
    var distance = parseFloat(markerNodes[i].getAttribute("distance"));
    var latlng = new google.maps.LatLng(
        parseFloat(markerNodes[i].getAttribute("lat")),
        parseFloat(markerNodes[i].getAttribute("lng")));

    createOption(name, distance, i);
    createMarker(latlng, name, address);
    bounds.extend(latlng);
    }
    map.fitBounds(bounds);
    */
    });
}

function parseXml(str) {
    if (window.ActiveXObject) {
        var doc = new ActiveXObject('Microsoft.XMLDOM');
        doc.loadXML(str);
        return doc;
    } else if (window.DOMParser) {
        return (new DOMParser).parseFromString(str, 'text/xml');
    }
}


function doNothing() {}
/* *************************************************************************************************
   *                                      Helper Functions                                         *
   ************************************************************************************************* */


function getRadioValue(theRadioGroup)
{
    var elements = document.getElementsByName(theRadioGroup);
    for (var i = 0, l = elements.length; i < l; i++)
    {
        if (elements[i].checked)
        {
            return elements[i].value;
        }
    }
    return "";
}

function clearSelection() {
    if (selectedShape) {
        var outreachTitle = document.getElementById("outreachTitle");
        var outreachComments = document.getElementById("outreachComments");
        var outreachDate = document.getElementById("outreachDate");
        //var radioPurpose = "radio_" + selectedShape.overlayPurpose;
        //document.getElementById(radioPurpose).checked = false;
        if (selectedShape.type !== google.maps.drawing.OverlayType.MARKER) {
            selectedShape.setEditable(false);
            selectedShape.setDraggable(false);
            selectedShape.title = outreachTitle.value;
        } else {
            selectedShape.setAnimation(null); 
            selectedShape.setTitle(outreachTitle.value);
        }
        selectedShape.overlayComments = outreachComments.value;
        //console.log("---" + outreachDate.value + "---");
        selectedShape.overlayDate = outreachDate.value;
        selectedShape = null;
        outreachTitle.value = "";
        outreachComments.value = "";
        outreachDate.value = "";
    }
}

function setSelection(shape) {
    /*if ("purpose" in shape) {
        console.log("I have a purpose!");
    } else { console.log("I don't have a purpose!"); }*/
    clearSelection();
    selectedShape = shape;
    selectedShape.overlayChanged = true;
    if (shape.type !== google.maps.drawing.OverlayType.MARKER) {
        shape.setEditable(true);
        shape.setDraggable(true);
        var radioPurpose = "radio_" + shape.overlayPurpose;
        overlayPurposeChange(document.getElementById(radioPurpose));
        selectedPurpose = shape.overlayPurpose;
        //console.log("radioPurpose: " + radioPurpose);
        document.getElementById(radioPurpose).checked = true;
        document.getElementById("outreachTitle").value = shape.title;//shape.overlayPurpose;
    } else {
        shape.setAnimation(google.maps.Animation.BOUNCE); 
        document.getElementById("outreachTitle").value = shape.getTitle();//shape.overlayPurpose;
    }
    //selectColor(shape.get('fillColor') || shape.get('strokeColor'));
    //var myName = document.getElementsByName("outreachTitle");
    document.getElementById("outreachComments").value = shape.overlayComments;//shape.overlayPurpose;
    document.getElementById("outreachDate").value = shape.overlayDate;
}

function deleteSelectedShape() {
    if (selectedShape) {
        selectedShape.deleted = true;
        selectedShape.setMap(null);
        //for (var i = 0, l = overlayArray.length; i < l; i++) {
            //if (overlayArray[i].getMap() === null) {alert(i);}
        //}
        clearSelection();
    }
}

function myDebug() {
    console.log("******* My Debug *******");
    for (var i = 0, l = overlayArray.length; i < l; i++) {
        console.log(overlayArray[i].getInfo());
    }

    var sendString = 'cID=<?php echo $_POST['campaignID']; ?>&action=saveoverlays&overlays=' + JSON.stringify(overlayArray);
    console.log("sendString: " + sendString);
/*//function overlayBuilder(overlayID, overlayDate, overlayTitle, overlayComments, vertexStr, overlayPurpose, overlayType) {
    var myOverlay = overlayBuilder(4, "2015-05-31", "Fake DB Overlay", "this won't work first time", "29460029,-98558663;29460271,-98557690;29459431,-98556802;29460234,-98556279;29460103,-98555327;29458986,-98555474;29458173,-98556901;29458929,-98558049", "planned_purple", "POLYGON");
    myOverlay.setMap(map);
    console.log(myOverlay.getInfo());
    var myOverlay2 = overlayBuilder(3, "2015-05-31", "Fake DB Marker", "this won't work first time", "29460029,-98558663", "marker", "MARKER");
    myOverlay2.setMap(map);
    console.log(myOverlay2.getInfo());
    /*
    if (selectedShape !== null) {
        console.log("Selected Shape: " + selectedShape.getInfo());
        console.log("---- verticesString -----");
        console.log(packVertices(selectedShape));
        console.log(unpackVertices("POLYGON", packVertices(selectedShape)));
    } else {
        console.log("nothing selected");
    }
    
    //console.log("Selected Shape: " + selectedShape !== null ? selectedShape.getInfo() : "nothing selected");
    console.log("Selected Purpose: " + selectedPurpose);
    console.log("overlayArray length: " + overlayArray.length);
    for (var i = 0, l = overlayArray.length; i < l; i++) { 
        console.log("overlayArray[" + i + "]: " + overlayArray[i].getInfo()); 
    }
     */
}

function overlayPurposeChange(myRadio) {
    //console.log('Value: ' + myRadio.value);
    selectedPurpose = myRadio.value;
    if (selectedPurpose === "complete") {
        //polyOptions = polyOptionsComplete;
        drawingManager.set('rectangleOptions', polyOptionsComplete);
        drawingManager.set('circleOptions', polyOptionsComplete);
        drawingManager.set('polygonOptions', polyOptionsComplete);
    } else if (selectedPurpose === "target") {
        //polyOptions = polyOptionsTarget;
        drawingManager.set('rectangleOptions', polyOptionsTarget);
        drawingManager.set('circleOptions', polyOptionsTarget);
        drawingManager.set('polygonOptions', polyOptionsTarget);
    } else if (selectedPurpose === "avoid") {
        //polyOptions = polyOptionsAvoid;
        drawingManager.set('rectangleOptions', polyOptionsAvoid);
        drawingManager.set('circleOptions',  polyOptionsAvoid);
        drawingManager.set('polygonOptions', polyOptionsAvoid);
    } else if (selectedPurpose === "planned_magenta") {
        //polyOptions = polyOptionsPlannedMagenta;
        drawingManager.set('rectangleOptions', polyOptionsPlannedMagenta);
        drawingManager.set('circleOptions', polyOptionsPlannedMagenta);
        drawingManager.set('polygonOptions', polyOptionsPlannedMagenta);
    } else if (selectedPurpose === "planned_blue") {
        //polyOptions = polyOptionsPlannedBlue;
        drawingManager.set('rectangleOptions', polyOptionsPlannedBlue);
        drawingManager.set('circleOptions', polyOptionsPlannedBlue);
        drawingManager.set('polygonOptions', polyOptionsPlannedBlue);
    } else if (selectedPurpose === "planned_purple") {
        //polyOptions = polyOptionsPlannedPurple;
        drawingManager.set('rectangleOptions', polyOptionsPlannedPurple);
        drawingManager.set('circleOptions', polyOptionsPlannedPurple);
        drawingManager.set('polygonOptions', polyOptionsPlannedPurple);
    } else { alert("overlayPurposeChange error! selectedPurpose: " + selectedPurpose);}

    if (selectedShape) {
        if (selectedShape.type !== google.maps.drawing.OverlayType.MARKER) {
            selectedShape.overlayPurpose = selectedPurpose;
            //console.log("Purpose: " + selectedShape.overlayPurpose);
            if (selectedPurpose === "complete") {
                selectedShape.setOptions(polyOptionsComplete);
            } else if (selectedPurpose === "target") {
                selectedShape.setOptions(polyOptionsTarget);
            } else if (selectedPurpose === "avoid") {
                selectedShape.setOptions(polyOptionsAvoid);
            } else if (selectedPurpose === "planned_magenta") {
                selectedShape.setOptions(polyOptionsPlannedMagenta);
            } else if (selectedPurpose === "planned_blue") {
                selectedShape.setOptions(polyOptionsPlannedBlue);
            } else if (selectedPurpose === "planned_purple") {
                selectedShape.setOptions(polyOptionsPlannedPurple);
            } else { alert("overlayPurposeChange() error! selectedPurpose: " + selectedPurpose);}
        }
    }
}

//Sean Ouimet    http://stackoverflow.com/questions/8831382/google-maps-v3-delete-vertex-on-polygon 
function deleteNode(mev) {
        if (mev.vertex != null) {
            this.getPath().removeAt(mev.vertex);
        }
}

function setUpPoly(newShape) {
    google.maps.event.addListener(newShape, 'click', function() { setSelection(newShape); });
    if (newShape.type !== google.maps.drawing.OverlayType.MARKER) {
        google.maps.event.addListener(newShape, 'rightclick', deleteNode);
    }
}

/* *************************************************************************************************
   *                                 Create test overlays                                          *
   ************************************************************************************************* */

function createTestOverlays() {
    var targetarea_c = [ new google.maps.LatLng(29.466408, -98.571184), new google.maps.LatLng(29.466568, -98.566865), new google.maps.LatLng(29.467885, -98.566017), new google.maps.LatLng(29.465727, -98.560985), new google.maps.LatLng(29.464700, -98.550986), new google.maps.LatLng(29.464849, -98.549259), new google.maps.LatLng(29.453920, -98.550643), new google.maps.LatLng(29.452971, -98.551142), new google.maps.LatLng(29.447763, -98.551769), new google.maps.LatLng(29.448632, -98.560910), new google.maps.LatLng(29.454966, -98.560148), new google.maps.LatLng(29.455788, -98.568302), new google.maps.LatLng(29.458030, -98.568066), new google.maps.LatLng(29.462458, -98.569053), new google.maps.LatLng(29.464046, -98.570770) ];
    var targetarea = new google.maps.Polygon({
        paths: targetarea_c,
        strokeColor: '#FFFF55',
        strokeOpacity: 0.6,
        strokeWeight: 3,
        fillColor: '#FFFF55',
        fillOpacity: 0.15
    });
    targetarea.type = google.maps.drawing.OverlayType.POLYGON;
    setUpPoly(targetarea);
    augmentMapOverlay(-1, "CBC Easter 2015", "this was a huge area!", "target", "2015-03-28", targetarea);
    overlayArray.push(targetarea);

    var complete1c = [ new google.maps.LatLng(29.456815, -98.559908), new google.maps.LatLng(29.456460, -98.556014), new google.maps.LatLng(29.455245, -98.556067), new google.maps.LatLng(29.454446, -98.555356), new google.maps.LatLng(29.454259, -98.556783), new google.maps.LatLng(29.453689, -98.558231), new google.maps.LatLng(29.453866, -98.560259) ];
    var complete1 = new google.maps.Polygon({
        paths: complete1c,
        strokeColor: '#55FF55',
        strokeOpacity: 0.7,
        strokeWeight: 2,
        fillColor: '#55FF55',
        fillOpacity: 0.35,
        draggable: true
    });
    complete1.type = google.maps.drawing.OverlayType.POLYGON;
    setUpPoly(complete1);
    augmentMapOverlay(-1, "Louis and Anthony", "go Louis!!", "complete", "2015-04-04", complete1);
    overlayArray.push(complete1);

    var markerCBC = new google.maps.Marker({
        position: new google.maps.LatLng(29.459990, -98.560104),
        title: 'Cheryl Bible Chapel',
        draggable: true
    });
    markerCBC.type = google.maps.drawing.OverlayType.MARKER;
    setUpPoly(markerCBC);
    augmentMapOverlay(-1, 'Cheryl Bible Chapel', "the best church in SATX", "marker", "2015-3-28", markerCBC);
    overlayArray.push(markerCBC);

    var markerCherylWest = new google.maps.Marker({
        position: new google.maps.LatLng(29.462615, -98.567211),
        title: 'Cheryl West Apts',
        draggable: true
    });
    markerCherylWest.type = google.maps.drawing.OverlayType.MARKER;
    setUpPoly(markerCherylWest);
    augmentMapOverlay(-1, "Cheryl West Apts", "outreach each Wednesday night", "marker", "2015-3-28", markerCherylWest);
    overlayArray.push(markerCherylWest);

    //for (var i = 0, l = overlayArray.length; i < l; i++) {
        //overlayArray[i].setMap(map);
    //}
}


/* *************************************************************************************************
   *                                  Google Maps Initializer                                      *
   ************************************************************************************************* */
function initialize() {
    var mapOptions = {
        center: { lat: 29.457570, lng: -98.559841},
        zoom: 16,
        mapTypeId: google.maps.MapTypeId.HYBRID
    };
    map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

    getOverlaysFromDatabase();
    // Create and display test polys and markers
    //createTestOverlays();
    //var polyOptions = {};
    selectedPurpose = getRadioValue("overlayPurpose")
    if (selectedPurpose === "complete") {
        polyOptions = polyOptionsComplete;
    } else if (selectedPurpose === "target") {
        polyOptions = polyOptionsTarget;
    } else if (selectedPurpose === "avoid") {
        polyOptions = polyOptionsAvoid;
    } else if (selectedPurpose === "planned_magenta") {
        polyOptions = polyOptionsPlannedMagenta;
    } else if (selectedPurpose === "planned_blue") {
        polyOptions = polyOptionsPlannedBlue;
    } else if (selectedPurpose === "planned_purple") {
        polyOptions = polyOptionsPlannedPurple;
    } else { alert("mapInit error! selectedPurpose: " + selectedPurpose);}

    /*var polyOptions = {
        strokeWeight: 0,
        fillOpacity: 0.45,
        editable: true,
        zIndez: 10
    };*/

    drawingManager = new google.maps.drawing.DrawingManager({
        drawingMode: null, //google.maps.drawing.OverlayType.POLYGON,
        drawingControl: true,
        drawingControlOptions: {
            position: google.maps.ControlPosition.TOP_CENTER,
            drawingModes: [
                google.maps.drawing.OverlayType.MARKER,
                google.maps.drawing.OverlayType.CIRCLE,
                google.maps.drawing.OverlayType.POLYGON,
                google.maps.drawing.OverlayType.POLYLINE,
                google.maps.drawing.OverlayType.RECTANGLE
            ]
        },
        markerOptions: { draggable: true },
        polylineOptions: { editable: true },
        rectangleOptions: polyOptions,
        circleOptions: polyOptions,
        polygonOptions: polyOptions
    });
    drawingManager.setMap(map);

    google.maps.event.addListener(drawingManager, 'overlaycomplete', function(e) {
        //console.log("overlaycomplete!");
        var tempDate = new Date();
        var dateString = tempDate.getFullYear() + "-" + (tempDate.getMonth() + 1) + "-" + tempDate.getDate();
        if (e.type != google.maps.drawing.OverlayType.MARKER) {
            // Switch back to non-drawing mode after drawing a shape.
            drawingManager.setDrawingMode(null);

            // Add an event listener that selects the newly-drawn shape when the user mouses down on it.
            var newShape = e.overlay;
            newShape.type = e.type;
            setUpPoly(newShape);
            augmentMapOverlay(-1, "", "", selectedPurpose, dateString, true, newShape);
            setSelection(newShape);
            overlayArray.push(newShape);
        } else {
            var newShape = e.overlay;
            newShape.type = e.type;
            setUpPoly(newShape);
            augmentMapOverlay(-1, "", "", "marker", dateString, true, newShape);
            setSelection(newShape);
            overlayArray.push(newShape);
        }
    });

    // Clear the current selection when the drawing mode is changed, or when the map is clicked.
    google.maps.event.addListener(drawingManager, 'drawingmode_changed', clearSelection);
    google.maps.event.addListener(map, 'click', clearSelection);
    google.maps.event.addDomListener(document.getElementById('delete-button'), 'click', deleteSelectedShape);
    google.maps.event.addDomListener(document.getElementById('debug-button'), 'click', myDebug);
    google.maps.event.addDomListener(document.getElementById('save-button'), 'click', saveOverlaysIntoDatabase);

    //buildColorPalette();
}


google.maps.event.addDomListener(window, 'load', initialize);
</script>
</head>
<body>
<div id="panel">
    <!--<div id="color-palette"></div>-->
    <div>
        <button id="delete-button">Delete Selected Shape</button>
    </div>
    <div id="overlay-type">
        <form>
            <input type="radio" id="radio_target" name="overlayPurpose" onclick="overlayPurposeChange(this);" value="target" checked>Target Area
            <br>
            <input type="radio" id="radio_avoid" name="overlayPurpose" onclick="overlayPurposeChange(this);" value="avoid">Avoid
            <br>
            <input type="radio" id="radio_complete" name="overlayPurpose" onclick="overlayPurposeChange(this);" value="complete">Complete
            <br>
            <input type="radio" id="radio_planned_magenta" name="overlayPurpose" onclick="overlayPurposeChange(this);" value="planned_magenta">Planned (Magenta)
            <br>
            <input type="radio" id="radio_planned_blue" name="overlayPurpose" onclick="overlayPurposeChange(this);" value="planned_blue">Planned (Blue)
            <br>
            <input type="radio" id="radio_planned_purple" name="overlayPurpose" onclick="overlayPurposeChange(this);" value="planned_purple">Planned (Purple)
        </form> 
    </div>
    <div>
        <button id="debug-button">Debug</button>
    </div>
    <div id="overlay-details">
        <form>
            Name:<br><input type="text" id="outreachTitle" name="outreachTitle"> 
            <textarea id="outreachComments" name="Comments" rows="10" cols="23">
                Name goes here.
            </textarea> 
            Outreach Date:<br><input type="date" id="outreachDate" name="outreachDate">
        </form> 
    </div>
    <div>
        <button id="save-button">Save</button>
    </div>
    <div>
        <form method="post" action="index.php">
            <div>
                <input type="submit" value="Cancel" class="button" />
            </div>
        </form>
    </div>
</div>
<div id="map-canvas"></div>

<?php
    else:
        header("Location: /Outreach/");
        exit;
    endif;
    require_once "common/footer.php";
?>

