// cart.js

// Các hàm xử lý giỏ hàng sẵn có ở đây...

// Kiểm tra nhập thông tin khi đặt hàng
document.addEventListener('DOMContentLoaded', function () {
    const guestOrderBtn = document.querySelector('order-guest'); // Nút "Đặt hàng không cần đăng nhập"

    if (guestOrderBtn) {
        guestOrderBtn.addEventListener('click', function (e) {
            const hoten = document.getElementById('hoten_input');
            const sodt = document.getElementById('sodt_input');
            const soNha = document.getElementById('so_nha_input');
            const province = document.getElementById('province');
            const district = document.getElementById('district');
            const ward = document.getElementById('ward');

            let missingFields = [];

            [hoten, sodt, soNha, province, district, ward].forEach(input => input.style.border = '');

            if (!hoten.value.trim()) missingFields.push(hoten);
            if (!sodt.value.trim()) missingFields.push(sodt);
            if (!soNha.value.trim()) missingFields.push(soNha);
            if (!province.value.trim()) missingFields.push(province);
            if (!district.value.trim()) missingFields.push(district);
            if (!ward.value.trim()) missingFields.push(ward);

            if (missingFields.length > 0) {
                e.preventDefault();
                missingFields.forEach(el => el.style.border = '2px solid red');
                missingFields[0].scrollIntoView({ behavior: 'smooth', block: 'center' });

                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger text-center mt-3';
                alertDiv.textContent = '⚠️ Vui lòng nhập đầy đủ thông tin trước khi đặt hàng.';
                document.querySelector('.card-body').prepend(alertDiv);

                setTimeout(() => alertDiv.remove(), 3000);
                return false;
            }
        });
    }
});
