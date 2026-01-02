<?php
// app/Views/partials/footer.php
?>
        </main> <!-- End page-wrapper -->
    </div> <!-- End main-content -->
</div> <!-- End app-layout -->

<script>
    // Sidebar Toggle Logic
    const toggleBtn = document.querySelector('.toggle-sidebar-btn');
    const sidebar = document.querySelector('.sidebar');
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        if (window.innerWidth < 768) {
            if (sidebar.classList.contains('is-open') && 
                !sidebar.contains(e.target) && 
                !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('is-open');
            }
        }
    });

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebar.classList.toggle('is-open');
        });
    }

    // Password Visibility Toggle (for auth pages)
    document.querySelectorAll('.password-toggle-icon').forEach(toggle => {
        toggle.addEventListener('click', () => {
            const wrapper = toggle.closest('.password-wrapper');
            const input = wrapper.querySelector('input');
            const eyeIcon = toggle.querySelector('.icon-eye');
            const eyeSlashIcon = toggle.querySelector('.icon-eye-slash');

            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeSlashIcon.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeSlashIcon.classList.add('hidden');
            }
        });
    });
</script>
</body>
</html>