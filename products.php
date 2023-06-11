<?php
require_once 'php/db_connect.php';

session_start();

if(!isset($_SESSION['userID'])){
  echo '<script type="text/javascript">';
  echo 'window.location.href = "login.html";</script>';
}
else{
  $user = $_SESSION['userID'];
  $category = $db->query("SELECT * FROM category WHERE item_status = '0'");
  $packing = $db->query("SELECT * FROM packing WHERE item_status = '0'");
}
?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0 text-dark">Products 产品</h1>
			</div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
	<div class="container-fluid">
        <div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-header">
                        <div class="row">
                            <div class="col-9"></div>
                            <div class="col-3">
                                <button type="button" class="btn btn-block bg-gradient-warning btn-sm" id="addGrades">Add Products 新增产品</button>
                            </div>
                        </div>
                    </div>
					<div class="card-body">
						<table id="gradeTable" class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>No. 排号</th>
                                    <th>Product Name 产品名称</th>
                                    <th>Product Price 产品价格</th>
									<th></th>
								</tr>
							</thead>
						</table>
					</div><!-- /.card-body -->
				</div><!-- /.card -->
			</div><!-- /.col -->
		</div><!-- /.row -->
	</div><!-- /.container-fluid -->
</section><!-- /.content -->

<div class="modal fade" id="gradeModal">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <form role="form" id="gradeForm" method="post" action="php/grades.php" enctype="multipart/form-data">
            <div class="modal-header">
              <h4 class="modal-title">Add Products 新增产品</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="card-body">
                <div class="form-group">
                  <input type="hidden" class="form-control" id="id" name="id">
                </div>
                <div class="form-group">
                    <label for="fileToUpload">Image</label>
                    <div id="image-preview">
                        <label for="image-upload" id="image-label">Choose Image</label>
                        <input type="file" name="image-upload" id="image-upload" />
                    </div>
                </div>
                <div class="form-group">
                  <label for="market">Product Name 产品名称 *</label>
                  <input type="text" class="form-control" name="code" id="code" placeholder="Enter Product Name" required>
                </div>
                <div class="form-group">
                    <label for="market">Product Category 产品类别 *</label>
                    <select class="form-control" style="width: 100%;" id="category" name="category" required>
                        <option value="" selected disabled hidden>Please Select</option>
                        <?php while($rowProducts2=mysqli_fetch_assoc($category)){ ?>
                            <option value="<?=$rowProducts2['id'] ?>"><?=$rowProducts2['category'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="market">Packing/UOM 包装 *</label>
                    <select class="form-control" style="width: 100%;" id="packing" name="packing" required>
                        <option value="" selected disabled hidden>Please Select</option>
                        <?php while($rowProducts=mysqli_fetch_assoc($packing)){ ?>
                            <option value="<?=$rowProducts['id'] ?>"><?=$rowProducts['packing_name'] ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                  <label for="grades">Product Price 产品价格 *</label>
                  <input type="number" class="form-control" name="grades" id="grades" step="0.01" placeholder="Enter Product Price" onchange="setTwoNumberDecimal" required>
                </div>
              </div>
            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-danger" data-dismiss="modal">Close 关闭</button>
              <button type="submit" class="btn btn-primary" name="submit" id="submitLot">Submit 提交</button>
            </div>
        </form>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<script>
$(function () {
    $("#gradeTable").DataTable({
        "responsive": true,
        "autoWidth": false,
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'order': [[ 1, 'asc' ]],
        'columnDefs': [ { orderable: false, targets: [0] }],
        'ajax': {
            'url':'php/loadGrades.php'
        },
        'columns': [
            { data: 'counter' },
            { data: 'item_name' },
            { data: 'item_price' },
            { 
                data: 'id',
                render: function ( data, type, row ) {
                    return '<div class="row"><div class="col-3"><button type="button" id="edit'+data+'" onclick="edit('+data+')" class="btn btn-success btn-sm"><i class="fas fa-pen"></i></button></div><div class="col-3"><button type="button" id="deactivate'+data+'" onclick="deactivate('+data+')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></div></div>';
                }
            }
        ],
        "rowCallback": function( row, data, index ) {
            $('td', row).css('background-color', '#E6E6FA');
        },        
    });

    $.uploadPreview({
        input_field: "#image-upload",   // Default: .image-upload
        preview_box: "#image-preview",  // Default: .image-preview
        label_field: "#image-label",    // Default: .image-label
        label_default: "Choose Image",   // Default: Choose File
        label_selected: "Change Image",  // Default: Change File
        no_label: false                 // Default: false
    });
    
    $.validator.setDefaults({
        submitHandler: function () {
            $('#spinnerLoading').show();

            $.ajax({
                type: "POST",
                enctype: 'multipart/form-data',
                url: "php/grades.php",
                data: $('#gradeForm').serialize(),
                processData: false,
                contentType: false,
                cache: false,
                timeout: 60000,
                success: function (data) {
                    var obj = JSON.parse(data); 
                
                    if(obj.status === 'success'){
                        $('#gradeModal').modal('hide');
                        toastr["success"](obj.message, "Success:");
                        
                        $.get('products.php', function(data) {
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
                },
                error: function (e) {
                    toastr["error"](e.responseText, "Failed:");
                    $('#spinnerLoading').hide();
                }
            });
        }
    });

    $('#addGrades').on('click', function(){
        $('#gradeModal').find('#id').val("");
        $('#gradeModal').find('#code').val("");
        $('#gradeModal').find('#grades').val("");
        $('#gradeModal').find('#category').val("");
        $('#gradeModal').find('#packing').val("");
        $('#gradeModal').modal('show');
        
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
});

function edit(id){
    $('#spinnerLoading').show();
    $.post('php/getGrades.php', {userID: id}, function(data){
        var obj = JSON.parse(data);
        
        if(obj.status === 'success'){
            $('#gradeModal').find('#id').val(obj.message.id);
            $('#gradeModal').find('#code').val(obj.message.class);
            $('#gradeModal').find('#grades').val(obj.message.grade);
            $('#gradeModal').find('#category').val(obj.message.category);
            $('#gradeModal').find('#packing').val(obj.message.packing);
            $('#gradeModal').modal('show');
            
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

function deactivate(id){
    $('#spinnerLoading').show();
    $.post('php/deleteGrades.php', {userID: id}, function(data){
        var obj = JSON.parse(data);
        
        if(obj.status === 'success'){
            toastr["success"](obj.message, "Success:");
            $.get('products.php', function(data) {
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