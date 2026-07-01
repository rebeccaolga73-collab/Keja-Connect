<?php
/**
 * KejaConnect - Core Reusable Footer Layout
 */
?>
    </main>
    
    <!-- FOOTER LICENSE BAR -->
    <footer class="bg-white border-t border-gray-200 py-3 px-6 text-center text-xs text-gray-400 shrink-0">
        <p>&copy; <?php echo date('Y'); ?> <?php echo APP_COMPANY; ?>. All Rights Reserved. Styled with Deep Green and Gold accents.</p>
    </footer>

</div>

<!-- RESPONSIVE NAV SCRIPTS -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const mobileMenuBtn = document.getElementById("mobileMenuBtn");
    const closeSidebarBtn = document.getElementById("closeSidebarBtn");
    const sidebar = document.getElementById("sidebar");

    if (mobileMenuBtn && sidebar) {
        mobileMenuBtn.addEventListener("click", function() {
            sidebar.classList.remove("-translate-x-full");
        });
    }

    if (closeSidebarBtn && sidebar) {
        closeSidebarBtn.addEventListener("click", function() {
            sidebar.classList.add("-translate-x-full");
        });
    }
    
    // Auto-dismiss Flash banners after 5 seconds
    setTimeout(function() {
        const flashes = document.querySelectorAll('.flash-alert');
        flashes.forEach(function(flash) {
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 400); 
        });
    }, 5000);
});
</script>
</body>
</html>
