<?php
// php_mvc_app/app/Views/layout/footer.php
?>
<?php if (!($isLogin ?? false)): ?>
        </div> <!-- Cierra el div .container -->
    </main> <!-- Cierra el main de contenido scrollable -->
</div> <!-- Cierra el flex-col de la main content area -->
</div> <!-- Cierra el div x-data general del layout -->
<?php endif; ?>

<script type="text/javascript">
    const BASE_URL_JS = "<?php echo BASE_URL; ?>";
</script>
<!-- Scripts JS específicos de cada módulo se incluyen directamente en sus vistas -->
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
<!-- DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

<!-- Scripts de DataTables Buttons y sus exportadores -->
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

<!-- Scripts JS globales -->
<?php if (isset($page_js)): ?>
    <script src="<?php echo BASE_URL . $page_js; ?>"></script>
<?php endif; ?>
</body>

</html>