<?php
require_once "db_connect.php";

session_start();

if(isset($_POST['userID'])){
	$id = filter_input(INPUT_POST, 'userID', FILTER_SANITIZE_STRING);

    if ($update_stmt = $db->prepare("SELECT * FROM purchase_cart WHERE purchase_id=?")) {
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
            $message = '<table><tr><th>Items</th><th>Weight</th><th>Price</th></tr>';
            
            while ($row = $result->fetch_assoc()) {
                $message .= "<tr><td>".$row['purchasing_item']."</td><td>".$row['purchasing_weight']."</td><td>".$row['purchasing_price']."</td></tr>";
            }

            $message .= '</table>';
            
            echo json_encode(
                array(
                    "status" => "success",
                    "message" => $message
                ));
        }
    }
}
else{
    echo json_encode(
        array(
            "status" => "failed",
            "message" => "Missing Attribute"
            )); 
}
?>