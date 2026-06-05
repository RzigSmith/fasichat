/* FasiChat Classroom — JavaScript principal */

// Auto-resize textarea
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('textarea').forEach(function (ta) {
        ta.addEventListener('input', function () {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 160) + 'px';
        });
    });

    // Fermeture automatique des alertes après 5s
    document.querySelectorAll('.alert').forEach(function (alert) {
        setTimeout(function () {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity .4s';
            setTimeout(function () { alert.remove(); }, 400);
        }, 5000);
    });
});
