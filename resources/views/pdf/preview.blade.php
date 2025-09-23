<!DOCTYPE html>
<html>
<head>
    <title>Preview Bukti Setoran</title>
</head>
<body style="margin:0; padding:0;">
    <embed src="data:application/pdf;base64,{{ $pdfContent }}" type="application/pdf" width="100%" height="100%" />
    <script>
        window.print();

        setInterval(() => {
            fetch('{{ route("petugas.setoran.checkSession") }}')
                .then(res => res.json())
                .then(data => {
                    if (!data.active) window.close();
                })
                .catch(() => window.close());
        }, 5000);
    </script>
</body>
</html>
