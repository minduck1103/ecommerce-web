<footer class="footer">
    <div class="container">
        <div class="footer-main">
            <div class="row g-4">
                <!-- Thông tin công ty -->
                <div class="col-lg-4 col-md-6">
                    <div class="footer-info">
                        <h3 class="footer-title">Unile Clothing</h3>
                        <p class="footer-description">
                            Chúng tôi cam kết mang đến những sản phẩm thời trang chất lượng cao với giá cả hợp lý nhất cho khách hàng.
                        </p>
                        <div class="contact-info">
                            <div class="contact-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <p>332 Đường Cao Lỗ, Quận 8, TP.HCM</p>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-phone-alt"></i>
                                <p>(84) 909090909</p>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <p>support@unileclothing.com</p>
                            </div>
                        </div>
                        <div class="social-links">
                            <a href="#" class="social-link facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="social-link instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="#" class="social-link tiktok">
                                <i class="fab fa-tiktok"></i>
                            </a>
                            <a href="#" class="social-link youtube">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Liên kết nhanh -->
                <div class="col-lg-2 col-md-6">
                    <h4 class="footer-title">Liên kết</h4>
                    <ul class="footer-links">
                        <li><a href="/shoppingcart">Trang chủ</a></li>
                        <li><a href="/shoppingcart/products">Sản phẩm</a></li>
                        <li><a href="/shoppingcart/about">Về chúng tôi</a></li>
                        <li><a href="/shoppingcart/contact">Liên hệ</a></li>
                        <li><a href="/shoppingcart/blog">Blog</a></li>
                    </ul>
                </div>

                <!-- Chính sách -->
                <div class="col-lg-2 col-md-6">
                    <h4 class="footer-title">Chính sách</h4>
                    <ul class="footer-links">
                        <li><a href="/shoppingcart/policy/shipping">Chính sách vận chuyển</a></li>
                        <li><a href="/shoppingcart/policy/return">Chính sách đổi trả</a></li>
                        <li><a href="/shoppingcart/policy/payment">Hình thức thanh toán</a></li>
                        <li><a href="/shoppingcart/policy/privacy">Bảo mật thông tin</a></li>
                        <li><a href="/shoppingcart/policy/terms">Điều khoản dịch vụ</a></li>
                    </ul>
                </div>

                <!-- Đăng ký nhận tin -->
                <div class="col-lg-4 col-md-6">
                    <h4 class="footer-title">Đăng ký nhận tin</h4>
                    <p class="footer-description">
                        Đăng ký để nhận thông tin về sản phẩm mới và các chương trình khuyến mãi của chúng tôi.
                    </p>
                    <form class="footer-form">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Email của bạn">
                            <button class="btn btn-dark" type="submit">
                                Đăng ký
                                <i class="fas fa-paper-plane ms-2"></i>
                            </button>
                        </div>
                    </form>
                    <div class="payment-methods">
                        <h5 class="payment-title">Thanh toán linh hoạt</h5>
                        <div class="payment-icons">
                            <i class="fab fa-cc-visa"></i>
                            <i class="fab fa-cc-mastercard"></i>
                            <i class="fab fa-cc-paypal"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="copyright">
                        © 2025 Fashion Shop. Thiết kế bởi <a href="#" class="author-link">Cud1</a>
                    </p>
                </div>
                <div class="col-md-6">
                    <div class="footer-bottom-links">
                        <a href="/shoppingcart/policy/privacy">Chính sách bảo mật</a>
                        <a href="/shoppingcart/policy/terms">Điều khoản sử dụng</a>
                        <a href="/shoppingcart/sitemap">Sitemap</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
.footer {
    background-color: #f8f9fa;
    padding: 5rem 0 2rem;
    margin-top: 5rem;
    border-top: 1px solid #eee;
}

.footer-main {
    margin-bottom: 3rem;
}

.footer-info {
    margin-bottom: 2rem;
}

.footer-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: #333;
    position: relative;
    padding-bottom: 0.5rem;
}

.footer-title::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 50px;
    height: 2px;
    background: #333;
}

.footer-description {
    color: #666;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.contact-info {
    margin-bottom: 1.5rem;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1rem;
}

.contact-item i {
    color: #666;
    font-size: 1.1rem;
    margin-top: 0.2rem;
}

.contact-item p {
    color: #666;
    margin: 0;
}

.social-links {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.social-link {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #fff;
    color: #333;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid #eee;
}

.social-link:hover {
    transform: translateY(-3px);
}

.social-link.facebook:hover {
    background: #1877f2;
    color: white;
    border-color: #1877f2;
}

.social-link.instagram:hover {
    background: #e4405f;
    color: white;
    border-color: #e4405f;
}

.social-link.tiktok:hover {
    background: #000;
    color: white;
    border-color: #000;
}

.social-link.youtube:hover {
    background: #ff0000;
    color: white;
    border-color: #ff0000;
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
    color: #666;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-block;
}

.footer-links a:hover {
    color: #333;
    transform: translateX(5px);
}

.footer-form .input-group {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}

.footer-form .form-control {
    border: 1px solid #eee;
    padding: 0.8rem 1rem;
    font-size: 0.95rem;
}

.footer-form .form-control:focus {
    box-shadow: none;
    border-color: #ddd;
}

.footer-form .btn {
    padding: 0.8rem 1.5rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.footer-form .btn i {
    transition: transform 0.3s ease;
}

.footer-form .btn:hover i {
    transform: translateX(3px);
}

.payment-methods {
    margin-top: 2rem;
}

.payment-title {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 1rem;
}

.payment-icons {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.payment-icons i {
    font-size: 2rem;
    color: #666;
}

.payment-icon {
    height: 24px;
    width: auto;
    object-fit: contain;
}

.footer-bottom {
    padding-top: 2rem;
    border-top: 1px solid #eee;
}

.copyright {
    color: #666;
    margin: 0;
    font-size: 0.9rem;
}

.author-link {
    color: #333;
    text-decoration: none;
    font-weight: 500;
}

.author-link:hover {
    color: #000;
}

.footer-bottom-links {
    display: flex;
    justify-content: flex-end;
    gap: 2rem;
}

.footer-bottom-links a {
    color: #666;
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.3s ease;
}

.footer-bottom-links a:hover {
    color: #333;
}

@media (max-width: 768px) {
    .footer {
        padding: 3rem 0 1rem;
    }

    .footer-title {
        font-size: 1.3rem;
        margin-bottom: 1rem;
    }

    .footer-bottom-links {
        justify-content: center;
        margin-top: 1rem;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .copyright {
        text-align: center;
    }
}
</style> 