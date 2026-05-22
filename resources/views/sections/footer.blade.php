<footer class="content-footer footer bg-footer-theme">
    <div class="container-fluid d-flex justify-content-between py-2 flex-md-row flex-column">

        <div>
            © {{ date('Y') }} - Gestion Pièces Détachées
        </div>

        <div class="d-none d-lg-inline-block">
            <span class="text-muted">Version 1.0</span>
        </div>

    </div>

    <script>
        document.getElementById('fileInput').addEventListener('change', function(e) {
            let fileName = e.target.files[0]?.name || 'Aucun fichier choisi';
            document.getElementById('fileName').innerText = fileName;
        });
    </script>
</footer>
