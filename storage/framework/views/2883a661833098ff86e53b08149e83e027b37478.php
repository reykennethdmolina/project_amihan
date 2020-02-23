<?php $__env->startSection('content'); ?>
<div>
    <div class="row justify-content-center mt-3">

        <div class="col-12" style="background-color:white">

            <form style="font-size:12px;">
                <div class="form-row">
                    <div class="form-group col-md-2">
                        <label style="font-weight:10">Report</label>
                        <select id="report" class="form-control form-control-sm">
                            <option value="1">Registry Listing</option>
                            <!-- <option value="2">Registry Detail</option> -->
                            <!-- <option value="2">PO Listing</option>
                            <option value="3">PO Listing with Items</option>
                            <option value="4">PO Consolidated Allocation</option> -->
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label style="font-weight:10">Created From</label>
                        <input type="text" class="dpicker form-control form-control-sm" data-date-format='yyyy-mm-dd' value="<?php echo e($date['from']); ?>"  id="createdfrom">
                    </div>
                    <div class="form-group col-md-2">
                        <label style="font-weight:10">Created To</label>
                        <input type="text" class="dpicker form-control form-control-sm" data-date-format='yyyy-mm-dd' value="<?php echo e($date['to']); ?>"  id="createdto">
                    </div>
                    <div class="form-group col-md-3">
                        <label style="font-weight:10">Education</label>
                        <select id="education" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="noschooling">No Schooling</option>
                            <option value="elem_level_1-5">Elem. Level (1-5)</option>
                            <option value="elem_grad">Elem. Graduate</option>
                            <option value="highschool_level">High School Level</option>
                            <option value="highschool_grad">High School Grad</option>
                            <option value="college_level_1-3">College Lvl (1-3yrs)</option>
                            <option value="college_grad">College Graduate</option>
                            <option value="post-graduate">Post-Graduate</option>
                            <option value="vocational">Vocational</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label style="font-weight:10">Religion</label>
                        <select id="religion" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="roman-catholic">Roman Catholic</option>
                            <option value="muslim">Muslim</option>
                            <option value="iglesia-ni-cristo">Iglesia ni Cristo</option>
                            <option value="christian">Christian</option>
                            <option value="protestant">Protestant</option>
                            <option value="others">Others</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label style="font-weight:10">Barangay</label>
                        <input type="text" class="form-control form-control-sm" id="barangay">
                    </div>    
                    <div class="form-group col-md-2">
                        <label style="font-weight:10">Gender</label>
                        <select id="gender" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label style="font-weight:10">Civil Status</label>
                        <select id="civil_status" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="single">Single</option>
                            <option value="married">Married</option>
                            <option value="separated">Separated</option>
                            <option value="widowed">Widowed</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label style="font-weight:10">Livelihood</label>
                        <select id="livelihood" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="subsistence">Subsistence Farming</option>
                            <option value="commercial">Commercial Farming</option>
                        </select>
                    </div>
                </div>   
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <button type="button" id="generate" class="btn btn-sm btn-primary"><i class="fas fa-spinner"> </i> Generate</button>
                        <!-- <button type="button" id="pdf" class="btn btn-sm btn-danger"><i class="fas fa-file-pdf"> </i> PDF</button> -->
                        <button type="button" id="excel" class="btn btn-sm btn-success"><i class="fas fa-file-excel"> </i> XLS</button>
                    </div>
                </div>             
            </form>

        </div>

        <div class="col-xs-12 table-responsive" style="font-size:12px;">
            <div id="list"></div>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script type="text/javascript">
$(function() {

    $('#generate').on("click", function(event) { 
        var report = $('#report').val();
        var createdfrom = $('#createdfrom').val();
        var createdto = $('#createdto').val();
        var education = $('#education').val();
        var religion = $('#religion').val();
        var barangay = $('#barangay').val().trim();
        var gender = $('#gender').val();
        var civil_status = $('#civil_status').val();
        var livelihood = $('#livelihood').val();

        $.ajax({
            url: "<?php echo e(route('report-registry.generate')); ?>",
            type: "GET",
            data: { report: report, createdfrom: createdfrom, createdto: createdto, education: education, religion: religion, barangay: barangay, gender: gender, civil_status: civil_status, livelihood: livelihood}
        }).done(function (data) {
            $('#list').html(data['html']);  
        });

    });

    // $('#pdf').on("click", function(event) { 
    //     var report = $('#report').val();
    //     var podatefrom = $('#podatefrom').val();
    //     var podateto = $('#podateto').val();
    //     var dateneeded = $('#dateneeded').val();
    //     var pickuplocation = $('#pickuplocation').val();
    //     var customer = $('#customer').val().trim();
    //     var refno = $('#refno').val();
    //     var status = $('#status').val();
    //     var payment_status = $('#payment_status').val();
    //     var paymentMode = $('#paymentMode').val();

    //     if (podatefrom == '' || podateto == '') {
    //         alert('PO Date From and PO Date To must not be empty!');
    //         return false;
    //     }

    //     var wurl = "<?php echo e(route('report-po.pdf')); ?>"+'?report='+report+'&podatefrom='+podatefrom+'&podateto='+podateto+'&dateneeded='+dateneeded+'&pickuplocation='+pickuplocation+'&customer='+customer+'&refno='+refno+'&status='+status+'&payment_status='+payment_status+'&paymentMode='+paymentMode;
    //     window.open(wurl);
    // });

    $('#excel').on("click", function(event) { 
        var report = $('#report').val();
        var createdfrom = $('#createdfrom').val();
        var createdto = $('#createdto').val();
        var education = $('#education').val();
        var religion = $('#religion').val();
        var barangay = $('#barangay').val().trim();
        var gender = $('#gender').val();
        var civil_status = $('#civil_status').val();
        var livelihood = $('#livelihood').val();

        var wurl = "<?php echo e(route('report-registry.excel')); ?>"+'?report='+report+'&createdfrom='+createdfrom+'&createdto='+createdto+'&education='+education+'&barangay='+barangay+'&gender='+gender+'&civil_status='+civil_status+'&livelihood='+livelihood;
        window.open(wurl);
    });

});  
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master-blade', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>