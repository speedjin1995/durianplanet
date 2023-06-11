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

$reasons = $db->query("SELECT * FROM reasons WHERE deleted = '0'");
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
                <input type="hidden" class="form-control" id="id" name="id">
                <input type="hidden" class="form-control" id="parentId" name="parentId">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="lotNo">Lot No 批号</label>
                            <input type="text" class="form-control" name="lotNo" id="lotNo" placeholder="Enter Lot No">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                        <label for="itemType">Item Types 货品种类</label>
                            <input type="text" class="form-control" name="itemType" id="itemType" placeholder="Enter item type" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <hr>

            <div class="row">
                <h4>Add Grading 添加分级</h4>
                <button style="margin-left:auto;margin-right: 25px;" type="button" class="btn btn-primary add-row">Add New</button>
            </div>

            

            <table id="TableId">
                <thead>
                    <tr>
                        <th>Lot No <br>批号</th>
                        <th>Grade <br>等级</th>
                        <th>Box/Tray No <br>桶/托盘代号</th>
                        <th>Box/Tray Weight <br>桶/托盘重量(G)</th>
                        <th>Gross weight <br>分级毛重(G)</th>
                        <th>Qty <br>片数(pcs)</th>
                        <th>Net weight <br>分级净重(G)</th>
                        <th>Moisture after grading <br>分级后湿度(%)</th>
                        <th>Status <br>状态</th>
                        <th>Action <br>行动</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
			
			<div class="card-footer">
				<button class="btn btn-success" id="saveProfile"><i class="fas fa-save"></i>Save 保存</button>
			</div>
		</form>
	</div>
</section>

<input type="text" id="barcodeScan">

<div class="modal fade" id="gradesModal">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <form role="form" id="gradeForm">
            <div class="modal-header">
              <h4 class="modal-title">Repacking 重新包装</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body" id="TableId">
                <div class="card-body">
                    <input type="hidden" class="form-control" id="id" name="id">
                    <input type="hidden" class="form-control" id="parentId" name="parentId">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="lotNo">Lot No 批号</label>
                                <input type="text" class="form-control" name="lotNo" id="lotNo" placeholder="Enter Lot No">
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                            <label for="itemType">Item Types 货品种类</label>
                                <input type="text" class="form-control" name="itemType" id="itemType" placeholder="Enter item type" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>

                <div class="row">
                    <h4>Add Grading 添加分级</h4>
                    <button style="margin-left:auto;margin-right: 25px;" type="button" class="btn btn-primary add-row">Add New</button>
                </div>

                

                <table id="TableId">
                    <thead>
                        <tr>
                            <th>Lot No <br>批号</th>
                            <th>Grade <br>等级</th>
                            <th>Box/Tray No <br>桶/托盘代号</th>
                            <th>Box/Tray Weight <br>桶/托盘重量(G)</th>
                            <th>Gross weight <br>分级毛重(G)</th>
                            <th>Qty <br>片数(pcs)</th>
                            <th>Net weight <br>分级净重(G)</th>
                            <th>Moisture after grading <br>分级后湿度(%)</th>
                            <th>Status <br>状态</th>
                            <th>Action <br>行动</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
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

<div class="modal fade" id="editGradesModal">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <form role="form" id="editGradeForm">
            <div class="modal-header">
              <h4 class="modal-title">Edit Grades 修改品规</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
                <div class="card-body">
                    <input type="hidden" class="form-control" id="editId" name="editId">
                    <input type="hidden" class="form-control" id="editParentId" name="editParentId">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editLotNo">Lot No 批号</label>
                                <input type="text" class="form-control" name="editLotNo" id="editLotNo" placeholder="Enter Lot No">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editBTrayNo">Box/Tray No 桶/托盘代号</label>
                                <input type="text" class="form-control" name="editBTrayNo" id="editBTrayNo" placeholder="Enter Box/Tray No">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                            <label for="editItemType">Item Types 货品种类</label>
                                <input type="text" class="form-control" name="editItemType" id="editItemType" placeholder="Enter item type">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editGrossWeight">Gross weight 分级毛重(G)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="editGrossWeight" id="editGrossWeight" placeholder="Enter Grading Gross weight">
                                    <button type="button" class="btn btn-primary" id="editGrossWeightBtn"><i class="fas fa-sync"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editBTrayWeight">Box/Tray Weight 桶/托盘重量(G)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="editBTrayWeight" id="editBTrayWeight" placeholder="Enter Box/Tray Weight">
                                    <button type="button" class="btn btn-primary" id="editTrayWeightBtn"><i class="fas fa-sync"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editNetWeight">Net weight 分级净重(G)</label>
                                <input type="number" class="form-control" name="editNetWeight" id="editNetWeight" placeholder="Enter Grading Net weight" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editQty">Qty 片数 (pcs) <!--span style="color:red;">*</span--></label>
                                <input type="number" class="form-control" name="editQty" id="editQty" placeholder="Enter qty">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editGrade">Grade 等级</label>
                                <select class="form-control" style="width: 100%;" id="editGrade" name="editGrade"></select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                                <div class="form-group">
                                    <label for="editMoistureAfGrade">Moisture after grading 分级后湿度(%)<span style="color:red;">*</span></label>
                                    <input type="number" class="form-control" name="editMoistureAfGrade" id="editMoistureAfGrade" placeholder="Enter Moisture after grading" max="100">
                                </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editRemark">Remark 备注</label>
                                <textarea class="form-control" name="editRemark" id="editRemark" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-danger editCloseBtn" data-dismiss="modal">Close 关闭</button>
              <button type="submit" class="btn btn-primary editSubmitBtn" name="submit" id="submitLot">Submit 提交</button>
            </div>
        </form>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<div id="addContents">
    <div class="card-body details">
        <div class="row">
            <div class="col-md-2">
                <div class="form-group">
                <label for="itemType">Status 状态</label>
                    <select class="form-control" style="width: 100%;" id="newStatus" name="newStatus">
                        <option selected="selected" value="PASSED">Passed 合格</option>
                        <option value="REJECT">Reject 不合格</option>
                        <option value="LAB">Lab 化验</option>
                    </select>
                </div>
            </div>

            <div class="col-md-3" >
                <div class="form-group" id="hideReason" hidden>
                    <label for="itemType">Reason 状态</label>
                    <select class="form-control" style="width: 100%;" id="newReason" name="newReason"></select>
                </div>
            </div>

            <div class="col-md-3 radioTray">
                <div class="form-check form-check-inline mr-5">
                    <input class="form-check-input" type="radio" name="sameTray" id="sameTrayYes" value="Yes">
                    <label class="form-check-label" for="sameTrayYes">Same Tray <br>同样桶/托盘</label>
                </div>

                <div class="form-check form-check-inline ml-10">
                    <input class="form-check-input" type="radio" name="sameTray" id="sameTrayNo" value="No" checked>
                    <label class="form-check-label" for="sameTrayNo">Non-Same Tray <br>不同样桶/托盘</label>
                </div>
            </div>

            <div class="col-md-3" >
                <div class="form-group" id="hideOldTrayNo" hidden>
                    <label for="bTrayNo">Reused Tray No 重复用桶/托盘代号</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="bTrayNo" id="bTrayNo" placeholder="Enter Box/Tray No">
                        <button type="button" class="btn btn-primary" id="oldTrayNoSyncBtn"><i class="fas fa-download"></i></button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-1">
                <button class="btn btn-danger btn-sm" id="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="newLotNo">Lot No 批号</label>
                    <input type="text" class="form-control" name="newLotNo" id="newLotNo" placeholder="Enter Lot No">
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="newGrade">Grade 等级</label>
                    <select class="form-control" style="width: 100%;" id="newGrade" name="newGrade"></select>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="newTrayNo">Box/Tray No 桶/托盘代号</label>
                    <input type="text" class="form-control" name="newTrayNo" id="newTrayNo" placeholder="Enter Box/Tray No">
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="newTrayWeight">Box/Tray Weight 桶/托盘重量(G) <span style="color:red;">*</span></label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="newTrayWeight" id="newTrayWeight" placeholder="Enter Box/Tray Weight">
                        <button type="button" class="btn btn-primary" id="trayWeightSyncBtn"><i class="fas fa-sync"></i></button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="newGrossWeight">Gross weight 分级毛重(G) <span style="color:red;">*</span></label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="newGrossWeight" id="newGrossWeight" placeholder="Enter Grading Gross weight">
                        <button type="button" class="btn btn-primary" id="grossWeightSyncBtn"><i class="fas fa-sync"></i></button>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="qty">Qty 片数 (pcs) <!--span style="color:red;">*</span--></label>
                    <input type="number" class="form-control" name="qty" id="qty" placeholder="Enter qty">
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="newNetWeight">Net weight 分级净重(G)</label>
                    <input type="number" class="form-control" name="newNetWeight" id="newNetWeight" placeholder="Enter Grading Net weight">
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label for="moistureAfGrade">Moisture after grading 分级后湿度(%)<span style="color:red;">*</span></label>
                    <input type="number" class="form-control" name="moistureAfGrade" id="moistureAfGrade" placeholder="Enter Moisture after grading" max="100">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="remark">Remark 备注</label>
                    <textarea class="form-control" name="remark" id="remark" rows="3"></textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var contentIndex = 0;
var size = $("#TableId").find(".details").length
var contentItems = "T4";

$(function () {
    
});
</script>