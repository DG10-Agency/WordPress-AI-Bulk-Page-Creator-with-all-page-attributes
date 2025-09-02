# Menu Generation Feature

## Overview
The plugin now includes an automated menu generation system that can create WordPress menus based on your created pages. This feature helps you quickly set up navigation menus after creating pages in bulk.

## Available Menu Types

### 1. Universal Bottom Menu
- **Purpose**: Comprehensive footer menu with essential links
- **Includes**:
  - Home page link
  - All legal pages (auto-detected by title patterns)
  - Sitemap link (configurable in settings)
  - Contact page (if exists)
- **Auto-assignment**: Attempts to assign to footer menu location

### 2. Services Menu
- **Purpose**: Navigation menu for service pages
- **Detection**: Finds pages with "service", "solution", "offer", or "package" in titles
- **Includes**: Main "Services" link + individual service pages
- **Usage**: Ideal for header navigation

### 3. Company Menu  
- **Purpose**: Menu for company information pages
- **Detection**: Finds pages like About, Team, Mission, Contact
- **Includes**: All company-related pages
- **Usage**: Perfect for footer or secondary navigation

## How to Use

### Step 1: Configure Sitemap URL
1. Go to Settings tab
2. Enter your sitemap URL in the "Sitemap URL" field
3. Save changes

### Step 2: Generate Menus
1. Go to Menu Generator tab
2. Click the desired menu type button
3. The plugin will:
   - Detect relevant pages automatically
   - Create the WordPress menu
   - Attempt auto-assignment to appropriate locations

### Step 3: Review and Customize
1. Go to WordPress Appearance → Menus
2. Review the generated menus
3. Make any customizations as needed
4. Assign to menu locations if not auto-assigned

## Technical Implementation

### Page Detection
The plugin uses smart pattern matching to detect page types:
- **Legal pages**: `/privacy|policy|gdpr|terms|conditions|disclaimer|cookie|refund|shipping|return|legal/i`
- **Service pages**: `/service|solution|offer|package/i`
- **Company pages**: `/about|company|team|mission|vision|story|career|contact/i`

### Settings Integration
- Sitemap URL stored in `abpcwa_sitemap_url` option
- Accessed via `get_option('abpcwa_sitemap_url', '')`

### WordPress Compliance
- Uses standard WordPress menu functions: `wp_create_nav_menu()`, `wp_update_nav_menu_item()`
- Respects existing menu locations and themes
- No core modifications - completely safe

## Error Handling
- **No pages found**: Shows warning message, doesn't create empty menus
- **Menu creation failure**: Shows error message with details
- **Theme compatibility**: Falls back to menu creation without auto-assignment if theme doesn't support standard locations

## Customization
You can extend the menu detection patterns by modifying the pattern arrays in `includes/menu-generator.php`:

```php
// Legal patterns
$legal_patterns = array(
    '/privacy|policy|gdpr/i',
    '/terms|conditions|service/i',
    // Add your custom patterns here
);
```

## Best Practices
1. **Create pages first**: Generate menus after creating all relevant pages
2. **Use descriptive titles**: Helps with accurate auto-detection
3. **Review generated menus**: Always check the results in Appearance → Menus
4. **Backup first**: Consider backing up your menu structure before generation

The menu generation feature provides a quick way to set up professional navigation after bulk page creation, saving time while maintaining WordPress standards.
