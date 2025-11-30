document.addEventListener("DOMContentLoaded", function () {
    const provinceSelect = document.getElementById("province");
    const districtSelect = document.getElementById("district");
    const wardSelect = document.getElementById("ward");
    const soNhaInput = document.getElementById("so_nha_input");
    const diachiInput = document.getElementById("diachi_input");
    const fullAddress = document.getElementById("full_address");
    const saveBtn = document.getElementById("saveBtn");

   
    const apiBase = "http://provinces.open-api.vn/api/";

    // 🏙️ Load Tỉnh/Thành phố
    fetch(apiBase + "?depth=1")
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            data.forEach(p => provinceSelect.add(new Option(p.name, p.code)));

            // ✅ Nếu có dữ liệu cũ
            if (typeof oldAddress2 !== "undefined" && oldAddress2.province) {
                provinceSelect.value = oldAddress2.province;
                loadDistricts(oldAddress2.province);
            }

            // ✅ Gán lại số nhà nếu có
            if (typeof oldAddress2 !== "undefined" && oldAddress2.so_nha) {
                soNhaInput.value = oldAddress2.so_nha;
            }
        })
   

    // 🏘️ Khi chọn Tỉnh mới
    provinceSelect.addEventListener("change", () => {
        const provinceCode = provinceSelect.value;
        districtSelect.innerHTML = "<option value=''>-- Chọn Quận/Huyện --</option>";
        wardSelect.innerHTML = "<option value=''>-- Chọn Xã/Phường --</option>";
        wardSelect.disabled = true;

        if (provinceCode) {
            loadDistricts(provinceCode);
        }

        updateAddress();
    });

    // 🏘️ Khi chọn Huyện mới
    districtSelect.addEventListener("change", () => {
        const districtCode = districtSelect.value;
        wardSelect.innerHTML = "<option value=''>-- Chọn Xã/Phường --</option>";
        wardSelect.disabled = true;

        if (districtCode) {
            loadWards(districtCode);
        }

        updateAddress();
    });

    // 🏡 Khi chọn Xã/Phường
    wardSelect.addEventListener("change", updateAddress);
    soNhaInput.addEventListener("input", updateAddress);

    // 🧩 Load Huyện
    function loadDistricts(provinceCode) {
        fetch(apiBase + "p/" + provinceCode + "?depth=2")
            .then(res => {
                if (!res.ok) throw new Error('Network response was not ok');
                return res.json();
            })
            .then(data => {
                districtSelect.innerHTML = "<option value=''>-- Chọn Quận/Huyện --</option>";
                data.districts.forEach(d => districtSelect.add(new Option(d.name, d.code)));
                districtSelect.disabled = false;

                // Nếu có dữ liệu cũ
                if (typeof oldAddress2 !== "undefined" && oldAddress2.district) {
                    districtSelect.value = oldAddress2.district;
                    loadWards(oldAddress2.district);
                }
            })
         
    }

    // 🧩 Load Xã
    function loadWards(districtCode) {
        fetch(apiBase + "d/" + districtCode + "?depth=2")
            .then(res => {
                if (!res.ok) throw new Error('Network response was not ok');
                return res.json();
            })
            .then(data => {
                wardSelect.innerHTML = "<option value=''>-- Chọn Xã/Phường --</option>";
                data.wards.forEach(w => wardSelect.add(new Option(w.name, w.name)));
                wardSelect.disabled = false;

                // Nếu có dữ liệu cũ
                if (typeof oldAddress2 !== "undefined" && oldAddress2.ward) {
                    wardSelect.value = oldAddress2.ward;
                    updateAddress();
                }
            })
         
    }

    // 🧩 Cập nhật địa chỉ hiển thị
    function updateAddress() {
        const province = provinceSelect.options[provinceSelect.selectedIndex]?.text || "";
        const district = districtSelect.options[districtSelect.selectedIndex]?.text || "";
        const ward = wardSelect.options[wardSelect.selectedIndex]?.text || "";
        const soNha = soNhaInput.value.trim();

        const full = [soNha, ward, district, province].filter(Boolean).join(", ");
        diachiInput.value = full;
        fullAddress.textContent = full ? "🏠 " + full : "";
        if (saveBtn) saveBtn.style.display = 'block';
    }
});