# Schema Markup Generator Feature

## Overview

The Schema Markup Generator is a powerful addition to the AI Bulk Page Creator plugin that automatically generates structured data (schema.org) markup for your WordPress pages. This feature helps improve SEO by providing search engines with detailed information about your content, leading to better visibility and rich snippets in search results.

## Features

### 1. Automatic Schema Detection
- **Intelligent Content Analysis**: The plugin analyzes page content, titles, and structure to determine the most appropriate schema type
- **Multiple Schema Types Supported**:
  - FAQ Page (`FAQPage`)
  - Blog Post (`BlogPosting`)
  - Article (`Article`)
  - Service (`Service`)
  - Product (`Product`)
  - Organization (`Organization`)
  - Local Business (`LocalBusiness`)
  - Web Page (`WebPage`) - fallback

### 2. Real-time Generation
- **On-page Creation**: Schema is generated automatically when pages are created via manual input, CSV upload, or AI generation
- **On-save Updates**: Schema is regenerated when pages are updated (configurable in settings)
- **Bulk Operations**: Generate schema for all existing pages with a single click

### 3. Admin Interface
- **Schema Column**: Adds a "Schema" column to the pages list showing the detected schema type with color-coded badges
- **Quick Actions**: Generate/regenerate schema directly from the pages list
- **Preview Functionality**: View generated schema markup in a user-friendly preview interface
- **Statistics Dashboard**: See schema generation statistics and type distribution

### 4. Frontend Output
- **Automatic Injection**: Schema markup is automatically added to the `<head>` section of your pages
- **JSON-LD Format**: Uses the standard JSON-LD format preferred by search engines
- **Validation Ready**: Outputs properly formatted schema that passes validation tests

## How It Works

### Schema Detection Process

1. **Content Analysis**: The plugin examines page content for specific patterns:
   - FAQ pages: Questions in headings, bold text, or paragraphs
   - Blog posts: Post type and categories
   - Articles: Word count and title keywords
   - Services: Service-related keywords in title and content
   - Products: Product keywords and price information
   - Organization: About/company-related keywords
   - Local Business: Location/business keywords

2. **Type Selection**: Based on the analysis, the most appropriate schema type is selected

3. **Data Generation**: Schema data is generated with relevant properties filled from page content

### Supported Schema Types

#### FAQ Page (`FAQPage`)
- Detects question-answer pairs in content
- Extracts questions from headings and bold text
- Creates structured FAQ schema with `Question` and `Answer` entities

#### Blog Post (`BlogPosting`)
- For WordPress posts or pages with blog-like characteristics
- Includes author information, publication dates, and publisher data
- Adds featured image if available

#### Article (`Article`)
- For long-form content (500+ words) or tutorial/guide pages
- Includes comprehensive article metadata
- Supports author and publisher information

#### Service (`Service`)
- For service pages with service-related keywords
- Includes service description and provider information
- Configurable service area and type

#### Product (`Product`)
- For product pages with product keywords and pricing
- Basic product schema with SKU and offer information
- Extensible for e-commerce integration

#### Organization (`Organization`)
- For about/company pages
- Uses site-wide organization information
- Includes logo and website URL

#### Local Business (`LocalBusiness`)
- For location-based business pages
- Basic implementation with business information
- Can be extended with address data

#### Web Page (`WebPage`)
- Fallback schema for all other pages
- Basic webpage information with publisher data

## Installation & Setup

### 1. Enable Auto-generation
Go to **AI Bulk Pages → Settings → Schema Settings** and ensure "Auto Schema Generation" is enabled.

### 2. Configure Settings
- **Auto Schema Generation**: Enable/disable automatic schema generation on page save
- Default: Enabled

### 3. Verify Output
Check your page source code to see the generated schema markup in the `<head>` section.

## Usage

### Manual Schema Generation

1. **From Pages List**:
   - Hover over any page in the Pages list
   - Click "Generate Schema" or "Regenerate Schema" in the quick actions

2. **From Schema Generator Tab**:
   - Go to **AI Bulk Pages → Schema Generator**
   - Use "Generate Schema for All Pages" for bulk operations
   - Select specific pages from the dropdown for individual generation

### Schema Preview

1. **In Schema Generator Tab**:
   - Select a page from the preview dropdown
   - View the generated JSON-LD markup in real-time

2. **Frontend Verification**:
   - View page source and look for `<script type="application/ld+json">`
   - Use Google's Rich Results Test to validate schema

### Customization

The schema generation can be extended by modifying the `includes/schema-generator.php` file. Each schema type has its own generation function that can be customized.

## Technical Details

### Database Storage
- Schema type stored as `_abpcwa_schema_type` post meta
- Schema data stored as `_abpcwa_schema_data` post meta (serialized array)

### Hooks & Filters
- `save_post`: Triggers schema generation on page save
- `wp_head`: Outputs schema markup in frontend
- AJAX handlers for admin preview functionality

### CSS Classes
Schema badges use the following CSS classes for styling:
- `.abpcwa-schema-badge`: Base badge style
- `.abpcwa-schema-{type}`: Type-specific styling (faq, blog, article, etc.)
- `.abpcwa-schema-none`: For pages without schema

## Best Practices

1. **Review Generated Schema**: Always check the generated schema matches your content intent
2. **Use Rich Results Test**: Validate your schema with Google's testing tool
3. **Regular Regeneration**: Regenerate schema after significant content changes
4. **Monitor Performance**: Large sites should use bulk operations during low-traffic periods

## Troubleshooting

### Common Issues

1. **No Schema Generated**:
   - Check if auto-generation is enabled in settings
   - Verify the page has sufficient content for detection

2. **Incorrect Schema Type**:
   - The detection is based on content patterns
   - Manually regenerate if detection is incorrect

3. **Validation Errors**:
   - Check for missing required fields
   - Ensure content is properly formatted

### Support

For issues with schema generation, check:
- Plugin error logs
- Schema validation tools
- Content formatting and structure

## Future Enhancements

Planned improvements include:
- Enhanced e-commerce product schema
- Event schema support
- Recipe schema for food blogs
- Review/rating schema integration
- Custom schema template system

## Changelog

### v1.0.0 (Initial Release)
- Basic schema detection and generation
- 8 supported schema types
- Admin interface integration
- Bulk operations support
- Frontend output automation
