<?php
require_once "db_connect.php";

session_start();

if(!isset($_SESSION['userID'])){
    echo '<script type="text/javascript">';
    echo 'window.location.href = "../login.html";</script>';
}

if(isset($_POST['items'], $_POST['itemWeight'], $_POST['itemsRepack'], $_POST['productWeight'])){
    $itemWeight=$_POST['itemWeight'];
    $itemsRepack=$_POST['itemsRepack'];
    $items = filter_input(INPUT_POST, 'items', FILTER_SANITIZE_STRING);
    $productWeight = filter_input(INPUT_POST, 'productWeight', FILTER_SANITIZE_STRING);
    $success = true;
    $quantity = '0';

    $deleted = array();

    if(isset($_POST['deleted']) && $_POST['deleted'] != null){
        $deleted = $_POST['deleted'];
        $deleted = array_map('intval', $deleted);
    }

    if ($select_stmt = $db->prepare("SELECT * FROM inventory WHERE item_id=?")) {
        $select_stmt->bind_param('s', $items);
        
        // Execute the prepared query.
        if ($select_stmt->execute()) {
            $result = $select_stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $quantity = $row['quantity'];
                $id = $row['id'];
                $quantity = (float)$quantity - (float)$productWeight;

                if ($update_stmt = $db->prepare("UPDATE inventory SET quantity=? WHERE id=?")) {
                    $update_stmt->bind_param('ss', $quantity, $id);
                    $update_stmt->execute();
                }
            }
        }
    }

    for($i=0; $i<count($itemsRepack); $i++){
        if($itemsRepack[$i] != null && !in_array($i,$deleted)){
            if ($select_stmt2 = $db->prepare("SELECT * FROM inventory WHERE item_id=?")) {
                $select_stmt2->bind_param('s', $itemsRepack[$i]);
                
                // Execute the prepared query.
                if ($select_stmt2->execute()) {
                    $result2 = $select_stmt2->get_result();
        
                    if ($row2 = $result2->fetch_assoc()) {
                        $quantity2 = $row2['quantity'];
                        $packing2 = $row2['packing'];
                        $id2 = $row2['id'];
    
                        if($packing2 == '2'){
                            $quantity2 = (float)$quantity2 + (float)$itemWeight[$i];
                        }
                        else{
                            $quantity2 = (int)$quantity2 + 1;
                        }
        
                        if ($update_stmt2 = $db->prepare("UPDATE inventory SET quantity=? WHERE id=?")) {
                            $update_stmt2->bind_param('ss', $quantity2, $id2);
                            
                            if(! $update_stmt2->execute()){
                                $success = false;
                            }
                        }
                    }
                }
            }
        }
        
    }

    if($success){
        echo json_encode(
            array(
                "status"=> "success", 
                "message"=> "Repacked"
            )
        );
    }
    else{
        echo json_encode(
            array(
                "status"=> "failed", 
                "message"=> "Something went wrong during repacking"
            )
        );
    }
}
else{
    echo json_encode(
        array(
            "status"=> "failed", 
            "message"=> "Please fill in all the fields"
        )
    );
}
?>