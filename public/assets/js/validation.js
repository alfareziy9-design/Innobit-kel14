document.addEventListener('DOMContentLoaded', function () {
    const articleForm = document.getElementById('articleForm');

    if (articleForm) {
        articleForm.addEventListener('submit', function (e) {
            const title = document.getElementById('title');
            const category = document.getElementById('category_id');
            const summary = document.getElementById('summary');
            const content = document.getElementById('content');

            let errors = [];

            if (title && title.value.trim() === '') {
                errors.push('Judul wajib diisi.');
            }

            if (category && category.value.trim() === '') {
                errors.push('Kategori wajib dipilih.');
            }

            if (summary && summary.value.trim() === '') {
                errors.push('Ringkasan wajib diisi.');
            }

            if (content && content.value.trim() === '') {
                errors.push('Isi artikel wajib diisi.');
            }

            if (errors.length > 0) {
                e.preventDefault();
                alert(errors.join('\n'));
            }
        });
    }

    const categoryForm = document.getElementById('categoryForm');

    if (categoryForm) {
        categoryForm.addEventListener('submit', function (e) {
            const name = document.getElementById('name');

            if (name && name.value.trim() === '') {
                e.preventDefault();
                alert('Nama kategori wajib diisi.');
            }
        });
    }
});