<?php
require_once "db_connect.php";

session_start();

if(!isset($_SESSION['userID'])){
    echo '<script type="text/javascript">';
    echo 'window.location.href = "../login.html";</script>';
}

if(isset($_POST['items'], $_POST['itemWeight'], $_POST['itemPrice'], $_POST['totalPrice'], $_POST['totalPricing'])){
    $items=$_POST['items'];
    $itemWeight=$_POST['itemWeight'];
    $itemPrice=$_POST['itemPrice'];
    $totalPrice=$_POST['totalPrice'];
    $totalPricing = filter_input(INPUT_POST, 'totalPricing', FILTER_SANITIZE_STRING);
    $success = true;
    $today = date("Y-m-d 00:00:00");
    $deleted = array();

    if(isset($_POST['deleted']) && $_POST['deleted'] != null){
        $deleted = $_POST['deleted'];
        $deleted = array_map('intval', $deleted);
    }

    if($_POST['id'] != null && $_POST['id'] != ''){
        if ($update_stmt = $db->prepare("UPDATE weighing SET item_types=?, lot_no=?, tray_weight=?, tray_no=?, grading_net_weight=?, grade, pieces, grading_gross_weight, grading_net_weight, moisture_after_grading=? WHERE id=?")) {
            $update_stmt->bind_param('ssssssss', $itemType, $grossWeight, $lotNo, $bTrayWeight, $bTrayNo, $netWeight, $moistureValue, $_POST['id']);
            
            // Execute the prepared query.
            if (! $update_stmt->execute()) {
                echo json_encode(
                    array(
                        "status"=> "failed", 
                        "message"=> $update_stmt->error
                    )
                );
            }
            else{

                $action = "User : ".$name."Update Tray No : ".$bTrayNo." in grades table!";

                if ($log_insert_stmt = $db->prepare("INSERT INTO log (userId , userName, action) VALUES (?, ?, ?)")) {
                    $log_insert_stmt->bind_param('sss', $userID, $name, $action);
                

                    if (! $log_insert_stmt->execute()) {
                        echo json_encode(
                            array(
                                "status"=> "failed", 
                                "message"=> $log_insert_stmt->error 
                            )
                        );
                    }
                    else{
                        $log_insert_stmt->close();
                    }
                }

                $update_stmt->close();
                $db->close();
                
                echo json_encode(
                    array(
                        "status"=> "success", 
                        "message"=> "Updated Successfully!!" 
                    )
                );
            }
        }
    }
    else{
        if ($select_stmt = $db->prepare("SELECT COUNT(*) FROM purchase WHERE created_datetime >= ?")) {
            $select_stmt->bind_param('s', $today);
            
            // Execute the prepared query.
            if (! $select_stmt->execute()) {
                echo json_encode(
                    array(
                        "status" => "failed",
                        "message" => "Failed to get latest count"
                    )); 
            }
            else{
                $result = $select_stmt->get_result();
                $count = 1;
                $firstChar = 'P'.date("Ymd");
                
                if ($row = $result->fetch_assoc()) {
                    $count = (int)$row['COUNT(*)'] + 1;
                    $select_stmt->close();
                }

                $charSize = strlen(strval($count));

                for($i=0; $i<(4-(int)$charSize); $i++){
                    $firstChar.='0';  // S0000
                }
        
                $firstChar .= strval($count);  //S00009

                if ($insert_stmt = $db->prepare("INSERT INTO purchase (batch_no, total_price) VALUES (?, ?)")) {
                    $insert_stmt->bind_param('ss', $firstChar, $totalPricing);
                    
                    // Execute the prepared query.
                    if (! $insert_stmt->execute()) {
                        echo json_encode(
                            array(
                                "status"=> "failed", 
                                "message"=> "Failed to created purchase records due to ".$insert_stmt->error
                            )
                        );
                    }
                    else{
                        $id = $insert_stmt->insert_id;;
                        $insert_stmt->close();

                        for($i=0; $i<sizeof($items); $i++){
                            if($items[$i] != null && !in_array($i,$deleted)){
                                if ($insert_stmt2 = $db->prepare("INSERT INTO purchase_cart (purchase_id, purchasing_weight, purchasing_price, purchasing_item) VALUES (?, ?, ?, ?)")) {
                                    $insert_stmt2->bind_param('ssss', $id, $itemWeight[$i], $totalPrice[$i], $items[$i]);
                                    
                                    // Execute the prepared query.
                                    if (! $insert_stmt2->execute()) {
                                        $success = false;
                                    }
                                    else{
                                        if ($select_stmt2 = $db->prepare("SELECT * FROM inventory WHERE item_id=?")) {
                                            $select_stmt2->bind_param('s', $items[$i]);
                                            
                                            // Execute the prepared query.
                                            if ($select_stmt2->execute()) {
                                                $result2 = $select_stmt2->get_result();
                                    
                                                if ($row2 = $result2->fetch_assoc()) {
                                                    $quantity2 = $row2['quantity'];
                                                    $packing2 = $row2['packing'];
                                                    $id2 = $row2['id'];
                                                    $quantity2 = (float)$quantity2 + (float)$itemWeight[$i];
                                    
                                                    if ($update_stmt2 = $db->prepare("UPDATE inventory SET quantity=? WHERE id=?")) {
                                                        $update_stmt2->bind_param('ss', $quantity2, $id2);
                                                        $update_stmt2->execute();
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if($success){
                            $insert_stmt2->close();
                            $db->close();

                            echo json_encode(
                                array(
                                    "status"=> "success", 
                                    "message"=> "Added Successfully!!"
                                )
                            );
                        }
                        else{
                            $insert_stmt2->close();
                            $db->close();

                            echo json_encode(
                                array(
                                    "status"=> "failed", 
                                    "message"=> "Failed to created purchase cart records due to ".$insert_stmt2->error 
                                )
                            );
                        }
                    }
                }
            }
        }
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