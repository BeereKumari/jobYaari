$(document).ready(function() {

    // Filter on category tag click
    $(document).on('click', '.category-tag', function(e) {
        e.preventDefault();
        $('.category-tag').removeClass('active');
        $(this).addClass('active');
        loadBlogs();
    });

    // Filter on dropdown/date change
    $('#filter-category, #filter-date').on('change', function() {
        loadBlogs();
    });

    // Filter button click
    $('#btn-filter').on('click', function() {
        loadBlogs();
    });

    // Clear filters
    $('#btn-clear').on('click', function() {
        $('#filter-category').val('');
        $('#filter-date').val('');
        $('.category-tag').removeClass('active');
        loadBlogs();
    });

    function loadBlogs() {
        const category = $('#filter-category').val() || $('.category-tag.active').data('category') || '';
        const date = $('#filter-date').val() || '';

        $('#blog-list').addClass('loading');
        $('#loading-spinner').show();

        $.ajax({
            url: '/ajax/filter-blogs.php',
            method: 'GET',
            data: { category: category, date: date },
            success: function(response) {
                $('#blog-list').html(response).removeClass('loading');
                $('#loading-spinner').hide();
            },
            error: function() {
                $('#blog-list').html('<div class="no-results"><p class="error-msg">Failed to load blogs. Please try again.</p></div>').removeClass('loading');
                $('#loading-spinner').hide();
            }
        });
    }

    // AJAX Search
    $('#search-form').on('submit', function(e) {
        e.preventDefault();
        const query = $('#search-input').val();

        $.ajax({
            url: '/ajax/search-blogs.php',
            method: 'GET',
            data: { q: query },
            success: function(response) {
                $('#blog-list').html(response);
            },
            error: function() {
                $('#blog-list').html('<div class="no-results"><p>Search failed. Please try again.</p></div>');
            }
        });
    });
});
