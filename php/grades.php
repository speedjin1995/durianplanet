<?php
require_once "db_connect.php";

session_start();
$allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
$path = '../assets/';

if(!isset($_SESSION['userID'])){
    echo '<script type="text/javascript">';
    echo 'window.location.href = "../login.html";</script>';
}

if(isset($_POST['code'], $_POST['grades'], $_POST['category'], $_POST['packing'])){
    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
    $grades = filter_input(INPUT_POST, 'grades', FILTER_SANITIZE_STRING);
    $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
    $packing = filter_input(INPUT_POST, 'packing', FILTER_SANITIZE_STRING);
    $path = $path.'products/';
    $filePath = 'products/';
    $uploadOk = 0;

    if(isset($_FILES["image-upload"]) && $_FILES["image-upload"]["error"] == 0){
        $filename = $_FILES["image-upload"]["name"];
        $filetype = $_FILES["image-upload"]["type"];
        $filesize = $_FILES["image-upload"]["size"];
    
        // Verify file extension
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if(!array_key_exists($ext, $allowed)){
            echo json_encode(
                array(
                    "status" => "failed",
                    "message" => "Please select a valid file format."
                )
            );
        }
    
        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if($filesize > $maxsize){
            echo json_encode(
                array(
                    "status" => "failed",
                    "message" => "File size is larger than the allowed limit."
                )
            );
        }
    
        // Verify MYME type of the file
        if(in_array($filetype, $allowed)){
            $temp = explode(".", $_FILES["image-upload"]["name"]);
            $newfilename = round(microtime(true)) . '.' . end($temp);

            // Check whether file exists before uploading it
            if(file_exists($path.$newfilename)){
                echo json_encode(
                    array(
                        "status" => "failed",
                        "message" => $newfilename." is already exists."
                    )
                );
            } 
            else{
                if (move_uploaded_file($_FILES["image-upload"]["tmp_name"], $path.$newfilename)) {
                    $filePath = $filePath.$newfilename;
                    $uploadOk = 1;
                } 
                else {
                    echo json_encode(
                        array(
                            "status" => "failed",
                            "message" => "Sorry, there was an error uploading your file."
                        )
                    );
                }
            } 
        } 
        else{
            echo json_encode(
                array(
                    "status" => "failed",
                    "message" => "Sorry, there was an error uploading your file."
                )
            );
        }
    }

    if($_POST['id'] != null && $_POST['id'] != ''){
        if($uploadOk == 1){
            if ($update_stmt = $db->prepare("UPDATE items SET category=?, item_name=?, item_price=?, packing=?, img=? WHERE id=?")) {
                $update_stmt->bind_param('ssssss', $category, $code, $grades, $packing, $path, $_POST['id']);
                
                // Execute the prepared query.
                if (! $update_stmt->execute()) {
                    echo json_encode(
                        array(
                            "status" => "failed",
                            "message" => $update_stmt->error
                        )
                    );
                }
                else{
                    $update_stmt->close();
                    $db->close();
    
                    echo json_encode(
                        array(
                            "status" => "success",
                            "message" => "Updated Successfully!!"
                        )
                    );
                }
            }
        }
        else{
            if ($update_stmt = $db->prepare("UPDATE items SET category=?, item_name=?, item_price=?, packing=? WHERE id=?")) {
                $update_stmt->bind_param('sssss', $category, $code, $grades, $packing, $_POST['id']);
                
                // Execute the prepared query.
                if (! $update_stmt->execute()) {
                    echo json_encode(
                        array(
                            "status" => "failed",
                            "message" => $update_stmt->error
                        )
                    );
                }
                else{
                    $update_stmt->close();
                    $db->close();
    
                    echo json_encode(
                        array(
                            "status" => "success",
                            "message" => "Updated Successfully!!"
                        )
                    );
                }
            }
        }
    }
    else{
        if($uploadOk == 1){
            if ($insert_stmt = $db->prepare("INSERT INTO items (category, item_name, item_price, packing, img) VALUES (?, ?, ?, ?, ?)")) {
                $insert_stmt->bind_param('sssss', $category, $code, $grades, $packing, $filePath);
                
                // Execute the prepared query.
                if (! $insert_stmt->execute()) {
                    echo json_encode(
                        array(
                            "status" => "failed",
                            "message" => $insert_stmt->error
                        )
                    );
                }
                else{
                    $insert_stmt->close();
                    $db->close();
    
                    echo json_encode(
                        array(
                            "status" => "success",
                            "message" => "Added Successfully!!"
                        )
                    );
                }
            }
        }
        else{
            if ($insert_stmt = $db->prepare("INSERT INTO items (category, item_name, item_price, packing) VALUES (?, ?, ?, ?)")) {
                $insert_stmt->bind_param('ssss', $category, $code, $grades, $packing);
                
                // Execute the prepared query.
                if (! $insert_stmt->execute()) {
                    echo json_encode(
                        array(
                            "status" => "failed",
                            "message" => $insert_stmt->error
                        )
                    );
                }
                else{
                    $insert_stmt->close();
                    $db->close();
    
                    echo json_encode(
                        array(
                            "status" => "success",
                            "message" => "Added Successfully!!"
                        )
                    );
                }
            }
        }
    }
}
else{
    echo json_encode(
        array(
            "status" => "failed",
            "message" => "Please fill in all the fields."
        )
    );
}
?>