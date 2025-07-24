<?php
// php_mvc_app/app/Views/layout/footer.php
?>
<script type="text/javascript">
    const BASE_URL_JS = "<?php echo BASE_URL; ?>";
</script>
<!-- Scripts JS específicos de cada módulo se incluyen directamente en sus vistas -->
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
<!-- DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<!-- Alpinejs JS -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<!-- Scripts JS globales -->
<?php if (isset($page_js)): ?>
    <script src="<?php echo BASE_URL . $page_js; ?>"></script>
<?php endif; ?>
</body>

</html>