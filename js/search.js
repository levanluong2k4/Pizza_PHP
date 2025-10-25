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
                        let option = document.createElement("div");
                        option.classList.add(
                            "list-group-item",
                            "list-group-item-action",
                            "d-flex",
                            "align-items-center",
                            "suggest-search"
                        );
                        option.setAttribute("data-masp", item.MaSP);

                        // ảnh
                        let img = document.createElement("img");
                        img.src = item.Anh; // đường dẫn ảnh trong DB
                        img.alt = item.TenSP;
                        img.style.width = "auto";
                        img.style.height = "40px";
                        img.classList.add("me-2", "rounded");

                        // tên sản phẩm
                        let span = document.createElement("span");
                        span.textContent = item.TenSP;

                        option.appendChild(img);
                        option.appendChild(span);

                        // khi click vào gợi ý
                        option.addEventListener("click", function (e) {
                            e.preventDefault();
                            resultBox.innerHTML = "";

                            let productImg = img.src;
                            let productName = span.textContent;
                            let maSP = item.MaSP;

                            // gọi AJAX lấy size
                            $.post("cart/get_product_sizes.php", { masp: maSP }, function (data) {
                                let sizes = JSON.parse(data);
                                if (sizes.length > 0) {
                                    showSizeModal(productName, productImg, sizes, maSP);
                                } else {
                                    alert('Sản phẩm này hiện tại chưa có thông tin size!');
                                }
                            });
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
