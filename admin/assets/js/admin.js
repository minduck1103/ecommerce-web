document.addEventListener('DOMContentLoaded', function() {
    // Xử lý chuyển đổi section
    const contentWrapper = document.getElementById('page-content');
    const navLinks = document.querySelectorAll('.nav-section');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Xóa class active khỏi tất cả các link
            navLinks.forEach(l => l.classList.remove('active'));
            // Thêm class active vào link được click
            this.classList.add('active');

            // Lấy URL từ href của link
            const href = this.getAttribute('href');
            const sectionName = href.split('/').pop().replace('.php', '');
            
            // Hiển thị loading
            contentWrapper.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

            // Gọi AJAX để lấy nội dung từ load_section.php
            fetch(`../admin/load_section.php?section=${sectionName}`, {
                credentials: 'same-origin'
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    contentWrapper.innerHTML = html;
                    
                    // Thực thi các script trong nội dung mới
                    const scripts = contentWrapper.getElementsByTagName('script');
                    Array.from(scripts).forEach(script => {
                        const newScript = document.createElement('script');
                        Array.from(script.attributes).forEach(attr => {
                            newScript.setAttribute(attr.name, attr.value);
                        });
                        newScript.textContent = script.textContent;
                        script.parentNode.replaceChild(newScript, script);
                    });
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    contentWrapper.innerHTML = `
                        <div class="alert alert-danger">
                            <h4 class="alert-heading">Lỗi tải nội dung!</h4>
                            <p>Error: ${error.message}</p>
                            <hr>
                            <p class="mb-0">Vui lòng thử lại sau hoặc liên hệ admin.</p>
                        </div>`;
                });
        });
    });

    // Xử lý nút toggle sidebar
    const sidebarToggle = document.getElementById('sidebarToggle');
    const wrapper = document.getElementById('wrapper');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            wrapper.classList.toggle('toggled');
        });
    }
}); 