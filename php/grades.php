<?php
require_once "db_connect.php";

session_start();
$allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
$path = '../assets/';

if(!isset($_SESSION['userID'])){
    echo '<script type="text/javascript">';
    echo 'window.location.href = "../login.html";</script>';
}

if(isset($_POST['code'], $_POST['grades'])){
    $code = filter_input(INPUT_POST, 'code', FILTER_SANITIZE_STRING);
    $grades = filter_input(INPUT_POST, 'grades', FILTER_SANITIZE_STRING);
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
            echo '<script type="text/javascript">alert("Please select a valid file format.");';
            echo 'location.href = "../index.php";</script>';
        }
    
        // Verify file size - 5MB maximum
        $maxsize = 5 * 1024 * 1024;
        if($filesize > $maxsize){
            echo '<script type="text/javascript">alert("File size is larger than the allowed limit.");';
            echo 'location.href = "../index.php";</script>';
        }
    
        // Verify MYME type of the file
        if(in_array($filetype, $allowed)){
            $temp = explode(".", $_FILES["image-upload"]["name"]);
            $newfilename = round(microtime(true)) . '.' . end($temp);

            // Check whether file exists before uploading it
            if(file_exists($path.$newfilename)){
                echo '<script type="text/javascript">alert("'.$newfilename.' is already exists.");';
                echo 'location.href = "../index.php";</script>';
            } 
            else{
                if (move_uploaded_file($_FILES["image-upload"]["tmp_name"], $path.$newfilename)) {
                    $filePath = $filePath.$newfilename;
                    $uploadOk = 1;
                } 
                else {
                    echo '<script type="text/javascript">alert("Sorry, there was an error uploading your file.");';
                    echo 'location.href = "../index.php";</script>';
                }
            } 
        } 
        else{
            echo '<script type="text/javascript">alert("Sorry, there was an error uploading your file.");';
            echo 'location.href = "../index.php";</script>';
        }
    }

    if($_POST['id'] != null && $_POST['id'] != ''){
        if ($update_stmt = $db->prepare("UPDATE items SET item_name=?, item_price=? WHERE id=?")) {
            $update_stmt->bind_param('sss', $code, $grades, $_POST['id']);
            
            // Execute the prepared query.
            if (! $update_stmt->execute()) {
                echo '<script type="text/javascript">alert("'.$update_stmt->error.'");';
                echo 'location.href = "../index.php";</script>';
            }
            else{
                $update_stmt->close();
                $db->close();

                echo '<script type="text/javascript">alert("Updated Successfully!!");';
                echo 'location.href = "../index.php";</script>';
            }
        }
    }
    else{
        if ($insert_stmt = $db->prepare("INSERT INTO items (item_name, item_price, img) VALUES (?, ?, ?)")) {
            $insert_stmt->bind_param('sss', $code, $grades, $filePath);
            
            // Execute the prepared query.
            if (! $insert_stmt->execute()) {
                echo '<script type="text/javascript">alert("'.$insert_stmt->error.'");';
                echo 'location.href = "../index.php";</script>';
            }
            else{
                $insert_stmt->close();
                $db->close();

                echo '<script type="text/javascript">alert("Added Successfully!!");';
                echo 'location.href = "../index.php";</script>';
            }
        }
    }
}
else{
    echo '<script type="text/javascript">alert("Please fill in all the fields.");';
    echo 'location.href = "../index.php";</script>';
}
?>