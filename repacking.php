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
}

$products = $db->query("SELECT * FROM items WHERE item_status = '0' AND packing = '2'");
$products2 = $db->query("SELECT * FROM items WHERE item_status = '0' AND packing <> '2'");
?>

<section class="content-header">
	<div class="container-fluid">
		<div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0 text-dark">Repacking 重新包装</h1>
			</div>
		</div>
	</div>
</section>

<section class="content" style="min-height:700px;">
	<div class="card">
		<form role="form" id="profileForm" novalidate="novalidate">
            <div class="card-body">
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="lotNo">Products 产品</label>
                            <select class="form-control" style="width: 100%;" id="items" name="items">
                                <option value="" selected disabled hidden>Please Select</option>
                                <?php while($rowProducts=mysqli_fetch_assoc($products)){ ?>
                                    <option value="<?=$rowProducts['id'] ?>"><?=$rowProducts['item_name'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <label for="lotNo">Products Weight 产品重量</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="productWeight" id="productWeight" placeholder="Enter Item Weight">
                            <button type="button" class="btn btn-primary" id="productWeightSyncBtn"><i class="fas fa-sync"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <hr>

            <div class="row">
                <h4>Add Packing 包装</h4>
                <button style="margin-left:auto;margin-right: 25px;" type="button" class="btn btn-primary add-row">Add New</button>
            </div>

            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Product <br>产品</th>
                        <th>Weight <br>重量</th>
                        <th>Action <br>行动</th>
                    </tr>
                </thead>
                <tbody id="TableId">
                </tbody>
            </table>
			
			<div class="card-footer">
				<button class="btn btn-success" id="saveProfile"><i class="fas fa-save"></i>Save 保存</button>
			</div>
		</form>
	</div>
</section>

<input type="text" id="barcodeScan">

<script type="text/html" id="addContents">
    <tr class="details">
        <td>
            <select class="form-control" style="width: 100%;" id="itemsRepack" name="itemsRepack">
                <option value="" selected disabled hidden>Please Select</option>
                <?php while($rowProducts2=mysqli_fetch_assoc($products2)){ ?>
                    <option value="<?=$rowProducts2['id'] ?>"><?=$rowProducts2['item_name'] ?></option>
                <?php } ?>
            </select>
        </td>
        <td>
            <div class="input-group">
                <input type="number" class="form-control" name="itemWeight" id="itemWeight" placeholder="Enter Item Weight">
                <button type="button" class="btn btn-primary" id="itemWeightSyncBtn"><i class="fas fa-sync"></i></button>
            </div>
        </td>
        <td>
            <button class="btn btn-danger btn-sm" id="remove"><i class="fa fa-times"></i></button>
        </td>
    </tr>
</script>

<script>
var contentIndex = 0;
var size = $("#TableId").find(".details").length

$(function () {
    $('#profileForm').validate({
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
            $('#spinnerLoading').show();
            $.post('php/repacking.php', $('#profileForm').serialize(), function(data){
                var obj = JSON.parse(data); 
                
                if(obj.status === 'success'){
                    toastr["success"](obj.message, "Success:");
                    
                    $.get('repacking.php', function(data) {
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
    });

    $('#productWeightSyncBtn').on('click', function(){
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
                $('input[name^="productWeight"]').val(parseFloat(text).toFixed(2));
            }
            else{
                toastr["error"]("Failed to get the reading!", "Failed:");
            }
        });
    });

    $('.add-row').on('click', function(){
        var $addContents = $("#addContents").clone();
        $("#TableId").append($addContents.html());

        $("#TableId").find('.details:last').attr("id", "detail" + size);
        $("#TableId").find('.details:last').attr("data-index", size);
        $("#TableId").find('#remove:last').attr("id", "remove" + size);
        $("#TableId").find('#itemWeightSyncBtn:last').attr("id", "itemWeightSyncBtn" + size);

        $("#TableId").find('#itemsRepack:last').attr('name', 'itemsRepack['+size+']').attr("id", "itemsRepack" + size).attr("required", true);
        $("#TableId").find('#itemWeight:last').attr('name', 'itemWeight['+size+']').attr("id", "itemWeight" + size).attr("required", true);
        
        size++;
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

    $("#TableId").on('click', 'button[id^="remove"]', function () {
        $("#TableId").append('<input type="hidden" name="deleted[]" value="'+index+'"/>');
        $(this).parents('.details').remove();
    });
});
</script>