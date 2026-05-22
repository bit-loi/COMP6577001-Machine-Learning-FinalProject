    </div> <!-- End .content -->
</div> <!-- End .main -->

<script>
    // Initialize Lucide icons with retry and multiple events to ensure icons render
    function initLucide() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }
    window.addEventListener('DOMContentLoaded', initLucide);
    window.addEventListener('load', initLucide);
    // Also try immediately
    setTimeout(initLucide, 100);
</script>
</body>
</html>
