<?php
require_once 'php/db_connect.php';

session_start();

if(!isset($_SESSION['userID'])){
  echo '<script type="text/javascript">';
  echo 'window.location.href = "login.html";</script>';
}
else{
    $user = $_SESSION['userID'];
    $stmt = $db->prepare("SELECT * from users where id = ?");
    $stmt->bind_param('s', $user);
    $stmt->execute();
    $result = $stmt->get_result();
    $role = 'NORMAL';
	
    if(($row = $result->fetch_assoc()) !== null){
     $role = $row['role_code'];
    }

    $products = $db->query("SELECT * FROM items WHERE item_status = '0'");
    $products2 = $db->query("SELECT * FROM items WHERE item_status = '0'");
}
?>

<style>
    @media screen and (min-width: 676px) {
        .modal-dialog {
          max-width: 1600px; /* New width for default modal */
        }
    }

    #TableId{
        width: 100%;
        margin-bottom: 20px;
		border-collapse: collapse;
    }
    #TableId th, #TableId td{
        border: 1px solid #cdcdcd;
    }
    #TableId th, #TableId td{
        padding: 10px;
        text-align: left;
    }
    .bootstrap-datetimepicker-widget.dropdown-menu.wider  {
        width: auto;
    }
    .mt-32{
        margin-top:32px;
    }
    .bootstrap-datetimepicker-widget table th:hover {
        color: black;
    }
    .bootstrap-datetimepicker-widget table td.disabled, .bootstrap-datetimepicker-widget table td.disabled:hover {
        background-color: #d0d0d0;
    }
</style>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0 text-dark">Sales Report 销售报告</h1>
			</div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<section class="content">
	<div class="container-fluid">
        <div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-body">
                        <div class="row">
                            <div class="form-group col-3">
                                <label>From Date 开始日期</label>
                                <div class="input-group date" id="fromDatePicker" data-target-input="nearest">
                                    <input type="text" class="form-control datetimepicker-input" data-target="#fromDatePicker" id="fromDate" name="fromDate" required/>
                                    <div class="input-group-append" data-target="#fromDatePicker" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div></div>
                                </div>
                            </div>

                            <div class="form-group col-3">
                                <label>To Date 结束日期</label>
                                <div class="input-group date" id="toDatePicker" data-target-input="nearest">
                                    <input type="text" class="form-control datetimepicker-input" data-target="#toDatePicker" id="toDate" name="toDate" required/>
                                    <div class="input-group-append" data-target="#toDatePicker" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-3">
                                <label for="itemType">Payment Methods 货品种类 *</label>
                                <select class="form-control" style="width: 100%;" id="paymentMethod" name="paymentMethod" required>
                                    <option value="" selected disabled hidden>Please Select</option>
                                    <option value="e-wallet">e-wallet</option>
                                    <option value="cash">cash</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3 mt-32">
                                <button class="btn btn-success" id="filterSearch"><i class="fas fa-search"></i> Filter 筛选</button> 
                            </div>                                            
                        </div>                        
						<table id="salesTable" class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>No. <br>排号</th>
                                    <th>Receipt No <br>单号</th>
                                    <th>Sub Total Price <br>小计</th>
                                    <th>Discount <br>折扣</th>
                                    <th>Total Price <br>总价</th>
                                    <th>Payment Method <br>付款方式</th>
                                    <th>Created Datetime <br>更新时间</th>
                                    <th>Action <br>行动</th>
								</tr>
							</thead>
						</table>
					</div><!-- /.card-body -->
				</div><!-- /.card -->
			</div><!-- /.col -->
		</div><!-- /.row -->
	</div><!-- /.container-fluid -->
</section><!-- /.content -->

<script>
var contentIndex = 0;
var size = $("#TableId").find(".details").length

$(function () {
    //Date picker
    var oneWeek = new Date();
    oneWeek.setHours(0,0,0,0);
    var oneWeek2 = new Date();
    oneWeek2.setHours(23,59,59,999);

    <?php 
            if($role  == "NORMAL"){
               echo "oneWeek.setDate(oneWeek.getDate() - 7);";
               

               echo "
               $('#fromDatePicker').datetimepicker({
                    icons: { time: 'far fa-clock' },
                    format: 'DD/MM/YYYY HH:mm:ss A',
                    defaultDate: oneWeek
                });";
        

                echo "
                $('#toDatePicker').datetimepicker({
                    icons: { time: 'far fa-clock' },
                    format: 'DD/MM/YYYY HH:mm:ss A',
                    defaultDate : oneWeek2
                });";
            }else{
                echo "$('#fromDatePicker').datetimepicker({
                    icons: { time: 'far fa-clock' },
                    format: 'DD/MM/YYYY HH:mm:ss A',
                    defaultDate: oneWeek
                });";
            
                echo "$('#toDatePicker').datetimepicker({
                    icons: { time: 'far fa-clock' },
                    format: 'DD/MM/YYYY HH:mm:ss A',
                    defaultDate: oneWeek2
                });";

            }
    ?>

    var table = $("#salesTable").DataTable({
        "responsive": true,
        "autoWidth": false,
        'processing': true,
        'serverSide': true,
        'searching': false,
        'serverMethod': 'post',
        'order': [[ 2, 'asc' ]],
        'columnDefs': [ { orderable: false, targets: [0] }],
        'ajax': {
            'url':'php/loadReceives.php'
        },
        'columns': [
            { data: 'counter' },
            { data: 'receipt_no' },
            { data: 'sub_total' },
            { data: 'discount' },
            { data: 'total_price' },
            { data: 'payment_method' },
            { data: 'created_datetime' },
            { 
                data: 'id',
                render: function ( data, type, row ) {
                    return '<div class="row"><div class="col-3"><button type="button" id="view'+data+'" onclick="view('+data+')" class="btn btn-warning btn-sm"><i class="fas fa-eye"></i></button></div><div class="col-3"><button type="button" id="edit'+data+'" onclick="edit('+data+')" class="btn btn-success btn-sm"><i class="fas fa-pen"></i></button></div><div class="col-3"><button type="button" id="deactivate'+data+'" onclick="deactivate('+data+')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></div></div>';
                }
            }
        ],
        "rowCallback": function( row, data, index ) {
            $('td', row).css('background-color', '#E6E6FA');
        },        
    });
    
    $.validator.setDefaults({
        submitHandler: function () {
            $('#spinnerLoading').show();
            if($('#gradesModal').hasClass('show')){
                $.post('php/wgrade.php', $('#gradeForm').serialize(), function(data){
                    var obj = JSON.parse(data); 
                    
                    if(obj.status === 'success'){
                        $('#gradesModal').modal('hide');
                        toastr["success"](obj.message, "Success:");

                        /*var printWindow = window.open('', '', 'height=400,width=800');
                        printWindow.document.write(obj.label);
                        printWindow.document.close();
                        setTimeout(function(){
                            printWindow.print();
                            printWindow.close();
                        }, 1000);*/
                        
                        $.get('wgrade.php', function(data) {
                            $('#mainContents').html(data);
                            $('#spinnerLoading').hide();
                        });
                    }
                    else if(obj.status === 'failed'){
                        toastr["error"](obj.message, "Failed:");
                        $('#spinnerLoading').hide();
                    }
                    else{
                        toastr["error"]("Something wrong when edit", "Failed:");
                        $('#spinnerLoading').hide();
                    }
                });
            }
            else if($('#editGradesModal').hasClass('show')){

                $.post('php/editGrading.php', $('#editGradeForm').serialize(), function(data){
                        var obj = JSON.parse(data); 
                        
                        if(obj.status === 'success'){
                            $('#gradesModal').modal('hide');
                            toastr["success"](obj.message, "Success:");
                            
                            $.get('wgrade.php', function(data) {
                                $('#mainContents').html(data);
                                $('#spinnerLoading').hide();
                            });
                        }
                        else if(obj.status === 'failed'){
                            toastr["error"](obj.message, "Failed:");
                            $('#spinnerLoading').hide();
                        }
                        else{
                            toastr["error"]("Something wrong when edit", "Failed:");
                            $('#spinnerLoading').hide();
                        }
                    });
            }
            
        }
    });

    $('#gradeTable tbody').on('click', 'td.dt-control', function () {
        var tr = $(this).closest('tr');
        var data_id = $(this).closest('tr').find('#row').val();
        var row = table.row( tr );

        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            // Open this row
            <?php 
                if($role == "ADMIN"){
                    echo 'debugger;row.child(format(data_id)).show();tr.addClass("shown");';
                }
                else{
                    echo 'row.child(formatNormal(data_id)).show();tr.addClass("shown");';
                }
            ?>
        }
    });

    $('#addGrades').on('click', function(){
        $('#gradesModal').find('#id').val("");
        $('#gradesModal').find('#lotNo').val("");
        $('#TableId').find('.details').remove();
        $('#gradesModal').modal('show');
        
        $('#gradeForm').validate({
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });
    });

    $(".add-row").click(function(){
        var $addContents = $("#addContents").clone();
        $("#TableId").append($addContents.html());

        $("#TableId").find('.details:last').attr("id", "detail" + size);
        $("#TableId").find('.details:last').attr("data-index", size);
        $("#TableId").find('#remove:last').attr("id", "remove" + size);
        $("#TableId").find('#itemWeightSyncBtn:last').attr("id", "itemWeightSyncBtn" + size);

        $("#TableId").find('#items:last').attr('name', 'items['+size+']').attr("id", "items" + size).attr("required", true);
        $("#TableId").find('#itemWeight:last').attr('name', 'itemWeight['+size+']').attr("id", "itemWeight" + size).attr("required", true);
        $("#TableId").find('#itemPrice:last').attr('name', 'itemPrice['+size+']').attr("id", "itemPrice" + size).attr("required", true);
        $("#TableId").find('#totalPrice:last').attr('name', 'totalPrice['+size+']').attr("id", "totalPrice" + size);
        
        size++;
    });

    $("#TableId").on('change', 'input[name^="itemWeight"]', function(){
        var grossWeight = 0;
        var bTrayNo = $(this).val();

        if($(this).parents('.details').find('input[name^="itemPrice"]').val()){
            var totalPrice = parseFloat($(this).val()) * parseFloat($(this).parents('.details').find('input[name^="itemWeight"]').val());
            var totalPricing = parseFloat($('#tableFoot').find('#totalPricing').val());
            totalPricing = totalPricing + totalPrice;
            $(this).parents('.details').find('input[name^="totalPrice"]').val(parseFloat(totalPrice).toFixed(2));
            $('#tableFoot').find('#totalPricing').val(parseFloat(totalPricing).toFixed(2));
        }
        else{
            $(this).parents('.details').find('input[name^="totalPrice"]').val(parseFloat("0.00").toFixed(2));
        }
    });

    $("#TableId").on('change', '[name^="itemPrice"]', function(){
        if($(this).parents('.details').find('input[name^="itemWeight"]').val()){
            var totalPrice = parseFloat($(this).val()) * parseFloat($(this).parents('.details').find('input[name^="itemWeight"]').val());
            var totalPricing = parseFloat($('#tableFoot').find('#totalPricing').val());
            totalPricing = totalPricing + totalPrice;
            $(this).parents('.details').find('input[name^="totalPrice"]').val(parseFloat(totalPrice).toFixed(2));
            $('#tableFoot').find('#totalPricing').val(parseFloat(totalPricing).toFixed(2));
        }
        else{
            $(this).parents('.details').find('input[name^="totalPrice"]').val(parseFloat("0.00").toFixed(2));
        }
    });
        
    // Find and remove selected table rows
    $("#TableId").on('click', 'button[id^="remove"]', function () {
        $(this).parents('.details').remove();
    });

    $("#TableId").on('click', 'button[id^="itemWeightSyncBtn"]', function(){
        var element = $(this);
        
        $.post('http://127.0.0.1:5002/handshaking', function(data){
            if(data != "Error"){
                console.log("Data Received:" + data);
                var temp = data.replace('S', '').replace('D', '').replace('+', '').replace('-', '').replace('g', '').replace('G', '').trim();
                var str = temp.split(".");
                var arr=[];
                
                for(var i=0; i<str[0].length; i++){
                    if(str[0].charAt(i).match(re3)){
                        arr.push(str[0][i]);
                    }
                }
                
                var text = arr.join("") + "." + str[1];
                element.parents('.details').find('input[name^="itemWeight"]').val(parseFloat(text).toFixed(2));
                element.parents('.details').find('input[name^="itemWeight"]').trigger('change');
            }
            else{
                toastr["error"]("Failed to get the reading!", "Failed:");
            }
        });
    });

    $('#filterSearch').on('click', function(){
        $('#spinnerLoading').show();

        var fromDateValue = '';
        var toDateValue = '';

        if($('#fromDate').val()){
        var convert1 = $('#fromDate').val().replace(", ", " ");
        convert1 = convert1.replace(":", "/");
        convert1 = convert1.replace(":", "/");
        convert1 = convert1.replace(" ", "/");
        convert1 = convert1.replace(" pm", "");
        convert1 = convert1.replace(" am", "");
        convert1 = convert1.replace(" PM", "");
        convert1 = convert1.replace(" AM", "");
        var convert2 = convert1.split("/");
        var date  = new Date(convert2[2], convert2[1] - 1, convert2[0], convert2[3], convert2[4], convert2[5]);
        fromDateValue = date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate() + " " + date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds();
        }
        
        if($('#toDate').val()){
        var convert3 = $('#toDate').val().replace(", ", " ");
        convert3 = convert3.replace(":", "/");
        convert3 = convert3.replace(":", "/");
        convert3 = convert3.replace(" ", "/");
        convert3 = convert3.replace(" pm", "");
        convert3 = convert3.replace(" am", "");
        convert3 = convert3.replace(" PM", "");
        convert3 = convert3.replace(" AM", "");
        var convert4 = convert3.split("/");
        var date2  = new Date(convert4[2], convert4[1] - 1, convert4[0], convert4[3], convert4[4], convert4[5]);
        toDateValue = date2.getFullYear() + "-" + (date2.getMonth() + 1) + "-" + date2.getDate() + " " + date2.getHours() + ":" + date2.getMinutes() + ":" + date2.getSeconds();
        }

        var itemTypeFilter = $('#itemTypeFilter').val() ? $('#itemTypeFilter').val() : '';


        //Destroy the old Datatable
        $("#gradeTable").DataTable().clear().destroy();

        //Create new Datatable
        table = $("#gradeTable").DataTable({
            "responsive": true,
            "autoWidth": false,
            'processing': true,
            'serverSide': true,
            'serverMethod': 'post',
            'searching': false,
            'order': [[ 2, 'asc' ]],
            'columnDefs': [ { orderable: false, targets: [0] }],
            'ajax': {
                'type': 'POST',
                'url':'php/filterWGrade.php',
                'data': {
                    fromDate: fromDateValue,
                    toDate: toDateValue,
                    itemTypeFilter: itemTypeFilter,
                } 
            },
            'columns': [
            { data: 'counter' },
            { data: 'item_types' },
            { data: 'lot_no' },
            { data: 'grade' },
            { data: 'tray_no' },
            { data: 'tray_weight' },
            { data: 'grading_gross_weight' },
            { data: 'pieces' },
            { data: 'grading_net_weight' },
            { data: 'moisture_after_grading' },
            { data: 'status' },
            { data: 'updated_datetime' },
            { 
                data: 'id',
                width: '140px',
                render: function ( data, type, row ) {
                    return '<div class="row px-0"><div class="col-3 mr-1"><button type="button" id="edit'+data+'" onclick="edit('+data+')" class="btn btn-success btn-sm"><i class="fas fa-pen"></i></button></div><div class="col-3 mr-1"><button type="button" id="print'+data+'" onclick="print('+data+')" class="btn btn-info btn-sm"><i class="fas fa-print"></i></button></div><div class="col-3"><button type="button" id="deactivate'+data+'" onclick="deactivate('+data+')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></div></div>';
                }
            }
            ],
            "rowCallback": function( row, data, index ) {
                $('td', row).css('background-color', '#E6E6FA');
            }

        });

        $('#spinnerLoading').hide();
    });
});

function format (id) {
    $('#spinnerLoading').show();
    $.post('php/getPuchaseList.php', {userID: id}, function(data){
        var obj = JSON.parse(data); 
        
        if(obj.status === 'success'){
            $('#spinnerLoading').hide();
            return obj.message;
        }
        else if(obj.status === 'failed'){
            toastr["error"](obj.message, "Failed:");
            $('#spinnerLoading').hide();
            return '';
        }
        else{
            toastr["error"]("Something wrong when edit", "Failed:");
            $('#spinnerLoading').hide();
            return '';
        }
    });
}

function formatNormal (row) {
  return '<div class="row"><div class="col-md-3"><p>Customer Name: '+row.customer_name+
  '</p></div><div class="col-md-3"><p>Unit Weight: '+row.unit+
  '</p></div><div class="col-md-3"><p>Weight Status: '+row.status+
  '</p></div><div class="col-md-3"><p>MOQ: '+row.moq+
  '</p></div></div><div class="row"><div class="col-md-3"><p>Address: '+row.customer_address+
  '</p></div><div class="col-md-3"><p>Batch No: '+row.batchNo+
  '</p></div><div class="col-md-3"><p>Weight By: '+row.userName+
  '</p></div><div class="col-md-3"><p>Package: '+row.packages+
  '</p></div></div><div class="row"><div class="col-md-3">'+
  '</div><div class="col-md-3"><p>Lot No: '+row.lots_no+
  '</p></div><div class="col-md-3"><p>Invoice No: '+row.invoiceNo+
  '</p></div><div class="col-md-3"><p>Unit Price: '+row.unitPrice+
  '</p></div></div><div class="row"><div class="col-md-3">'+
  '</div><div class="col-md-3"><p>Order Weight: '+row.supplyWeight+
  '</p></div><div class="col-md-3"><p>Delivery No: '+row.deliveryNo+
  '</p></div><div class="col-md-3"><p>Total Weight: '+row.totalPrice+
  '</p></div></div><div class="row"><div class="col-md-3"><p>Contact No: '+row.customer_phone+
  '</p></div><div class="col-md-3"><p>Variance Weight: '+row.varianceWeight+
  '</p></div><div class="col-md-3"><p>Purchase No: '+row.purchaseNo+
  '</p></div><div class="col-md-3"><div class="row"><div class="col-3"><button type="button" class="btn btn-warning btn-sm" onclick="edit('+row.id+
  ')"><i class="fas fa-pen"></i></button></div><div class="col-3"><button type="button" class="btn btn-info btn-sm" onclick="print('+row.id+
  ')"><i class="fas fa-print"></i></button></div><div class="col-3"><button type="button" class="btn btn-success btn-sm" onclick="portrait('+row.id+
  ')"><i class="fas fa-receipt"></i></button></div></div></div></div>'+
  '</div><div class="row"><div class="col-md-3"><p>Remark: '+row.remark+
  '</p></div><div class="col-md-3"><p>% Variance: '+row.variancePerc+
  '</p></div><div class="col-md-3"><p>Transporter: '+row.transporter_name+
  '</p></div></div>';
}

function view(id){
    $('#spinnerLoading').show();
    $.post('php/getEditGrading.php', {userID: id}, function(data){
        var obj = JSON.parse(data);
        
        if(obj.status === 'success'){
            $('#editGradesModal').find('#editId').val(obj.message.id);
            $('#editGradesModal').find('#editParentId').val(obj.message.parent_no);
            $('#editGradesModal').find('#editItemType').val(obj.message.itemType);
            $('#editGradesModal').find('#editGrossWeight').val(obj.message.grossWeight);
            $('#editGradesModal').find('#editLotNo').val(obj.message.lotNo);
            $('#editGradesModal').find('#editBTrayWeight').val(obj.message.tray_weight);
            $('#editGradesModal').find('#editBTrayNo').val(obj.message.bTrayNo);
            $('#editGradesModal').find('#editNetWeight').val(obj.message.netWeight);
            $('#editGradesModal').find('#editQty').val(obj.message.pieces);
            $('#editGradesModal').find('#editGrade').val(obj.message.grade);
            $('#editGradesModal').find('#editMoistureAfGrade').val(obj.message.moisture_after_grading);
            $('#editGradesModal').find('#editRemark').val(obj.message.remark);
            $('#editGradesModal').modal('show');

            if(obj.message.itemType == 'T1'){
                $('#editGradesModal').find("#editGrade").html($('#editGradesHidden').html());
            }
            else if(obj.message.itemType == 'T3'){
                $('#editGradesModal').find("#editGrade").html($('#editGrades2Hidden').html());
            }
            else if(obj.message.itemType == 'T4'){
                $('#editGradesModal').find("#editGrade").html($('#editGrades3Hidden').html());
            }
            
            $('#editGradeForm').validate({
                errorElement: 'span',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function (element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                }
            });
        }
        else if(obj.status === 'failed'){
            toastr["error"](obj.message, "Failed:");
        }
        else{
            toastr["error"]("Something wrong when activate", "Failed:");
        }
        $('#spinnerLoading').hide();
    });
}

function edit(id){
    $('#spinnerLoading').show();
    $.post('php/getEditGrading.php', {userID: id}, function(data){
        var obj = JSON.parse(data);
        
        if(obj.status === 'success'){
            $('#editGradesModal').find('#editId').val(obj.message.id);
            $('#editGradesModal').find('#editParentId').val(obj.message.parent_no);
            $('#editGradesModal').find('#editItemType').val(obj.message.itemType);
            $('#editGradesModal').find('#editGrossWeight').val(obj.message.grossWeight);
            $('#editGradesModal').find('#editLotNo').val(obj.message.lotNo);
            $('#editGradesModal').find('#editBTrayWeight').val(obj.message.tray_weight);
            $('#editGradesModal').find('#editBTrayNo').val(obj.message.bTrayNo);
            $('#editGradesModal').find('#editNetWeight').val(obj.message.netWeight);
            $('#editGradesModal').find('#editQty').val(obj.message.pieces);
            $('#editGradesModal').find('#editGrade').val(obj.message.grade);
            $('#editGradesModal').find('#editMoistureAfGrade').val(obj.message.moisture_after_grading);
            $('#editGradesModal').find('#editRemark').val(obj.message.remark);
            $('#editGradesModal').modal('show');

            if(obj.message.itemType == 'T1'){
                $('#editGradesModal').find("#editGrade").html($('#editGradesHidden').html());
            }
            else if(obj.message.itemType == 'T3'){
                $('#editGradesModal').find("#editGrade").html($('#editGrades2Hidden').html());
            }
            else if(obj.message.itemType == 'T4'){
                $('#editGradesModal').find("#editGrade").html($('#editGrades3Hidden').html());
            }
            
            $('#editGradeForm').validate({
                errorElement: 'span',
                errorPlacement: function (error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function (element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function (element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                }
            });
        }
        else if(obj.status === 'failed'){
            toastr["error"](obj.message, "Failed:");
        }
        else{
            toastr["error"]("Something wrong when activate", "Failed:");
        }
        $('#spinnerLoading').hide();
    });
}

function print(id){
    $.post('php/printGrading.php', {userID: id}, function(data){
        var obj = JSON.parse(data);

        if(obj.status === 'success'){
            var printWindow = window.open('', '', 'height=400,width=800');
            printWindow.document.write(obj.message);
            printWindow.document.close();
            setTimeout(function(){
                printWindow.print();
                printWindow.close();
            }, 500);
        }
        else if(obj.status === 'failed'){
            toastr["error"](obj.message, "Failed:");
        }
        else{
            toastr["error"]("Something wrong when activate", "Failed:");
        }
    });
}

function deactivate(id){
    $('#spinnerLoading').show();
    $.post('php/deleteReceives.php', {userID: id}, function(data){
        var obj = JSON.parse(data);
        
        if(obj.status === 'success'){
            toastr["success"](obj.message, "Success:");
            $.get('wgrade.php', function(data) {
                $('#mainContents').html(data);
                $('#spinnerLoading').hide();
            });
        }
        else if(obj.status === 'failed'){
            toastr["error"](obj.message, "Failed:");
            $('#spinnerLoading').hide();
        }
        else{
            toastr["error"]("Something wrong when activate", "Failed:");
            $('#spinnerLoading').hide();
        }
    });
}
</script>