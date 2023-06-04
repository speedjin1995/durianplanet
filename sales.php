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
				<h1 class="m-0 text-dark">Sales 销售</h1>
			</div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
	<div class="container-fluid">
        <div class="row">
            <div class="col-7">
                <div class="row">
                    <?php while($rowProducts=mysqli_fetch_assoc($products)){ ?>
                        <div class="col-3">
                            <div class="card" id="items<?=$rowProducts['id'] ?>" onclick="addItems('<?=$rowProducts['id'] ?>')">
                                <div class="card-header">
                                    <label><?=$rowProducts['item_name'] ?> </label>
                                </div>
                                <div class="card-body">
                                    <img src="assets/durian.jpeg" width="100%"/>
                                </div><!-- /.card-body -->
                            </div><!-- /.card -->
                        </div>
                    <?php } ?>
                </div>
            </div>
			<div class="col-5">
                <form role="form" id="saleForm">
                    <div class="card">
                        <div class="card-header">
                            <label>Your Orders </label>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Items</th>
                                        <th>Price Per Kg</th>
                                        <th>KG</th>
                                        <th>Price (RM)</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="TableId"></tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2"></th>
                                        <th>Sub Total Price (RM): </th>
                                        <th>
                                            <div class="form-group">
                                                <input type="number" class="form-control" name="subTotalPricing" id="subTotalPricing" step="0.01" placeholder="Enter Sub Total Price" value="0.00" readonly>
                                            </div>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th colspan="2"></th>
                                        <th>Discount (RM): </th>
                                        <th>
                                            <div class="form-group">
                                                <input type="number" class="form-control" name="totalDiscount" id="totalDiscount" step="0.01" placeholder="Enter Discount Value" value="0.00">
                                            </div>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th colspan="2"></th>
                                        <th>Total Price (RM): </th>
                                        <th>
                                            <div class="form-group">
                                                <input type="number" class="form-control" name="totalPricing" id="totalPricing" step="0.01" placeholder="Enter Total Price" value="0.00" readonly>
                                            </div>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                            <div class="row col-12">
                                <div class="form-group">
                                    <label for="itemType">Payment Methods 货品种类 *</label>
                                    <select class="form-control" style="width: 100%;" id="paymentMethod" name="paymentMethod" required>
                                        <option value="" selected disabled hidden>Please Select</option>
                                        <option value="e-wallet">e-wallet</option>
                                        <option value="cash">cash</option>
                                    </select>
                                </div>
                            </div>
                        </div><!-- /.card-body -->
                        <div class="card-foot">
                            <button type="button" class="btn btn-danger" id="cancelSales">Cancel 取消</button>
                            <button type="submit" class="btn btn-primary" name="submitsales" id="submitSales">Submit 提交</button>
                        </div><!-- /.card-foot -->
                    </div><!-- /.card -->
                </form>
			</div><!-- /.col -->
		</div><!-- /.row -->
	</div><!-- /.container-fluid -->
</section><!-- /.content -->
<input type="text" id="barcodeScan">

<div class="modal fade" id="editModal">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <form role="form" id="editForm">
            <div class="modal-header">
              <h4 class="modal-title">Price & Weight 价格与重量</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
                <div class="card-body">
                    <input type="hidden" class="form-control" name="editId" id="editId"/>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="itemType">Prices 价格 *</label>
                                <input type="text" class="form-control" name="editPrice" id="editPrice" placeholder="Enter Prices" readonly>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="form-group">
                                <label for="lotNo">Weight 重量 *</label>
                                <input type="number" class="form-control" name="editWeight" id="editWeight" placeholder="Enter Weight">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-danger" data-dismiss="modal">Close 关闭</button>
              <button type="submit" class="btn btn-primary" name="submit" id="submitEdit">Submit 提交</button>
            </div>
        </form>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<script type="text/html" id="addContents">
    <tr class="details">
        <td>
            <input type="hidden" class="form-control" name="cartId" id="cartId"/>
            <select class="form-control" style="width: 100%;" id="items" name="items" readonly>
                <option value="" selected disabled hidden>Please Select</option>
                <?php while($rowProducts2=mysqli_fetch_assoc($products2)){ ?>
                    <option value="<?=$rowProducts2['id'] ?>"><?=$rowProducts2['item_name'] ?></option>
                <?php } ?>
            </select>
        </td>
        <td>
            <div class="form-group">
                <input type="number" class="form-control" name="itemPrice" id="itemPrice" step="0.01" placeholder="Enter Item Price" readonly>
            </div>
        </td>
        <td>
            <div class="input-group">
                <input type="number" class="form-control" name="itemWeight" id="itemWeight" placeholder="Enter Item Weight" readonly>
            </div>
        </td>
        <td>
            <div class="form-group">
                <input type="number" class="form-control" name="totalPrice" id="totalPrice" step="0.01" placeholder="Enter Total Price" value="0.00" readonly>
            </div>
        </td>
        <td>
            <button class="btn btn-danger btn-sm" id="remove"><i class="fa fa-times"></i></button>
        </td>
    </tr>
</script>

<script>
var re3= /[0-9]/;
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
    
    $.validator.setDefaults({
        submitHandler: function () {
            if($('#editModal').hasClass('show')){
                //$('#spinnerLoading').show();
                var editId = $('#editModal').find('#editId').val();
                var editPrice = $('#editModal').find('#editPrice').val();
                var editWeight = $('#editModal').find('#editWeight').val();
                debugger;
                var totalPrice = parseFloat(parseFloat(editPrice) * parseFloat(editPrice)).toFixed(2);
                var subTotalPrice = parseFloat($('#subTotalPricing').val());
                subTotalPrice = parseFloat(parseFloat(subTotalPrice) + parseFloat(totalPrice)).toFixed(2);
                var totalDiscount = parseFloat($('#totalDiscount').val());
                var totalPricing = parseFloat(parseFloat(subTotalPrice) - parseFloat(totalDiscount)).toFixed(2);

                var $addContents = $("#addContents").clone();
                $("#TableId").append($addContents.html());

                $("#TableId").find('.details:last').attr("id", "detail" + size);
                $("#TableId").find('.details:last').attr("data-index", size);
                $("#TableId").find('#remove:last').attr("id", "remove" + size);

                $("#TableId").find('#items:last').attr('name', 'items['+size+']').attr("id", "items" + size).attr("required", true).val(editId);
                $("#TableId").find('#itemWeight:last').attr('name', 'itemWeight['+size+']').attr("id", "itemWeight" + size).attr("required", true).val(editWeight);
                $("#TableId").find('#itemPrice:last').attr('name', 'itemPrice['+size+']').attr("id", "itemPrice" + size).attr("required", true).val(editPrice);
                $("#TableId").find('#totalPrice:last').attr('name', 'totalPrice['+size+']').attr("id", "totalPrice" + size).val(totalPrice);
                
                size++;
                $('#editModal').modal('hide');
                //$('#spinnerLoading').hide();
                $('#subTotalPricing').val(subTotalPrice);
                $('#totalPricing').val(totalPricing);
            }
            else{
                $.post('php/receive.php', $('#saleForm').serialize(), function(data){
                    var obj = JSON.parse(data); 
                    
                    if(obj.status === 'success'){
                        toastr["success"](obj.message, "Success:");
                        
                        $.get('sales.php', function(data) {
                            $('#mainContents').html(data);
                            $('#spinnerLoading').hide();
                        });
                    }
                    else if(obj.status === 'failed'){
                        toastr["error"](obj.message, "Failed:");
                        $('#spinnerLoading').hide();
                    }
                    else{
                        toastr["error"]("Something wrong when submit", "Failed:");
                        $('#spinnerLoading').hide();
                    }
                });
            }
        }
    });

    $('#scanReceives').on('click', function(){
        $('#barcodeScan').trigger('focus');
    });

    $('#barcodeScan').on('change', function(){
        $('#spinnerLoading').show();
        var url = $(this).val();
        $(this).val('');

        $.get(url, function(data){
            var obj = JSON.parse(data);
            
            if(obj.status === 'success'){
                $('#editModal').find('#id').val(obj.message.id);
                $('#editModal').find('#itemType').val(obj.message.itemTypes);
                $('#editModal').find('#lotNo').val(obj.message.lotNo);
                $('#editModal').find('#bTrayNo').val(obj.message.bTrayNo);
                $('#editModal').find('#bTrayWeight').val(obj.message.trayWeight);
                $('#editModal').find('#grossWeight').val(obj.message.grossWeight);
                $('#editModal').find('#netWeight').val(obj.message.netWeight);
                $('#editModal').find('#moistureValue').val(obj.message.afterReceiving);
                $('#editModal').modal('show');

                $('#editForm').validate({
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
    });

    // Find and remove selected table rows
    $("#TableId tbody").on('click', 'button[name^="delete"]', function () {
        $(this).parents("tr").remove();
    });

    $('#addReceive').on('click', function(){
        $('#receiveModal').find('#id').val("");
        $('#receiveModal').find('#itemType').val('-');
        $('#receiveModal').find('#grossWeight').val("");
        $('#receiveModal').find('#lotNo').val("");
        $('#receiveModal').find('#bTrayWeight').val("");
        $('#receiveModal').find('#bTrayNo').val("");
        $('#receiveModal').find('#netWeight').val("");
        $('#receiveModal').modal('show');
        
        $("#TableId tbody").empty();

        $('#receiveForm').validate({
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

    $('#receiveModal').find('#grossWeight').on('change', function(){
        var grossWeight = $(this).val();
        var bTrayNo = 0;

        if($('#receiveModal').find('#bTrayWeight').val()){
            bTrayNo = $('#receiveModal').find('#bTrayWeight').val();
            var netweight = grossWeight - bTrayNo;
            $('#receiveModal').find('#netWeight').val(netweight.toFixed(2));

        }
        else{
            $('#receiveModal').find('#netWeight').val(grossWeight.toFixed(2));
        }
    });

    $('#receiveModal').find('#bTrayWeight').on('change', function(){
        var grossWeight = 0;
        var bTrayNo = $(this).val();

        if($('#receiveModal').find('#grossWeight').val()){
            grossWeight = $('#receiveModal').find('#grossWeight').val();
            var netweight = grossWeight - bTrayNo;
            $('#receiveModal').find('#netWeight').val(netweight.toFixed(2));
        }
        else{
            $('#receiveModal').find('#netWeight').val((0).toFixed(2));
        }
    });

    $('#editModal').find('#grossWeight').on('change', function(){
        var grossWeight = $(this).val();
        var bTrayNo = 0;

        if($('#editModal').find('#bTrayWeight').val()){
            bTrayNo = $('#editModal').find('#bTrayWeight').val();
            var netweight = grossWeight - bTrayNo;
            $('#editModal').find('#netWeight').val(netweight.toFixed(2));
        }
        else{
            $('#editModal').find('#netWeight').val(grossWeight.toFixed(2));
        }
    });

    $('#editModal').find('#bTrayWeight').on('change', function(){
        var grossWeight = 0;
        var bTrayNo = $(this).val();

        if($('#editModal').find('#grossWeight').val()){
            grossWeight = $('#editModal').find('#grossWeight').val();
            var netweight = grossWeight - bTrayNo;
            $('#editModal').find('#netWeight').val(netweight.toFixed(2));
        }
        else{
            $('#editModal').find('#netWeight').val((0).toFixed(2));
        }
    });

    $('#itemType').on('change', function(){
        var itemType = $(this).val();

        if(itemType == 'T3' || itemType == 'T1'){
            $("#bTrayWeight").removeAttr("required");
            $("#bTrayNo").removeAttr("required");
        }
        else{
            //$("#bTrayWeight").attr("required","required");
            //$("#bTrayNo").attr("required","required");
        }
    });

    $('#lotNo').on('change', function(){
        var size = $("#TableId").find("tr").length;
        $("#bTrayNo").val($('#lotNo').val() + padLeadingZeros((size).toString(), 3));
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
        $("#receiveTable").DataTable().clear().destroy();

        //Create new Datatable
        table = $("#receiveTable").DataTable({
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
            'url':'php/filterReceive.php',
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
        { data: 'tray_no' },
        { data: 'tray_weight' },
        { data: 'gross_weight' },
        { data: 'net_weight' },
        { data: 'moisture_after_receiving' },
        { data: 'updated_datetime' },
        { 
            data: 'id',
            render: function ( data, type, row ) {
                return '<div class="row"><div class="col-3"><button type="button" id="edit'+data+'" onclick="edit('+data+')" class="btn btn-success btn-sm"><i class="fas fa-pen"></i></button></div><div class="col-3"><button type="button" id="print'+data+'" onclick="print('+data+')" class="btn btn-info btn-sm"><i class="fas fa-print"></i></button></div><div class="col-3"><button type="button" id="deactivate'+data+'" onclick="deactivate('+data+')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></div></div>';
            }
        }
        ],
        "rowCallback": function( row, data, index ) {
            $('td', row).css('background-color', '#E6E6FA');
        }

        });

        $('#spinnerLoading').hide();
    });

    $('#grossWeightSyncBtn').on('click', function(){
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
                $('#receiveModal').find('#grossWeight').val(parseFloat(text).toFixed(2));
                $('#receiveModal').find('#grossWeight').trigger('change');
            }
            else{
                toastr["error"]("Failed to get the reading!", "Failed:");
            }
        });
    });

    $('#trayWeightSyncBtn').on('click', function(){
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
                $('#receiveModal').find('#bTrayWeight').val(parseFloat(text).toFixed(2));
                $('#receiveModal').find('#bTrayWeight').trigger('change');
            }
            else{
                toastr["error"]("Failed to get the reading!", "Failed:");
            }
        });
    });

    $('#editTrayWeightBtn').on('click', function(){
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
                $('#editModal').find('#bTrayWeight').val(parseFloat(text).toFixed(2));
                $('#editModal').find('#bTrayWeight').trigger('change');
            }
            else{
                toastr["error"]("Failed to get the reading!", "Failed:");
            }
        });
    });

    $('#editGrossWeightBtn').on('click', function(){
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
                $('#editModal').find('#grossWeight').val(parseFloat(text).toFixed(2));
                $('#editModal').find('#grossWeight').trigger('change');
            }
            else{
                toastr["error"]("Failed to get the reading!", "Failed:");
            }
        });
    });

    $('#cancelSales').on('click', function(){
        $.get('sales.php', function(data) {
            $('#mainContents').html(data);
        });
    });
});

function addItems(id){
    $('#spinnerLoading').show();
    $.post('php/getGrades.php', {userID: id}, function(data){
        var obj = JSON.parse(data);
        
        if(obj.status === 'success'){
            $('#editModal').find('#editId').val(obj.message.id);
            $('#editModal').find('#editPrice').val(obj.message.grade);
            $('#editModal').find('#editWeight').val("");
            $('#editModal').modal('show');
            
            $('#editForm').validate({
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
            $('#spinnerLoading').hide();
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

function print(id){
    $.post('php/printReceive.php', {userID: id}, function(data){
        var obj = JSON.parse(data);

        if(obj.status === 'success'){
            var printWindow = window.open('', '', 'height=400,width=800');
            printWindow.document.write(obj.message);
            printWindow.document.close();
            setTimeout(function(){
                printWindow.print();
                printWindow.close();
            }, 1000);
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
    alert(id);
    $('#spinnerLoading').show();
    $.post('php/deleteReceives.php', {userID: id}, function(data){
        var obj = JSON.parse(data);
        
        if(obj.status === 'success'){
            toastr["success"](obj.message, "Success:");
            $.get('receive.php', function(data) {
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

function padLeadingZeros(num, size) {
    var s = num+"";
    while (s.length < size) s = "0" + s;
    return s;
}
</script>