document.addEventListener('DOMContentLoaded', function () {
    const categoryFilter = document.getElementById('categoryFilter');

    if (categoryFilter) {
        categoryFilter.addEventListener('change', function () {
            this.form.submit();
        });
    }
});