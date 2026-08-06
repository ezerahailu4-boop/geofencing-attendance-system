<!----comment---->
<?php include "./checkSession.php";?>
<!DOCTYPE html>
<html lang="en">
    <head>        
        <!-- META SECTION -->
        <title>Dashboard - Employee Panel</title>            
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />        
        <link rel="icon" href="./logo.jpg" type="image/x-icon" />
        <!-- END META SECTION -->
                
        <!-- CSS INCLUDE -->        
        <link rel="stylesheet" type="text/css" id="theme" href="./admin/css/<?php getTheme();?>"/>
        <link rel="stylesheet" href="./mobile.css"/>
        <!-- EOF CSS INCLUDE -->    

        <!-- alertify plugin -->
<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.11.2/build/alertify.min.js"></script>
<!-- CSS -->
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.11.2/build/css/alertify.min.css"/>
 
<style>
/* attendance mark modern styles */
.att-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    padding: 28px;
    margin-bottom: 24px;
}
.att-header {
    background: linear-gradient(135deg, #000066, #3333cc);
    color: #fff;
    border-radius: 12px;
    padding: 16px 24px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.att-header h3 { margin: 0; font-size: 18px; font-weight: 600; }
.att-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.att-info-item {
    background: #f8f9ff;
    border: 1px solid #e8eaf6;
    border-radius: 12px;
    padding: 14px 16px;
}
.att-info-item label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: #888;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}
.att-info-item span {
    font-size: 15px;
    font-weight: 600;
    color: #1a1a2e;
}
.att-info-item span.green { color: #00b894; }

/* big check-in button */
.checkin-btn-wrap { text-align: center; padding: 16px 0 24px; }
#submitBtn {
    width: 180px;
    height: 180px;
    border-radius: 50%;
    border: none;
    font-size: 16px;
    font-weight: 700;
    color: #fff;
    background: linear-gradient(135deg, #00b894, #00cec9);
    box-shadow: 0 8px 32px rgba(0,184,148,0.4);
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
#submitBtn i { font-size: 36px; }
#submitBtn.checkout {
    background: linear-gradient(135deg, #d63031, #e17055);
    box-shadow: 0 8px 32px rgba(214,48,49,0.4);
}
#submitBtn:disabled {
    background: linear-gradient(135deg, #b2bec3, #dfe6e9);
    box-shadow: none;
    cursor: not-allowed;
}
#submitBtn:not(:disabled):hover { transform: scale(1.05); }

.status-badge {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 13px;
    font-weight: 600;
    margin-top: 12px;
}
.status-badge.inside { background: #d4edda; color: #155724; }
.status-badge.outside { background: #f8d7da; color: #721c24; }

.location-table-wrap {
    background: #f8f9ff;
    border-radius: 12px;
    padding: 16px;
    margin-top: 16px;
}
.location-table-wrap h5 {
    font-size: 13px;
    font-weight: 700;
    color: #000066;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 12px;
}
.location-table-wrap table { width: 100%; font-size: 13px; }
.location-table-wrap th { color: #888; font-weight: 600; padding: 6px 8px; border-bottom: 1px solid #e0e0e0; }
.location-table-wrap td { padding: 8px; color: #333; }

#map { border-radius: 12px; overflow: hidden; margin-top: 16px; }

.time-row {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 16px;
}
.time-box {
    flex: 1;
    min-width: 160px;
    background: #f8f9ff;
    border: 1px solid #e8eaf6;
    border-radius: 12px;
    padding: 12px 16px;
}
.time-box label { font-size: 11px; font-weight: 600; color: #888; text-transform: uppercase; display: block; margin-bottom: 4px; }
.time-box input { border: none; background: transparent; font-size: 15px; font-weight: 700; color: #000066; width: 100%; outline: none; }
</style>
    <!-- script start -->
<script>

   var coordinates={
       lat:0,
       lng:0,
       acc:0,
       address:"",
       distance:0,
       velocity:0,
       speed:0,
   }
    success=(pos)=>{
        coordinates.lat=pos.coords.latitude;
        coordinates.lng=pos.coords.longitude;
        // check employee current location status
          checkStatus();                    
    }

    error=(err)=> {
         console.warn(`ERROR(${err.code}): ${err.message}`);
        alertify.error(`<span style='color:#fff;font-size:12px;'>ERROR(${err.code}): ${err.message}) Try Again OR Open In Diffrent Browser</span>`);
    }

    var options = {
        timeout: 500, 
        enableHighAccuracy: true 
    };

// watch user position
    getCurrentPositions=()=>{                
        let watchId=navigator.geolocation.watchPosition(success,error,options);
    }
    getCurrentPositions();
    // getCurrentPosition    
</script>
    <!-- script end -->

    </head>
    <body>
        <!-- START PAGE CONTAINER -->
        <div class="page-container">
            
             <?php include "./header.php";?>    


                <!-- START BREADCRUMB -->
                <ul class="breadcrumb">
                    <li><a href="dashboard.php">Dashboard</a></li>          
                </ul>
                <!-- END BREADCRUMB -->                       
                
                <!-- PAGE CONTENT WRAPPER -->
                <div class="page-content-wrap">
                    <div class="container" style="max-width:800px;padding-top:20px;">

                        <div class="att-header">
                            <i class="fa fa-map-marker" style="font-size:22px;"></i>
                            <h3>Mark Attendance</h3>
                            <span style="margin-left:auto;font-size:13px;opacity:0.8;"><?php echo date("l, d M Y"); ?></span>
                        </div>

                        <!-- info grid -->
                        <div class="att-info-grid">
                            <div class="att-info-item">
                                <label>Employee</label>
                                <span><?php echo getUserName($_SESSION["emp_id"]); ?></span>
                            </div>
                            <div class="att-info-item">
                                <label>Live Time</label>
                                <span class="green" id="time"><?php echo date("h:i:s a"); ?></span>
                            </div>
                            <div class="att-info-item">
                                <label>Assigned Location</label>
                                <span><?php echo getAssignedLocation($_SESSION["emp_id"]); ?></span>
                            </div>
                            <div class="att-info-item">
                                <label>Distance Limit</label>
                                <span><?php getDistanceLimit($_SESSION["emp_id"]); ?> m</span>
                            </div>
                        </div>

                        <!-- check in/out button -->
                        <div class="att-card">
                            <div class="checkin-btn-wrap">
                                <button type="button" id="submitBtn" disabled onclick="takeAction()">
                                    <i class="fa fa-map-marker"></i>
                                    <span id="btnText">Locating...</span>
                                </button>
                                <div><span id="statusBadge" class="status-badge outside">Detecting location...</span></div>
                            </div>

                            <!-- last times -->
                            <div class="time-row">
                                <div class="time-box">
                                    <label><i class="fa fa-sign-in"></i> Last Check-In</label>
                                    <input type="text" id="checkInTime" readonly placeholder="--:-- --">
                                </div>
                                <div class="time-box">
                                    <label><i class="fa fa-sign-out"></i> Last Check-Out</label>
                                    <input type="text" id="checkOutTime" readonly placeholder="--:-- --">
                                </div>
                            </div>

                            <!-- current location table -->
                            <div class="location-table-wrap">
                                <h5><i class="fa fa-crosshairs"></i> Current Location</h5>
                                <table>
                                    <thead><tr><th>Latitude</th><th>Longitude</th><th>Distance</th><th>Address</th></tr></thead>
                                    <tbody><tr>
                                        <td id="lat">—</td>
                                        <td id="lng">—</td>
                                        <td id="distance">—</td>
                                        <td id="address">Detecting...</td>
                                    </tr></tbody>
                                </table>
                            </div>

                            <div id="map" style="width:100%;height:280px;margin-top:16px;"></div>
                        </div>
                    </div>
                </div><!-- END PAGE CONTENT WRAPPER -->
            </div><!-- END PAGE CONTENT -->
        </div>
        <!-- END PAGE CONTAINER -->

        <!-- MESSAGE BOX-->
        <div class="message-box animated fadeIn" data-sound="alert" id="mb-signout">
            <div class="mb-container">
                <div class="mb-middle">
                    <div class="mb-title"><span class="fa fa-sign-out"></span> Log <strong>Out</strong> ?</div>
                    <div class="mb-content">
                        <p>Are you sure you want to log out?</p>                    
                        <p>Press No if youwant to continue work. Press Yes to logout current user.</p>
                    </div>
                    <div class="mb-footer">
                        <div class="pull-right">
                            <a href="./logout.php" class="btn btn-success btn-lg">Yes</a>
                            <button class="btn btn-default btn-lg mb-control-close">No</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END MESSAGE BOX-->

       
		<!---script--->

<script src="./checkDistance.js"></script>
<script>
    let currentAction = 'checkin';

    function getTime(){
        document.querySelector("#time").textContent = new Date().toLocaleTimeString();
    }
    setInterval(getTime, 1000);

    function checkStatus(){
        let storeLat = <?php echo getAssignedLocationLat($_SESSION["emp_id"]);?>;
        let storeLng = <?php echo getAssignedLocationLng($_SESSION["emp_id"]);?>;
        let distance = findDistance(storeLat, storeLng, coordinates.lat, coordinates.lng, "M");
        coordinates.distance = distance;
        $("#distance").text(distance + " m");
        $("#lat").text(coordinates.lat);
        $("#lng").text(coordinates.lng);

        let myApi = "https://maps.googleapis.com/maps/api/geocode/json?latlng="+coordinates.lat+","+coordinates.lng+"&key=AIzaSyClbbki1DYIOxc8KN-WULeJFaqf-ESHkkY";
        $.ajax({ url: myApi, success: function(data){
            if(data.results && data.results.length > 1){
                coordinates.address = data.results[0].formatted_address;
                $("#address").text(coordinates.address);
            } else {
                let addr = "<?php echo addslashes(getAssignedLocation($_SESSION["emp_id"])); ?>";
                $("#address").text(addr);
                coordinates.address = addr;
            }
        }});

        let limit = <?php getDistanceLimit($_SESSION['emp_id']); ?>;
        let status = <?php echo checkStatus($_SESSION["emp_id"]); ?>;

        if(distance <= limit){
            $("#submitBtn").prop("disabled", false);
            $("#statusBadge").text("\u2705 You are inside the geofence ("+Math.round(distance)+" m away)").removeClass("outside").addClass("inside");
            if(status == 0){
                currentAction = 'checkin';
                $("#submitBtn").removeClass("checkout");
                $("#btnText").text("Check In");
                $("#submitBtn i").attr("class", "fa fa-sign-in");
                $("#checkInTime").val("<?php echo getLastCheckInTime($_SESSION['emp_id']); ?>");
                $("#checkOutTime").val("<?php echo getLastCheckOutTime($_SESSION['emp_id']); ?>");
            } else {
                currentAction = 'checkout';
                $("#submitBtn").addClass("checkout");
                $("#btnText").text("Check Out");
                $("#submitBtn i").attr("class", "fa fa-sign-out");
                $("#checkInTime").val("<?php echo getLastCheckInTime($_SESSION['emp_id']); ?>");
            }
        } else {
            $("#submitBtn").prop("disabled", true);
            $("#statusBadge").text("\u274C Outside geofence ("+Math.round(distance)+" m away)").removeClass("inside").addClass("outside");
            $("#btnText").text("Out of Range");
        }
    }
    setInterval(checkStatus, 2000);

    function takeAction(){
        let userId = <?php echo $_SESSION["emp_id"]; ?>;
        if(currentAction === 'checkin'){
            $.post("./checkInEntry.php", {userId:userId, userLat:coordinates.lat, userLng:coordinates.lng, address:coordinates.address, distance:coordinates.distance}, function(){
                let html = "<center><i class='fa fa-check-circle' style='color:#00b894;font-size:80px;'></i></center>";
                alertify.alert('Checked In!', html + '<p style="text-align:center;margin-top:10px;">Attendance recorded successfully</p>', function(){ window.location.reload(); });
            });
        } else {
            let lastId = <?php echo getLastInsertCheckInId($_SESSION["emp_id"]); ?>;
            $.post("./checkOutEntry.php", {userId:userId, userLat:coordinates.lat, userLng:coordinates.lng, address:coordinates.address, lastInsertCheckInId:lastId, distance:coordinates.distance}, function(){
                let html = "<center><i class='fa fa-check-circle' style='color:#d63031;font-size:80px;'></i></center>";
                alertify.alert('Checked Out!', html + '<p style="text-align:center;margin-top:10px;">See you tomorrow!</p>', function(){ window.location.reload(); });
            });
        }
    }

    var map, infoWindow;
    function initMap(){
        map = new google.maps.Map(document.getElementById('map'), {
            center: {lat: 0, lng: 0}, zoom: 16, mapTypeId: 'roadmap'
        });
        infoWindow = new google.maps.InfoWindow;
        if(navigator.geolocation){
            navigator.geolocation.watchPosition(function(pos){
                var p = {lat: pos.coords.latitude, lng: pos.coords.longitude};
                infoWindow.setPosition(p);
                infoWindow.setContent('<?php echo getUserName($_SESSION["emp_id"]); ?> — You are here');
                infoWindow.open(map);
                map.setCenter(p);
            });
        }
    }
</script>

<!-- google map api  -->
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyClbbki1DYIOxc8KN-WULeJFaqf-ESHkkY&callback=initMap">
</script>  
		<!---google map api--->
		
 
        <?php include "./preload.php";?>
        <?php include "./mainScripts.php";?>
     </body>
</html>






