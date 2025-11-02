<?php
$stores = [
    "Thành phố Hồ Chí Minh" => [
        "Quận 1" => [
            "The Pizza Company Nguyễn Thái Học - 107 Nguyễn Thái Học, P. Cầu Ông Lãnh",
        ],
        "Quận 2 (TP. Thủ Đức)" => [
            "The Pizza Company Estella Place - Tầng 4, TTTM Estella Place, 88 Song Hành, P. An Phú",
            "The Pizza Company Vincom Thảo Điền - Tầng 5, TTTM Vincom Mega Mall, 159 Xa lộ Hà Nội, P. Thảo Điền",
        ],
        "Quận 3" => [
            "The Pizza Company Nguyễn Thị Minh Khai - 506-508 Nguyễn Thị Minh Khai, P. 2",
            "The Pizza Company Lê Văn Sỹ - 333 Lê Văn Sỹ, P. 13",
        ],
    ],
    "Hà Nội" => [
        "Quận Cầu Giấy" => [
            "The Pizza Company Cầu Giấy - 333 Cầu Giấy, P. Dịch Vọng",
        ],
        "Quận Hà Đông" => [
            "The Pizza Company Nguyễn Văn Lộc - Biệt thự 16, Nguyễn Văn Lộc, KĐT Mỗ Lao",
        ],
    ],
    "Đồng Nai" => [
        "TP. Biên Hòa" => [
            "The Pizza Company Vincom Biên Hòa - 1096 Phạm Văn Thuận, P. Tân Mai",
            "The Pizza Company Pegasus - 53-55 Võ Thị Sáu, P. Quyết Thắng",
        ]
    ]
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chọn cửa hàng</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body { background: #f9f9f9; }
        .store-item {
            padding: 10px; border: 1px solid #ddd; border-radius: 8px;
            margin-bottom: 8px; background: white; cursor: pointer;
        }
        .store-item:hover { background: #e8f0fe; }
    </style>
</head>
<body class="p-4">
    <h4 class="mb-3">🏬 Chọn cửa hàng nhận hàng</h4>

    <!-- Tỉnh -->
    <div class="mb-3">
        <label class="form-label">Tỉnh / Thành phố:</label>
        <select id="province" class="form-select">
            <option value="">-- Chọn (không bắt buộc) --</option>
            <?php foreach ($stores as $province => $districts): ?>
                <option value="<?= htmlspecialchars($province) ?>"><?= htmlspecialchars($province) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Quận -->
    <div class="mb-3">
        <label class="form-label">Quận / Huyện:</label>
        <select id="district" class="form-select" disabled>
            <option value="">-- Chọn (không bắt buộc) --</option>
        </select>
    </div>

    <!-- Cửa hàng -->
    <div class="mb-3">
        <label class="form-label">Danh sách cửa hàng:</label>
        <div id="storeList"></div>
    </div>

    <button id="saveBtn" class="btn btn-primary" disabled>Lưu địa chỉ</button>

    <script>
        const stores = <?= json_encode($stores, JSON_UNESCAPED_UNICODE) ?>;
        const provinceSelect = document.getElementById('province');
        const districtSelect = document.getElementById('district');
        const storeList = document.getElementById('storeList');
        const saveBtn = document.getElementById('saveBtn');

        // Cập nhật danh sách cửa hàng
        function renderStores(province, district) {
            storeList.innerHTML = '';
            let list = [];

            if (province && district) {
                list = stores[province][district] || [];
            } else if (province) {
                // gom tất cả cửa hàng trong tỉnh
                Object.values(stores[province]).forEach(arr => list.push(...arr));
            } else {
                // gom toàn bộ cửa hàng (nếu không chọn gì)
                Object.values(stores).forEach(prov => {
                    Object.values(prov).forEach(arr => list.push(...arr));
                });
            }

            if (list.length === 0) {
                storeList.innerHTML = '<p class="text-muted">Không có cửa hàng phù hợp.</p>';
                saveBtn.disabled = true;
                return;
            }

            list.forEach(store => {
                const div = document.createElement('div');
                div.classList.add('store-item');
                div.innerHTML = `<label><input type="radio" name="store" value="${store}"> ${store}</label>`;
                storeList.appendChild(div);
            });

            document.querySelectorAll('input[name="store"]').forEach(radio => {
                radio.addEventListener('change', () => {
                    saveBtn.disabled = false;
                });
            });
        }

        // Khi chọn tỉnh
        provinceSelect.addEventListener('change', function() {
            const province = this.value;
            districtSelect.innerHTML = '<option value="">-- Chọn (không bắt buộc) --</option>';
            districtSelect.disabled = !province;

            if (province) {
                Object.keys(stores[province]).forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d;
                    opt.textContent = d;
                    districtSelect.appendChild(opt);
                });
            }

            renderStores(province, '');
        });

        // Khi chọn quận
        districtSelect.addEventListener('change', function() {
            renderStores(provinceSelect.value, this.value);
        });

        // Khi nhấn Lưu
        saveBtn.addEventListener('click', () => {
            const selected = document.querySelector('input[name="store"]:checked');
            if (!selected) return alert('Vui lòng chọn cửa hàng!');
            const storeName = selected.value;

            fetch('save_store.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `store_name=${encodeURIComponent(storeName)}`
            })
            .then(res => res.text())
            .then(msg => alert(msg))
            .catch(() => alert('❌ Lỗi khi lưu địa chỉ!'));
        });

        // Lần đầu render toàn bộ
        renderStores('', '');
    </script>
</body>
</html>
