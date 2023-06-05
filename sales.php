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
                                <!--div class="card-body">
                                    <img src="assets/<?=$rowProducts['img'] ?>" width="100%"/>
                                </div><!-- /.card-body -->
                            </div><!-- /.card -->
                        </div>
                    <?php } ?>
                </div>
            </div>
			<div class="col-5">
                <form role="form" id="saleForm" method="post" action="php/receive.php">
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
                            </table><br>
                            <div class="row col-12">
                                <div class="form-group">
                                    <label for="itemType">Payment Methods 付款方式 *</label>
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

    $('#saleForm').validate({
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
    
    $.validator.setDefaults({
        submitHandler: function () {
            if($('#editModal').hasClass('show')){
                //$('#spinnerLoading').show();
                var editId = $('#editModal').find('#editId').val();
                var editPrice = $('#editModal').find('#editPrice').val();
                var editWeight = $('#editModal').find('#editWeight').val();
                var totalPrice = parseFloat(parseFloat(editPrice) * parseFloat(editWeight)).toFixed(2);
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

    $('#cancelSales').on('click', function(){
        $.get('sales.php', function(data) {
            $('#mainContents').html(data);
        });
    });

    $('#totalDiscount').on('change', function(){
        if($('#subTotalPricing').val()){
            var subTotalPrice = parseFloat($('#subTotalPricing').val());
            var totalDiscount = parseFloat($(this).val());
            var totalPricing = parseFloat(parseFloat(subTotalPrice) - parseFloat(totalDiscount)).toFixed(2);
            $('#totalPricing').val(totalPricing);
        }
        else{
            $('#totalPricing').val(0.00);
        }
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