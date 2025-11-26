<!-- <div class="d-flex gap-2 mb-3">

    <a href="list_orders.php" 
       class="btn btn-success">
       Tất cả đơn
    </a>

    <a href="pending.php" 
       class="btn btn-warning text-white">
       Chờ xử lý
    </a>

    <a href="completed.php" 
       class="btn btn-primary">
       Hoàn thành
    </a>

    <a href="canceled.php" 
       class="btn btn-danger">
       Đã huỷ
    </a> -->


<div class="order-filter mt-3 mb-4">
    <a href="list_orders.php" class="filter-btn <?= $active=='all'?'active':'' ?>">
        Tất cả đơn
    </a>

    <a href="pending.php" class="filter-btn <?= $active=='pending'?'active':'' ?>">
        Chờ xử lý
    </a>

    <a href="completed.php" class="filter-btn <?= $active=='completed'?'active':'' ?>">
        Hoàn thành
    </a>

    <a href="canceled.php" class="filter-btn <?= $active=='canceled'?'active':'' ?>">
        Đã huỷ
    </a>
</div>


