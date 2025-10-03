</div>
<footer class="bg-white border-t border-gray-200 text-center py-6 mt-16">
    <p class="text-sm text-gray-600">&copy; <?php echo date('Y'); ?> E-Learning System</p>
</footer>

<script>
    // JavaScript untuk toggle menu mobile
    const navToggle = document.getElementById('nav-toggle');
    const navContent = document.getElementById('nav-content');

    navToggle.addEventListener('click', () => {
        navContent.classList.toggle('hidden');
    });
</script>
</body>

</html>