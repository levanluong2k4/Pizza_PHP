<?php
require __DIR__ . '/../../../includes/db_connect.php';

// Query to get top purchasing areas by province, district, ward
$sql = "
    SELECT
        k.tinhthanhpho,
        k.huyenquan,
        k.xaphuong,
        COUNT(DISTINCT d.MaDH) AS so_don_hang,
        COALESCE(SUM(d.TongTien), 0) AS tong_tien
    FROM khachhang k
    LEFT JOIN donhang d ON k.MaKH = d.MaKH AND d.trangthai = 'Giao thành công'
    WHERE k.tinhthanhpho IS NOT NULL AND k.tinhthanhpho != ''
    GROUP BY k.tinhthanhpho, k.huyenquan, k.xaphuong
    ORDER BY tong_tien DESC, so_don_hang DESC
    LIMIT 50
";

$result = mysqli_query($ketnoi, $sql);
$areas = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get unique provinces for filter
$sql_provinces = "SELECT DISTINCT tinhthanhpho FROM khachhang WHERE tinhthanhpho IS NOT NULL AND tinhthanhpho != '' ORDER BY tinhthanhpho";
$provinces_result = mysqli_query($ketnoi, $sql_provinces);
$provinces = mysqli_fetch_all($provinces_result, MYSQLI_ASSOC);

// Get all locations for JavaScript
$sql_locations = "SELECT DISTINCT tinhthanhpho, huyenquan, xaphuong FROM khachhang WHERE tinhthanhpho IS NOT NULL AND tinhthanhpho != '' AND huyenquan IS NOT NULL AND huyenquan != '' AND xaphuong IS NOT NULL AND xaphuong != '' ORDER BY tinhthanhpho, huyenquan, xaphuong";
$locations_result = mysqli_query($ketnoi, $sql_locations);
$locations = mysqli_fetch_all($locations_result, MYSQLI_ASSOC);
?>

<?php include '../../../admin/navbar_admin.php'; ?>

<style>
    .main-title { color: #28a745; font-weight: 700; margin-top: 40px; text-align: center; }
    table { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); }
    th { background-color: #28a745; color: white; }
    td, th { vertical-align: middle; text-align: center; }
    tr:nth-child(even) { background-color: #f2f2f2; }
    .filter-section { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); margin-bottom: 20px; }
    .province-display { font-weight: bold; color: #28a745; font-size: 18px; }
</style>

<script>
const locations = <?php echo json_encode($locations); ?>;
</script>

<div class="container mt-5">
        <h2 class="main-title"><i class="fa-solid fa-map-marked-alt"></i> Khu vực khách hàng mua nhiều nhất</h2>

        <!-- Filter Section -->
        <div class="filter-section">
            <h5><i class="fa-solid fa-filter"></i> Lọc theo khu vực</h5>
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="province" class="form-label">Tỉnh/Thành phố</label>
                    <select class="form-select" id="province">
                        <option value="">-- Chọn Tỉnh/Thành phố --</option>
                        <?php foreach ($provinces as $prov): ?>
                            <option value="<?php echo htmlspecialchars($prov['tinhthanhpho']); ?>"><?php echo htmlspecialchars($prov['tinhthanhpho']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="district" class="form-label">Quận/Huyện</label>
                    <select class="form-select" id="district" disabled>
                        <option value="">-- Chọn Quận/Huyện --</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="ward" class="form-label">Xã/Phường</label>
                    <select class="form-select" id="ward" disabled>
                        <option value="">-- Chọn Xã/Phường --</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary" id="filter-btn"><i class="fa-solid fa-search"></i> Lọc</button>
                </div>
            </div>
            <div class="mt-3">
                <div id="province-display" class="province-display"></div>
            </div>
        </div>

        <!-- Results Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tỉnh/Thành phố</th>
                        <th>Quận/Huyện</th>
                        <th>Xã/Phường</th>
                        <th>Số đơn hàng</th>
                        <th>Tổng tiền (VNĐ)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $stt = 1; foreach ($areas as $area): ?>
                        <tr data-province="<?php echo htmlspecialchars($area['tinhthanhpho']); ?>" data-district="<?php echo htmlspecialchars($area['huyenquan']); ?>" data-ward="<?php echo htmlspecialchars($area['xaphuong']); ?>">
                            <td><?php echo $stt++; ?></td>
                            <td><?php echo htmlspecialchars($area['tinhthanhpho'] ?: 'Chưa cập nhật'); ?></td>
                            <td><?php echo htmlspecialchars($area['huyenquan'] ?: 'Chưa cập nhật'); ?></td>
                            <td><?php echo htmlspecialchars($area['xaphuong'] ?: 'Chưa cập nhật'); ?></td>
                            <td><?php echo number_format($area['so_don_hang']); ?></td>
                            <td><?php echo number_format($area['tong_tien'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const provinceSelect = document.getElementById('province');
        const districtSelect = document.getElementById('district');
        const wardSelect = document.getElementById('ward');
        const provinceDisplay = document.getElementById('province-display');
        const filterBtn = document.getElementById('filter-btn');
        const tableRows = document.querySelectorAll('tbody tr');

        // Function to get unique districts for a province
        function getDistricts(province) {
            const districts = [...new Set(locations.filter(loc => loc.tinhthanhpho === province).map(loc => loc.huyenquan))].sort();
            return districts;
        }

        // Function to get unique wards for a province and district
        function getWards(province, district) {
            const wards = [...new Set(locations.filter(loc => loc.tinhthanhpho === province && loc.huyenquan === district).map(loc => loc.xaphuong))].sort();
            return wards;
        }

        // Populate districts when province changes
        provinceSelect.addEventListener('change', function() {
            const province = this.value;
            districtSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
            wardSelect.innerHTML = '<option value="">-- Chọn Xã/Phường --</option>';
            provinceDisplay.textContent = '';
            if (province) {
                districtSelect.disabled = false;
                const districts = getDistricts(province);
                districts.forEach(district => {
                    const option = document.createElement('option');
                    option.value = district;
                    option.textContent = district;
                    districtSelect.appendChild(option);
                });
            } else {
                districtSelect.disabled = true;
                wardSelect.disabled = true;
            }
        });

        // Populate wards when district changes
        districtSelect.addEventListener('change', function() {
            const district = this.value;
            const province = provinceSelect.value;
            wardSelect.innerHTML = '<option value="">-- Chọn Xã/Phường --</option>';
            provinceDisplay.textContent = '';
            if (district && province) {
                wardSelect.disabled = false;
                const wards = getWards(province, district);
                wards.forEach(ward => {
                    const option = document.createElement('option');
                    option.value = ward;
                    option.textContent = ward;
                    wardSelect.appendChild(option);
                });
            } else {
                wardSelect.disabled = true;
            }
        });

        // Show province when ward is selected
        wardSelect.addEventListener('change', function() {
            const ward = this.value;
            const province = provinceSelect.value;
            if (ward && province) {
                provinceDisplay.textContent = `Tỉnh/Thành phố: ${province}`;
            } else {
                provinceDisplay.textContent = '';
            }
        });

        // Filter table when button is clicked
        filterBtn.addEventListener('click', function() {
            const selectedProvince = provinceSelect.value;
            const selectedDistrict = districtSelect.value;
            const selectedWard = wardSelect.value;

            tableRows.forEach(row => {
                const rowProvince = row.getAttribute('data-province');
                const rowDistrict = row.getAttribute('data-district');
                const rowWard = row.getAttribute('data-ward');

                let showRow = true;

                if (selectedProvince && rowProvince !== selectedProvince) {
                    showRow = false;
                }
                if (selectedDistrict && rowDistrict !== selectedDistrict) {
                    showRow = false;
                }
                if (selectedWard && rowWard !== selectedWard) {
                    showRow = false;
                }

                row.style.display = showRow ? '' : 'none';
            });
        });
    </script>
</body>
</html>

<?php mysqli_close($ketnoi); ?>
