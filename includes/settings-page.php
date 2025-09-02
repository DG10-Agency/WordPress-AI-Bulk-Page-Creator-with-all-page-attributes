<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Register settings
function abpcwa_register_settings() {
    register_setting('abpcwa_settings_group', 'abpcwa_ai_provider');
    register_setting('abpcwa_settings_group', 'abpcwa_openai_api_key');
    register_setting('abpcwa_settings_group', 'abpcwa_gemini_api_key');
    register_setting('abpcwa_settings_group', 'abpcwa_deepseek_api_key');
}
add_action('admin_init', 'abpcwa_register_settings');

// Settings tab content
function abpcwa_settings_tab() {
    ?>
    <form method="post" action="options.php">
        <?php
        settings_fields('abpcwa_settings_group');
        do_settings_sections('ai-bulk-page-creator');
        submit_button();
        ?>
    </form>
    <?php
}

// Add settings section and fields
function abpcwa_settings_init() {
    add_settings_section(
        'abpcwa_settings_section',
        'AI Settings',
        'abpcwa_settings_section_callback',
        'ai-bulk-page-creator'
    );

    add_settings_field(
        'abpcwa_ai_provider',
        'AI Provider',
        'abpcwa_ai_provider_callback',
        'ai-bulk-page-creator',
        'abpcwa_settings_section'
    );

    add_settings_field(
        'abpcwa_openai_api_key',
        'OpenAI API Key',
        'abpcwa_openai_api_key_callback',
        'ai-bulk-page-creator',
        'abpcwa_settings_section'
    );

    add_settings_field(
        'abpcwa_gemini_api_key',
        'Gemini API Key',
        'abpcwa_gemini_api_key_callback',
        'ai-bulk-page-creator',
        'abpcwa_settings_section'
    );

    add_settings_field(
        'abpcwa_deepseek_api_key',
        'DeepSeek API Key',
        'abpcwa_deepseek_api_key_callback',
        'ai-bulk-page-creator',
        'abpcwa_settings_section'
    );
}
add_action('admin_init', 'abpcwa_settings_init');

// Section callback
function abpcwa_settings_section_callback() {
    echo '<p>Select your preferred AI provider and enter the corresponding API key.</p>';
}

// AI Provider field callback
function abpcwa_ai_provider_callback() {
    $provider = get_option('abpcwa_ai_provider', 'openai');
    ?>
    <select name="abpcwa_ai_provider">
        <option value="openai" <?php selected($provider, 'openai'); ?>>OpenAI</option>
        <option value="gemini" <?php selected($provider, 'gemini'); ?>>Gemini</option>
        <option value="deepseek" <?php selected($provider, 'deepseek'); ?>>DeepSeek</option>
    </select>
    <?php
}

// OpenAI API Key field callback
function abpcwa_openai_api_key_callback() {
    $api_key = get_option('abpcwa_openai_api_key');
    echo '<input type="text" name="abpcwa_openai_api_key" value="' . esc_attr($api_key) . '" class="regular-text">';
}

// Gemini API Key field callback
function abpcwa_gemini_api_key_callback() {
    $api_key = get_option('abpcwa_gemini_api_key');
    echo '<input type="text" name="abpcwa_gemini_api_key" value="' . esc_attr($api_key) . '" class="regular-text">';
}

// DeepSeek API Key field callback
function abpcwa_deepseek_api_key_callback() {
    $api_key = get_option('abpcwa_deepseek_api_key');
    echo '<input type="text" name="abpcwa_deepseek_api_key" value="' . esc_attr($api_key) . '" class="regular-text">';
}
