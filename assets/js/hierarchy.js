jQuery(document).ready(function($) {
    let hierarchyData = null; // To store fetched data
    let currentView = 'tree'; // Default view

    // 1. Initialize the hierarchy visualization
    function initHierarchy() {
        // Set initial loading message for the default view
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
                hierarchyData = data;
                // Render the default view
                switchView(currentView);
            },
            error: function(xhr, status, error) {
                $('#abpcwa-hierarchy-spinner').removeClass('is-active');
                var errorMessage = 'Error loading hierarchy: ' + error;
                if (xhr.status === 403) {
                    errorMessage += ' (Forbidden - check user permissions and nonce validation)';
                }
                // Display error in the active view container
                $('.abpcwa-hierarchy-view.active-view').html(
                    '<div class="abpcwa-error">' + errorMessage + '<br>Status: ' + xhr.status + '<br>Response: ' + xhr.responseText + '</div>'
                );
                console.error('Hierarchy Error:', xhr);
            }
        });
    }

    // 2. Switch between different visualization views
    function switchView(view) {
        if (!hierarchyData) return; // Don't switch if data isn't loaded

        currentView = view;
        
        // Update button styles
        $('.abpcwa-view-controls .button').removeClass('button-primary');
        $('.abpcwa-view-controls .button[data-view="' + view + '"]').addClass('button-primary');

        // Show the correct view container
        $('.abpcwa-hierarchy-view').removeClass('active-view');
        $('#abpcwa-hierarchy-' + view).addClass('active-view');

        // Call the appropriate render function
        switch (view) {
            case 'tree':
                renderTreeView();
                break;
            case 'mindmap':
                renderMindmapView();
                break;
            case 'orgchart':
                renderOrgChartView();
                break;
            case 'grid':
                renderGridView();
                break;
        }
    }

    // 3. Render Functions for each view
    function renderTreeView() {
        // Check if tree is already initialized
        if ($.jstree.reference('#abpcwa-hierarchy-tree')) {
            $('#abpcwa-hierarchy-tree').jstree(true).settings.core.data = hierarchyData;
            $('#abpcwa-hierarchy-tree').jstree(true).refresh();
        } else {
            $('#abpcwa-hierarchy-tree').jstree({
                'core': {
                    'data': hierarchyData,
                    'themes': { 'name': 'default', 'responsive': true },
                    'check_callback': false // Read-only
                },
                'plugins': ['search']
            });
        }
    }

    function renderMindmapView() {
        const container = $('#abpcwa-hierarchy-mindmap');
        container.empty(); // Clear previous render

        // D3.js Mind Map implementation
        const width = container.width();
        const height = container.height();
        const svg = d3.select(container.get(0)).append("svg")
            .attr("width", width)
            .attr("height", height)
            .call(d3.zoom().on("zoom", (event) => {
               svg.attr("transform", event.transform)
            }))
            .append("g");

        // Center the graph
        svg.attr("transform", `translate(${width / 2}, ${height / 2})`);

        // Data transformation for D3
        const root = d3.stratify()
            .id(d => d.id)
            .parentId(d => d.parent === '#' ? null : d.parent)
            (hierarchyData);
        
        const treeLayout = d3.tree().size([2 * Math.PI, Math.min(width, height) / 2 - 100]);
        treeLayout(root);

        // Links
        svg.selectAll('path')
            .data(root.links())
            .enter()
            .append('path')
            .attr('d', d3.linkRadial()
                .angle(d => d.x)
                .radius(d => d.y))
            .attr('fill', 'none')
            .attr('stroke', '#ccc');

        // Nodes
        const node = svg.selectAll('g')
            .data(root.descendants())
            .enter()
            .append('g')
            .attr('transform', d => `rotate(${d.x * 180 / Math.PI - 90}) translate(${d.y},0)`);

        node.append('circle')
            .attr('r', 5)
            .attr('fill', '#2271b1');

        node.append('text')
            .attr('dy', '0.31em')
            .attr('x', d => d.x < Math.PI ? 8 : -8)
            .attr('text-anchor', d => d.x < Math.PI ? 'start' : 'end')
            .attr('transform', d => d.x >= Math.PI ? 'rotate(180)' : null)
            .text(d => d.data.text)
            .clone(true).lower()
            .attr('stroke', 'white');
    }

    function renderOrgChartView() {
        const container = $('#abpcwa-hierarchy-orgchart');
        container.empty();

        const width = container.width();
        const height = container.height();
        const svg = d3.select(container.get(0)).append("svg")
            .attr("width", width)
            .attr("height", height)
            .call(d3.zoom().on("zoom", (event) => {
               svg.attr("transform", event.transform)
            }))
            .append("g");

        // Adjust starting position
        svg.attr("transform", `translate(50, 50)`);

        const root = d3.stratify()
            .id(d => d.id)
            .parentId(d => d.parent === '#' ? null : d.parent)
            (hierarchyData);

        const treeLayout = d3.tree().nodeSize([150, 150]);
        treeLayout(root);

        // Links
        svg.selectAll('path')
            .data(root.links())
            .enter()
            .append('path')
            .attr('d', d => `M${d.source.x},${d.source.y} C ${d.source.x},${(d.source.y + d.target.y) / 2} ${d.target.x},${(d.source.y + d.target.y) / 2} ${d.target.x},${d.target.y}`)
            .attr('fill', 'none')
            .attr('stroke', '#ccc');

        // Nodes
        const node = svg.selectAll('g')
            .data(root.descendants())
            .enter()
            .append('g')
            .attr('transform', d => `translate(${d.x},${d.y})`);

        node.append('rect')
            .attr('width', 120)
            .attr('height', 40)
            .attr('x', -60)
            .attr('y', -20)
            .attr('rx', 5)
            .attr('ry', 5)
            .attr('fill', '#2271b1')
            .attr('stroke', '#fff')
            .attr('stroke-width', 2);

        node.append('text')
            .attr('dy', '0.31em')
            .attr('text-anchor', 'middle')
            .attr('fill', '#fff')
            .text(d => d.data.text);
    }

    function renderGridView() {
        const container = $('#abpcwa-hierarchy-grid');
        container.empty();

        const root = d3.stratify()
            .id(d => d.id)
            .parentId(d => d.parent === '#' ? null : d.parent)
            (hierarchyData);

        function createGrid(node, container) {
            const card = $('<div class="abpcwa-grid-card"></div>');
            card.append(`<strong>${node.data.text}</strong>`);
            card.append(`<p>ID: ${node.data.id}</p>`);
            
            if (node.children) {
                const childrenContainer = $('<div class="abpcwa-grid-children"></div>');
                node.children.forEach(child => {
                    createGrid(child, childrenContainer);
                });
                card.append(childrenContainer);
            }
            
            container.append(card);
        }

        createGrid(root, container);
    }

    // 4. Event Handlers
    function setupEventHandlers() {
        // View switcher buttons
        $('.abpcwa-view-controls').on('click', '.button', function() {
            const view = $(this).data('view');
            if (view !== currentView) {
                switchView(view);
            }
        });

        // Expand all button
        $('#abpcwa-expand-all').on('click', function() {
            if (currentView === 'tree') {
                $('#abpcwa-hierarchy-tree').jstree('open_all');
            }
        });

        // Collapse all button
        $('#abpcwa-collapse-all').on('click', function() {
            if (currentView === 'tree') {
                $('#abpcwa-hierarchy-tree').jstree('close_all');
            }
        });

        // Search functionality
        var to = false;
        $('#abpcwa-hierarchy-search').keyup(function() {
            if (to) clearTimeout(to);
            to = setTimeout(function() {
                var v = $('#abpcwa-hierarchy-search').val();
                if (currentView === 'tree') {
                    $('#abpcwa-hierarchy-tree').jstree(true).search(v);
                }
                // Add search logic for other views here
            }, 250);
        });
    }

    // 5. Initialize the script
    initHierarchy();
    setupEventHandlers();
});
