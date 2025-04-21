<footer class="footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="footer-title">Shopping Cart</h5>
                <p class="footer-description">Chúng tôi luôn trân trọng và mong đợi nhận được mọi ý kiến đóng góp từ khách hàng để có thể nâng cấp trải nghiệm dịch vụ và sản phẩm tốt hơn nữa.</p>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-tiktok"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <div class="col-lg-2">
                <h5 class="footer-title">Về chúng tôi</h5>
                <ul class="footer-links">
                    <li><a href="#">Giới thiệu</a></li>
                    <li><a href="#">Liên hệ</a></li>
                    <li><a href="#">Tuyển dụng</a></li>
                    <li><a href="#">Tin tức</a></li>
                </ul>
            </div>
            <div class="col-lg-2">
                <h5 class="footer-title">Hỗ trợ</h5>
                <ul class="footer-links">
                    <li><a href="#">FAQs</a></li>
                    <li><a href="#">Bảo mật</a></li>
                    <li><a href="#">Điều khoản</a></li>
                    <li><a href="#">Tra cứu đơn hàng</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h5 class="footer-title">Đăng ký nhận tin</h5>
                <p class="footer-description">Nhận thông tin sản phẩm mới nhất, tin khuyến mãi và nhiều hơn nữa.</p>
                <form class="footer-form">
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Email của bạn">
                        <button class="btn btn-dark" type="submit">Đăng ký</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="footer-bottom">
            <p class="mb-0">&copy; <?= date('Y') ?> Shopping Cart. All rights reserved.</p>
        </div>
    </div>
</footer>

<style>
.footer {
    background-color: #f8f9fa;
    padding: 4rem 0 2rem;
    margin-top: 4rem;
}

.footer-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: #000;
}

.footer-description {
    color: #6c757d;
    margin-bottom: 1.5rem;
}

.social-links {
    display: flex;
    gap: 1rem;
}

.social-link {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #000;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-link:hover {
    background: #333;
    color: white;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 0.8rem;
}

.footer-links a {
    color: #6c757d;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-links a:hover {
    color: #000;
}

.footer-form .form-control {
    border: 1px solid #dee2e6;
    padding: 0.75rem 1rem;
}

.footer-form .btn {
    padding: 0.75rem 1.5rem;
}

.footer-bottom {
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 1px solid #dee2e6;
    text-align: center;
    color: #6c757d;
}
</style> 