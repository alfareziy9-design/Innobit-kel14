document.addEventListener('DOMContentLoaded', function () {
    const deleteLinks = document.querySelectorAll('.btn-delete');

    deleteLinks.forEach(function (link) {
        link.addEventListener('click', function (e) {
            const confirmDelete = confirm('Yakin ingin menghapus data ini?');

            if (!confirmDelete) {
                e.preventDefault();
            }
        });
    });
});