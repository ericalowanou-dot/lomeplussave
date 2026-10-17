{{-- Enregistre en arrière-plan les clics « Appeler » / « WhatsApp » (liens portant data-contact-canal).
     Le lien s'ouvre normalement : l'envoi ne bloque ni ne retarde jamais le contact. --}}
<script>
(function () {
    if (window.__contactVendeurTracking) return;
    window.__contactVendeurTracking = true;

    document.addEventListener('click', function (e) {
        var link = e.target.closest && e.target.closest('[data-contact-canal]');
        if (!link) return;
        try {
            var token = document.querySelector('meta[name="csrf-token"]');
            var data = new FormData();
            data.append('_token', token ? token.content : '');
            data.append('canal', link.dataset.contactCanal);
            data.append('vendeur_id', link.dataset.contactVendeur);
            if (link.dataset.contactArticle) data.append('article_id', link.dataset.contactArticle);

            var url = @json(route('contact-vendeur.store'));
            if (!(navigator.sendBeacon && navigator.sendBeacon(url, data))) {
                fetch(url, { method: 'POST', body: data, keepalive: true, credentials: 'same-origin' }).catch(function () {});
            }
        } catch (err) { /* le suivi ne doit jamais gêner le visiteur */ }
    }, true);
})();
</script>
