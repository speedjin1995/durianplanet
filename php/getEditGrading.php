<?php
require_once "db_connect.php";

session_start();

if(isset($_POST['userID'])){
    $id = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_STRING);

    if ($update_stmt = $db->prepare("SELECT * FROM purchase WHERE id=?")) {
        $update_stmt->bind_param('s', $id);

        // Execute the prepared query.
        if (! $update_stmt->execute()) {
            echo json_encode(
                array(
                    "status" => "failed",
                    "message" => "Something went wrong"
                )); 
        }
        else{
            $result = $update_stmt->get_result();
            $message = array();
            
            if ($row = $result->fetch_assoc()) {
                $message['id'] = $row['id'];
                $message['batch_no'] = $row['batch_no'];
                $message['total_price'] = $row['total_price'];
                $message['carts'] = array();

                if ($update_stmt2 = $db->prepare("SELECT * FROM purchase_cart WHERE purchase_id=?")) {
                    $update_stmt2->bind_param('s', $id);

                    if (! $update_stmt2->execute()) {
                        echo json_encode(
                            array(
                                "status" => "failed",
                                "message" => "Something went wrong when get cart"
                            )); 
                    }
                    else{
                        $result2 = $update_stmt2->get_result();
                        
                        while ($row2 = $result2->fetch_assoc()) {
                            $message2 = array();
                            $message2['id'] = $row2['id'];
                            $message2['purchasing_weight'] = $row2['purchasing_weight'];
                            $message2['purchasing_price'] = $row2['purchasing_price'];
                            $message2['purchasing_item'] = $row2['purchasing_item'];

                            array_push($message['carts'], $message2);
                        }

                        echo json_encode(
                            array(
                                "status" => "success",
                                "message" => $message
                            )
                        );
                    }
                }
                else{
                    echo json_encode(
                        array(
                            "status" => "failed",
                            "message" => "FAiled to get cart"
                        ));
                }
            }
        }
    }
}
else{
    echo json_encode(
        array(
            "status" => "failed",
            "message" => "Missing Attribute"
        )
    ); 
}
?>