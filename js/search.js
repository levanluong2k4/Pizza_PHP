const searchBox = document.getElementById("search-box");
const resultBox = document.getElementById("search-result");

searchBox.addEventListener("keyup", function () {
    let query = this.value;

    if (query.length > 0) {
        fetch("handlers/search.php?q=" + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                resultBox.innerHTML = "";

                if (data.length > 0) {
data.forEach(item => {
    let option = document.createElement("button");
    option.classList.add(
        "list-group-item",
        "list-group-item-action",
        "d-flex",
        "align-items-center",
        "suggest-search",
        "btn-buy"
    );
    option.setAttribute("data-masp", item.MaSP);
    option.style.width = "65%";

    // ảnh sản phẩm
    let img = document.createElement("img");
    img.src = item.Anh;
    img.alt = item.TenSP;
    img.style.width = "auto";
    img.style.height = "40px";
    img.classList.add("me-3", "rounded");

    // tên + giá
    let infoDiv = document.createElement("div");
    let nameEl = document.createElement("div");
    nameEl.textContent = item.TenSP;
    nameEl.classList.add("fw-bold");

    let priceEl = document.createElement("div");
    priceEl.textContent = Number(item.Gia).toLocaleString("vi-VN") + "₫";
    priceEl.classList.add("text-danger", "small");

    infoDiv.appendChild(nameEl);
    infoDiv.appendChild(priceEl);

    option.appendChild(img);
    option.appendChild(infoDiv);
    option.addEventListener("click", () => {
    resultBox.innerHTML = "";
        });

    resultBox.appendChild(option);
});


                } else {
                    resultBox.innerHTML = "<div class='list-group-item'>Không tìm thấy</div>";
                }
            })
            .catch(err => console.error(err));
    } else {
        resultBox.innerHTML = "";
    }
});



// Ẩn kết quả khi click ra ngoài
document.addEventListener("click", function (event) {
    // Nếu phần được click không nằm trong ô tìm kiếm hoặc danh sách gợi ý
    if (!resultBox.contains(event.target) && event.target !== searchBox) {
        resultBox.innerHTML = ""; // Xóa gợi ý
    }
});




// Xử lý bật/tắt nút tìm kiếm
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("search-box");
    const searchButton = document.getElementById("search-btn");

    // Hàm kiểm tra và bật/tắt nút
    function toggleButton() {
        const hasText = searchInput.value.trim().length > 0;
        searchButton.disabled = !hasText; // Nếu có chữ => bật nút
    }

    // Kiểm tra mỗi khi người dùng gõ
    searchInput.addEventListener("input", toggleButton);

    // Khi load lại trang, kiểm tra luôn (trường hợp nhập sẵn)
    toggleButton();
});


