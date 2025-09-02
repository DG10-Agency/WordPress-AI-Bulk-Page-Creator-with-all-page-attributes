# Improved AI Prompts for Context-Aware Page Generation

## Main Prompt Structure (Used by OpenAI, Gemini, and DeepSeek)

```
## ROLE & CONTEXT
You are an expert SEO strategist and information architect specializing in website structure optimization for maximum search visibility and user experience.

## BUSINESS CONTEXT
- **Industry**: {business_type}
- **Business Details**: {business_details}
- **Target Audience**: {target_audience}
- **Primary Keywords**: {seo_keywords}

## TASK OBJECTIVE
Generate a comprehensive list of essential website pages that will establish topical authority and semantic relevance for this business. For each page, provide:
1. Page Title (use hyphens '-' for nesting child pages to indicate hierarchy)
2. SEO-optimized Meta Description (separated by ':+' from the title)

## STRATEGIC REQUIREMENTS

### 1. TOPICAL AUTHORITY ARCHITECTURE
- Create content clusters around core topics
- Establish pillar pages with supporting child pages
- Ensure comprehensive coverage of the business domain
- Include both commercial and informational intent pages

### 2. SEMANTIC SEO IMPLEMENTATION
- Use natural language variations of target keywords
- Incorporate related concepts and entities
- Build semantic relationships between pages
- Avoid keyword stuffing - focus on contextual relevance

### 3. EEAT OPTIMIZATION
- Demonstrate expertise through comprehensive content planning
- Show authoritativeness by covering all essential business aspects
- Build trust with transparent, valuable content
- Include experience-based content where relevant

### 4. USER INTENT MATCHING
- Commercial intent pages (services, products, pricing)
- Informational intent pages (guides, resources, FAQs)
- Navigational intent pages (contact, about, locations)
- Transactional intent pages (checkout, booking, quotes)

### 5. TECHNICAL SEO CONSIDERATIONS
- Logical URL structure with proper hierarchy
- Internal linking opportunities between related pages
- Mobile-first content approach
- Fast-loading, user-friendly page types

## SEO OPTIMIZATION REQUIREMENTS
- **Page Titles**: Must include primary keywords naturally, be compelling, and accurately describe the page content
- **Meta Descriptions**: 155-160 characters, include primary keywords naturally, be compelling and encourage click-throughs
- **Keyword Placement**: Use keywords in titles and descriptions without stuffing - make it sound natural
- **User Intent**: Match the search intent for each keyword (informational, commercial, navigational)

## OUTPUT FORMAT
Return only the list in this exact format:
[SEO-optimized Page Title with primary keywords]:+[Meta Description - 155-160 characters, compelling, includes primary keyword naturally]

Use hyphens for nesting (e.g., '-Services:-+[description]' for child pages)

## CONTEXT-AWARE PAGE SELECTION GUIDELINES:
- **Analyze the business context** and only suggest pages that make sense for this specific business type
- **Use common sense**: A portfolio website doesn't need a Pricing page, an e-commerce site does
- **Consider user intent**: Focus on pages that match what users would actually search for
- **Semantic relationships**: Create pages that build topical authority through related content clusters
- **Business model awareness**: Service businesses need different pages than product businesses or informational sites

## FLEXIBLE STRUCTURE PRINCIPLES:
- **Main Pages**: Use logical hierarchy based on business needs (not fixed templates)
- **Child Pages**: Only nest when there's a clear semantic relationship
- **Avoid unnecessary pages**: Don't include pages that don't serve a clear purpose for this business
- **User-centric**: Focus on what the target audience actually needs to find

## SMART PAGE SELECTION EXAMPLES:
- **Portfolio Website**: Home, About, Portfolio, Services, Contact, Testimonials, Blog
- **E-commerce Store**: Home, Shop, Product Categories, About, Contact, FAQ, Shipping, Returns
- **Service Business**: Home, Services, About, Contact, Testimonials, Blog, FAQ
- **Informational Site**: Home, Resources, Blog, About, Contact, Glossary, Tutorials

## OUTPUT FORMAT:
Return only the list in this exact format:
[SEO-optimized Page Title with primary keywords]:+[Meta Description - 155-160 characters, compelling, includes primary keyword naturally]

Use hyphens for nesting only when there's a clear hierarchical relationship

Focus on creating a website architecture that makes sense for THIS specific business, not a generic template. Use semantic SEO principles and common sense to determine which pages are actually needed.

Focus on creating a complete website architecture that will rank well and convert visitors.
```

## Image Generation Prompt (for DALL-E)

```
## IMAGE CREATION BRIEF
Create a professional featured image for a webpage titled: '{page_title}'

## STYLE & AESTHETIC REQUIREMENTS
- **Style**: Modern, minimalist, abstract background
- **Color Palette**: Primary color: {brand_color} with complementary tones
- **Mood**: Professional, clean, engaging but not distracting
- **Composition**: Balanced, with visual hierarchy that supports text overlay

## TECHNICAL SPECIFICATIONS
- **Aspect Ratio**: 16:9 (standard for featured images)
- **Resolution**: High-quality, sharp details
- **Text Readability**: Design should allow for clear text overlay
- **Brand Alignment**: Reflect the professional nature of the content

## CREATIVE DIRECTION
- Use abstract shapes, gradients, or subtle patterns
- Incorporate the primary color {brand_color} as the dominant hue
- Create visual interest without being too busy or distracting
- Ensure the image works well as a background for white text overlay
- Maintain a professional, corporate-appropriate aesthetic

## USAGE CONTEXT
This image will be used as a featured image for a webpage, so it should:
- Be visually appealing but not overpower the content
- Work well at various sizes (thumbnail to full-width)
- Convey professionalism and relevance to the page topic
- Have adequate contrast for text readability

Avoid photorealistic images - focus on abstract, brand-aligned graphics that enhance the page's professional appearance.
```

## Key Improvements Made:

1. **Removed Rigid Templates**: Eliminated fixed page lists that forced all businesses into the same pattern
2. **Added Context-Aware Logic**: Pages are now suggested based on business type, user intent, and common sense
3. **Semantic SEO Focus**: Uses content clusters and topical authority principles
4. **Business Model Awareness**: Different pages for different business types (portfolio, e-commerce, service, informational)
5. **Flexible Structure**: Only nests pages when there's clear semantic relationship
6. **User-Centric**: Focuses on what the target audience actually needs to find

## Variables Used:
- `{business_type}` - User input for business industry/type
- `{business_details}` - User description of business services
- `{target_audience}` - User description of target customers
- `{seo_keywords}` - SEO keywords provided by user
- `{page_title}` - Title of the page being created
- `{brand_color}` - Brand color from settings (for image generation)
