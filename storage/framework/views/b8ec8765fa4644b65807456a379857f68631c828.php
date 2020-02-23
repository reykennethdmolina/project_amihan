<table class="table table-striped table-sm">
    <thead>
        <tr>
            <th>Name</th>
            <th>Education</th>
            <th>Religion</th>
            <th>Barangay</th>
            <th>Gender</th>
            <th>Civil</th>
            <th>Livelihood</th>
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td class="text-capitalize"><?php echo e($item->firstname); ?> <?php echo e($item->middlename); ?> <?php echo e($item->lastname); ?></td>
            <td class="text-capitalize"><?php echo e($item->education); ?></td>
            <td class="text-capitalize"><?php echo e($item->religion); ?></td>
            <td class="text-capitalize"><?php echo e($item->barangay); ?></td>
            <td class="text-capitalize"><?php echo e($item->gender); ?></td>
            <td class="text-capitalize"><?php echo e($item->civil_status); ?></td>
            <td class="text-capitalize"><?php echo e($item->livelihood_type); ?></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        
    </tbody>
</table>