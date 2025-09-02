jQuery(document).ready(function($) {
    // Initialize the hierarchy tree
    function initHierarchyTree() {
        $('#abpcwa-hierarchy-tree').html('<div class="abpcwa-loading">' + abpcwaHierarchy.strings.loading + '</div>');
        
        // Fetch hierarchy data from REST API
        $.ajax({
            url: abpcwaHierarchy.rest_url + 'hierarchy',
            method: 'GET',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', abpcwaHierarchy.nonce);
                $('#abpcwa-hierarchy-spinner').addClass('is-active');
            },
            success: function(data) {
                $('#abpcwa-hierarchy-spinner').removeClass('is-active');
                renderHierarchyTree(data);
            },
            error: function(xhr, status, error) {
                $('#abpcwa-hierarchy-spinner').removeClass('is-active');
                var errorMessage = 'Error loading hierarchy: ' + error;
                if (xhr.status === 403) {
                    errorMessage += ' (Forbidden - check user permissions and nonce validation)';
                }
                $('#abpcwa-hierarchy-tree').html(
                    '<div class="abpcwa-error">' + errorMessage + '<br>Status: ' + xhr.status + '<br>Response: ' + xhr.responseText + '</div>'
                );
                console.error('Hierarchy Error:', xhr);
            }
        });
    }

    // Render the hierarchy tree
    function renderHierarchyTree(data) {
        $('#abpcwa-hierarchy-tree').jstree({
            'core': {
                'data': data,
                'themes': {
                    'name': 'default',
                    'responsive': true
                },
                'check_callback': function() {
                    return false; // Read-only mode
                }
            },
            'plugins': ['search']
        });

        // Expand all button
        $('#abpcwa-expand-all').on('click', function() {
            $('#abpcwa-hierarchy-tree').jstree('open_all');
        });

        // Collapse all button
        $('#abpcwa-collapse-all').on('click', function() {
            $('#abpcwa-hierarchy-tree').jstree('close_all');
        });

        // Search functionality
        var to = false;
        $('#abpcwa-hierarchy-search').keyup(function() {
            if (to) {
                clearTimeout(to);
            }
            to = setTimeout(function() {
                var v = $('#abpcwa-hierarchy-search').val();
                $('#abpcwa-hierarchy-tree').jstree(true).search(v);
            }, 250);
        });
    }

    // Initialize the hierarchy
    initHierarchyTree();
});
