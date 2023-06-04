<?php
## Database configuration
require_once 'db_connect.php';

## Read value
$draw = $_POST['draw'];
$row = $_POST['start'];
$rowperpage = $_POST['length']; // Rows display per page
$columnIndex = $_POST['order'][0]['column']; // Column index
$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
$searchValue = mysqli_real_escape_string($db,$_POST['search']['value']); // Search value

## Search 
$searchQuery = " ";
if($searchValue != ''){
  $searchQuery = "WHERE receipt_no like '%".$searchValue."%'";
}

## Total number of records without filtering
$sel = mysqli_query($db,"select count(*) as allcount from sales");
$records = mysqli_fetch_assoc($sel);
$totalRecords = $records['allcount'];

## Total number of record with filtering
$sel = mysqli_query($db,"select count(*) as allcount from sales". $searchQuery);
$records = mysqli_fetch_assoc($sel);
$totalRecordwithFilter = $records['allcount'];

## Fetch records
$empQuery = "select * from sales ".$searchQuery." order by ".$columnName." ".$columnSortOrder." limit ".$row.",".$rowperpage;
$empRecords = mysqli_query($db, $empQuery);
$data = array();
$counter = 1;

while($row = mysqli_fetch_assoc($empRecords)) {
  $data[] = array( 
    "counter"=>$counter,
    "id"=>$row['id'],
    "receipt_no"=>$row['receipt_no'],
    "sub_total"=>$row['sub_total'],
    "discount"=>$row['discount'],
    "total_price"=>$row['total_price'],
    "payment_method"=>$row['payment_method'],
    "created_datetime"=>$row['created_datetime']
  );

  $counter++;
}

## Response
$response = array(
  "draw" => intval($draw),
  "iTotalRecords" => $totalRecords,
  "iTotalDisplayRecords" => $totalRecordwithFilter,
  "aaData" => $data,
  "query" => $empQuery
);

echo json_encode($response);
?>