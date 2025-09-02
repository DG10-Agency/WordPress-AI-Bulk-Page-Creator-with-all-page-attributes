<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add admin menu
function abpcwa_add_admin_menu() {
    add_menu_page(
        'AI Bulk Page Creator',
        'AI Bulk Pages',
        'manage_options',
        'ai-bulk-page-creator',
        'abpcwa_admin_page',
        ABPCWA_PLUGIN_URL . 'assets/images/dg10-logo.svg',
        25
    );
}
add_action('admin_menu', 'abpcwa_add_admin_menu');

// Admin page content
function abpcwa_admin_page() {
    ?>
    <div class="wrap">
        <h1>AI Bulk Page Creator with Attributes</h1>
        <div class="nav-tab-wrapper">
            <a href="?page=ai-bulk-page-creator&tab=manual" class="nav-tab <?php echo !isset($_GET['tab']) || $_GET['tab'] == 'manual' ? 'nav-tab-active' : ''; ?>">Manual Creation</a>
            <a href="?page=ai-bulk-page-creator&tab=csv" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] == 'csv' ? 'nav-tab-active' : ''; ?>">CSV Upload</a>
            <a href="?page=ai-bulk-page-creator&tab=ai" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] == 'ai' ? 'nav-tab-active' : ''; ?>">Generate with AI</a>
            <a href="?page=ai-bulk-page-creator&tab=hierarchy" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] == 'hierarchy' ? 'nav-tab-active' : ''; ?>">Page Hierarchy</a>
            <a href="?page=ai-bulk-page-creator&tab=settings" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] == 'settings' ? 'nav-tab-active' : ''; ?>">Settings</a>
        </div>
        <?php
        $tab = isset($_GET['tab']) ? $_GET['tab'] : 'manual';
        if ($tab == 'manual') {
            abpcwa_manual_creation_tab();
        } elseif ($tab == 'csv') {
            abpcwa_csv_upload_tab();
        } elseif ($tab == 'ai') {
            abpcwa_ai_generation_tab();
        } elseif ($tab == 'hierarchy') {
            abpcwa_hierarchy_tab();
        } else {
            abpcwa_settings_tab();
        }
        ?>
        <div class="dg10-footer-promo">
            <p>
                This plugin is brought to you by <a href="https://www.dg10.agency" target="_blank">DG10 Agency</a>. 
                This is an open-source project. Feel free to <a href="<?php echo ABPCWA_GITHUB_URL; ?>" target="_blank">star us on GitHub</a>.
            </p>
        </div>
    </div>
    <?php
}

// Manual creation tab content
function abpcwa_manual_creation_tab() {
    ?>
    <form method="post" action="">
        <?php wp_nonce_field('abpcwa_manual_create_pages'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Page Titles</th>
                <td>
                    <textarea name="abpcwa_titles" id="abpcwa_titles" rows="10" cols="50" class="large-text"></textarea>
                    <p class="description">
                        Enter one page title per line. Use hyphens for nesting.<br>
                        - Use <code>:+</code> for the page excerpt (meta description).<br>
                        - Use <code>:*</code> for featured image URL.<br>
                        - Use <code>::template=template-name.php</code> for page template.<br>
                        - Use <code>::status=draft</code> for post status (publish, draft, private, pending).
                    </p>
                </td>
            </tr>
        </table>
        <?php submit_button('Create Pages'); ?>
    </form>
    <?php
    if (isset($_POST['submit']) && check_admin_referer('abpcwa_manual_create_pages')) {
        abpcwa_create_pages_manually($_POST['abpcwa_titles']);
    }
}

// CSV upload tab content
function abpcwa_csv_upload_tab() {
    ?>
    <form method="post" action="" enctype="multipart/form-data">
        <?php wp_nonce_field('abpcwa_csv_upload'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">CSV File</th>
                <td>
                    <input type="file" name="abpcwa_csv_file" id="abpcwa_csv_file" accept=".csv">
                    <p class="description">
                        Upload a CSV file with the following columns: <code>post_title</code>, <code>post_parent</code>, <code>meta_description</code>, <code>featured_image</code>, <code>page_template</code>, <code>post_status</code>.
                        <br>The <code>post_parent</code> column should contain the title of the parent page.
                    </p>
                </td>
            </tr>
        </table>
        <?php submit_button('Upload and Create Pages'); ?>
    </form>
    <?php
    if (isset($_POST['submit']) && check_admin_referer('abpcwa_csv_upload')) {
        if (isset($_FILES['abpcwa_csv_file']) && !empty($_FILES['abpcwa_csv_file']['tmp_name'])) {
            abpcwa_create_pages_from_csv($_FILES['abpcwa_csv_file']);
        } else {
            echo '<div class="notice notice-error"><p>Please select a CSV file to upload.</p></div>';
        }
    }
}
