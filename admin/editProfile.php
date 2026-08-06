<?php include "./checkSession.php"; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- META SECTION -->
    <title>Edit Profile - Admin Panel</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" href="./logo.jpg" type="image/x-icon" />
    <!-- END META SECTION -->

    <!-- CSS INCLUDE -->
    <link rel="stylesheet" type="text/css" id="theme" href="css/<?php getTheme(); ?>" />
    <style>
        .form-control {
            background: #fff !important;
            height: 38px;
            font-size: 13px;
        }


        .form-control:focus {
            border: 1px solid #000066;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            color: #111;
            margin-top: 2px;
            font-size: 13px;
            font-weight: 650;
        }

        .btns {
            margin-top: 20px;
            word-spacing: 7px;
        }

        .btns button {
            padding: 10px;
            width: 100px;
            border: none;
            border-radius: 4px;
            color: #fff;
            font-size: 15px;
            box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.12);
        }

        .btns button:hover {
            transition: 0.6s ease-in;
            background: #E71C23 !important;
        }

        .col-md-6 button {
            width: 100%;
            margin-top: 20px;
            border: none;
            color: #fff;
            background: #E71C23;
            padding: 7px;
            border-radius: 4px;
            font-weight: 600;
        }

        .col-md-6 button:hover {
            background: #0ABDE3 !important;
        }

        button.updateImage:hover {
            background-color: #2ecc72 !important;
        }
    </style>
    <!-- CSS -->
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.11.2/build/css/alertify.min.css" />

    <!-- JavaScript -->
    <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.11.2/build/alertify.min.js"></script>
    <!-- EOF CSS INCLUDE -->
</head>

<body onload="getAdminProfileRecord()">
    <!-- START PAGE CONTAINER -->
    <div class="page-container">

        <?php include "./header.php"; ?>

        <!-- START BREADCRUMB -->
        <ul class="breadcrumb">
            <li><a href="./dashboard.php">Dashboard</a></li>
            <li><a href="#">Settings</a></li>
            <li class="active">Edit Profile</li>
        </ul>
        <!-- END BREADCRUMB -->

        <!-- PAGE CONTENT WRAPPER -->
        <div class="page-content-wrap container">
            <span style="padding:10px;background:#000066;color:#fff;text-align:left;font-weight:600;font-size:17px;width:100%;margin-bottom:10px;display:block;">
                <i class="fa fa-edit"></i> Edit Profile
                <span style="float:right;margin-right:10px;cursor:pointer" class="fa fa-image" title="Change Profile Picture" onclick="$('#myModal').modal('show')">
                </span>
            </span>

            <!-- employee detail  -->
            <div id="editProfile"></div>

            <!-- modal here so it exists in DOM immediately -->
            <div class="modal fade" id="myModal" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-primary" style="border-radius:0;">
                            <button type="button" class="close" style="color:#f00;border-radius:50%;background:#fff;width:20px;" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title" style="color:#fff;"><i class="fa fa-edit"></i> Update Profile Picture</h4>
                        </div>
                        <div class="modal-body">
                            <form action="" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label>Select New Image:</label>
                                    <input type="file" name="newImage" id="newImage" class="form-control" accept="image/*" required>
                                </div>
                                <center>
                                    <button type="submit" name="updateImage" style="color:#fff;background:#E71C23;border:0;padding:7px;border-radius:3px;font-size:14px;">
                                        <i class="fa fa-image"></i> Update Profile Picture
                                    </button>
                                </center>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- END PAGE CONTAINER -->
        <!-- php start here -->
        <?php

        // image update
        if (isset($_POST["updateImage"])) {
            include "./db.php";
            $admin_id = intval($_SESSION["admin_id"]);
            $r = $conn->prepare("SELECT email FROM admin WHERE id=?");
            $r->bind_param("i", $admin_id);
            $r->execute();
            $adminRow = $r->get_result()->fetch_assoc();

            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
            $mime = mime_content_type($_FILES["newImage"]["tmp_name"]);
            if (!isset($allowed[$mime])) {
        ?>
                <script>alertify.error("<span style='color:#fff;font-size:15px;'><i class='fa fa-warning'></i> Only image files are allowed!</span>");</script>
        <?php
            } else {
                $ext = $allowed[$mime];
                $image_name = $adminRow["email"] . "." . $ext;
                if (move_uploaded_file($_FILES["newImage"]["tmp_name"], "./images/" . $image_name)) {
                    $upd = $conn->prepare("UPDATE admin SET image=? WHERE id=?");
                    $upd->bind_param("si", $image_name, $admin_id);
                    if ($upd->execute()) {
        ?>
                        <script>
                            alertify.success("<span style='color:#fff;font-size:15px;'><i class='fa fa-check-circle'></i> Profile Picture Updated!!!</span>");
                            // reload profile pic in header without full page reload
                            setTimeout(function(){
                                var ts = new Date().getTime();
                                $('img[src*="images/"]').each(function(){
                                    var src = $(this).attr('src').split('?')[0];
                                    $(this).attr('src', src + '?t=' + ts);
                                });
                                $('#myModal').modal('hide');
                                getAdminProfileRecord();
                            }, 800);
                        </script>
        <?php
                    } else { ?>
                        <script>alertify.error("<span style='color:#fff;font-size:15px;'><i class='fa fa-warning'></i> DB Error!!!</span>");</script>
        <?php       }
                } else { ?>
                    <script>alertify.error("<span style='color:#fff;font-size:15px;'><i class='fa fa-warning'></i> Image Not Uploaded!!!</span>");</script>
        <?php       }
            }
        }

        // update profile
        if (isset($_POST["edit"])) {
            $admin_id = intval($_SESSION["admin_id"]); // always use session
            $admin_name = $_POST["name"];
            $position = $_POST["position"];
            include "./db.php";
            $upd = $conn->prepare("UPDATE admin SET name=?, position=? WHERE id=?");
            $upd->bind_param("ssi", $admin_name, $position, $admin_id);
            if ($upd->execute()) {
        ?>
                <script>alertify.success("<span style='color:#fff;font-size:15px;'><i class='fa fa-check-circle'></i> Profile Updated!!!</span>");</script>
        <?php   } else { ?>
                <script>alertify.error("<span style='color:#fff;font-size:15px;'><i class='fa fa-warning'></i> Profile Not Updated!!!</span>");</script>
        <?php   }
        }
        ?>
        <!---PHP End herer -->
        <!--- MESSAGE BOX-->
        <div class="message-box animated fadeIn" data-sound="alert" id="mb-signout">
            <div class="mb-container">
                <div class="mb-middle">
                    <div class="mb-title"><span class="fa fa-sign-out"></span> Log <strong>Out</strong> ?</div>
                    <div class="mb-content">
                        <p>Are you sure you want to log out?</p>
                        <p>Press N o if you want to continue work. Press Yes to logout current user.</p>
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
        <?php include "./preload.php"; ?>
        <?php include "./mainScripts.php"; ?>


        <script>
            function getAdminProfileRecord() {
                let admin_id = <?php echo $_SESSION["admin_id"]; ?>;
                $.ajax({
                    url: `getEditProfile.php?admin_id=${admin_id}`,
                    success: function(data) {
                        $("#editProfile").html(data);
                    }
                });
            }
        </script>
</body>

</html>