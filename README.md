# AI Blog Writer WordPress Plugin

A powerful WordPress plugin that generates high-quality blog posts using OpenRouter and Perplexity APIs. This plugin leverages AI to research topics, write engaging content, and automatically publish posts to your WordPress site.

## Features

### 🤖 Dual AI Integration
- **OpenRouter API**: For creative writing and content generation
- **Perplexity API**: For research and current information gathering
- **Smart Routing**: Automatically chooses the best API for each task

### ✨ Content Generation
- **Single Post Generation**: Create one post at a time with custom settings
- **Batch Processing**: Generate multiple posts simultaneously
- **Research-Enhanced**: Uses Perplexity to gather current data before writing
- **SEO Optimization**: Generates SEO-friendly titles and excerpts

### 🎨 Customization Options
- **Model Selection**: Choose from various AI models (Llama, GPT-4, Claude)
- **Temperature Control**: Adjust creativity vs. focus (0.1-2.0)
- **Token Limits**: Control response length (100-4000 tokens)
- **Post Settings**: Set default category and publication status

### 🛡️ Security & Safety
- **API Key Security**: Keys stored securely in WordPress options
- **Input Sanitization**: All inputs properly sanitized
- **Nonce Verification**: AJAX requests protected
- **Permission Checks**: Admin-only access to features

## Installation

1. **Upload Plugin**
   ```bash
   # Clone or download this repository
   git clone https://github.com/atakhadivi/autoblogging.git
   ```

2. **Activate Plugin**
   - Go to WordPress Admin → Plugins
   - Find "AI Blog Writer" and click Activate

3. **Configure Settings**
   - Go to AI Blog Writer → Settings
   - Add your API keys
   - Configure your preferences

## API Setup

### OpenRouter API
1. Sign up at [OpenRouter.ai](https://openrouter.ai)
2. Generate an API key from your dashboard
3. Add the key to plugin settings

### Perplexity API
1. Sign up at [Perplexity.ai](https://perplexity.ai)
2. Get your API key from account settings
3. Add the key to plugin settings

## Usage

### Single Post Generation
1. Navigate to **AI Blog Writer → Generate Post**
2. Enter your topic (e.g., "The Future of AI in Healthcare")
3. Choose options:
   - Use research (recommended for current topics)
   - Select API preference
   - Choose target category
4. Click **Generate Post**
5. View or edit the generated post

### Batch Generation
1. Navigate to **AI Blog Writer → Batch Generator**
2. Enter multiple topics (one per line)
3. Set delay between posts (to avoid rate limits)
4. Click **Generate Batch**
5. Monitor progress and view results

### Settings Configuration
Navigate to **AI Blog Writer → Settings** to configure:
- **API Keys**: Your OpenRouter and Perplexity keys
- **Model Selection**: Choose default AI model
- **Temperature**: Control creativity (higher = more creative)
- **Max Tokens**: Limit response length
- **Default Category**: Where posts are published
- **Default Status**: Draft, Pending, or Published

## Available AI Models

### OpenRouter Models
- `openrouter/meta-llama/llama-3.1-8b-instruct` (Fast, efficient)
- `openrouter/meta-llama/llama-3.1-70b-instruct` (High quality)
- `openrouter/openai/gpt-4o` (GPT-4 with vision)
- `openrouter/anthropic/claude-3-sonnet` (Claude 3)

### Perplexity Models
- `llama-3.1-8b-instruct` (Research-focused)

## How It Works

### Generation Process
1. **Research Phase** (if enabled)
   - Perplexity API gathers current information about the topic
   - Extracts key facts, statistics, and trends

2. **Writing Phase**
   - OpenRouter API writes the blog post
   - Uses research data as reference
   - Creates structured content with headings

3. **SEO Optimization**
   - Generates compelling title (under 60 chars)
   - Creates excerpt (150-160 chars)
   - Formats for readability

4. **WordPress Integration**
   - Creates post with selected category
   - Sets specified status (draft/pending/publish)
   - Adds metadata for tracking

## Technical Details

### File Structure
```
ai-blog-writer/
├── ai-blog-writer.php          # Main plugin file
├── includes/
│   ├── class-aibw-api-handler.php      # API integration
│   ├── class-aibw-post-generator.php   # Content generation
│   ├── class-aibw-settings.php         # Settings management
│   └── class-aibw-admin.php            # Admin interface
├── assets/
│   ├── js/
│   │   └── admin.js                    # Admin JavaScript
│   └── css/
│       └── admin.css                   # Admin styles
└── README.md
```

### Database Options
- `aibw_settings`: Plugin configuration stored in WordPress options

### Post Meta
- `_aibw_generated`: Boolean flag for AI-generated posts
- `_aibw_topic`: Original topic used for generation
- `_aibw_generation_time`: Timestamp of creation
- `_aibw_used_research`: Whether research was used

## Error Handling

The plugin includes comprehensive error handling:
- **API Connection Errors**: Clear error messages for connection issues
- **Rate Limiting**: Built-in delays to avoid API limits
- **Validation**: Input validation before API calls
- **Logging**: Errors logged to WordPress debug log

## Best Practices

### Content Quality
- **Specific Topics**: More specific = better results
- **Research Enabled**: Always use research for current topics
- **Review Content**: Always review before publishing
- **Edit as Needed**: Use generated content as starting point

### API Management
- **Monitor Usage**: Keep track of API calls
- **Set Limits**: Use reasonable token limits
- **Batch Carefully**: Don't overload with too many posts
- **Test First**: Test with single post before batch

### SEO Optimization
- **Custom Titles**: Consider editing generated titles
- **Add Images**: Add relevant images to posts
- **Internal Links**: Add links to related content
- **Meta Descriptions**: Add custom meta descriptions

## Troubleshooting

### Common Issues

**"No API key configured"**
- Go to Settings and add your API keys
- Make sure keys are valid and active

**"API request failed"**
- Check your internet connection
- Verify API keys are correct
- Check API service status
- Review error message for details

**"Permission denied"**
- Make sure you're logged in as admin
- Check user role capabilities

**"Generation takes too long"**
- Reduce max tokens in settings
- Use faster models (Llama 8B)
- Check API service status

### Debug Mode
To enable debug logging, add to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

## Performance Tips

1. **Use Fast Models**: Llama 8B is faster than 70B
2. **Limit Tokens**: Set reasonable max token limits
3. **Batch Delays**: Add 2-3 second delays between batch posts
4. **Draft Status**: Generate as draft first, review, then publish

## Security Considerations

- **API Keys**: Never share your API keys
- **User Permissions**: Only admins can access plugin features
- **Input Validation**: All inputs are sanitized
- **Nonce Protection**: AJAX requests require valid nonces
- **Capability Checks**: WordPress capability checks on all actions

## Support & Contributing

### Getting Help
- Check this README first
- Review error messages carefully
- Check API service status pages
- Enable debug logging if needed

### Contributing
Contributions are welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Submit a pull request

## License

GPL v2 or later. This plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation.

## Credits

- **Developer**: Your Name
- **APIs**: OpenRouter.ai and Perplexity.ai
- **Framework**: WordPress

## Changelog

### 1.0.0
- Initial release
- OpenRouter integration
- Perplexity integration
- Single post generation
- Batch generation
- Admin interface
- Settings management
- Error handling
- Security features

---

**Note**: This plugin uses third-party AI services. Usage costs apply according to each service's pricing. Always review generated content before publishing.