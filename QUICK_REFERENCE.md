# AI Blog Writer - Quick Reference Card

## 🚀 Instant Commands

### Setup
```bash
# Zip the plugin
cd /Users/atakhadivi/Documents/GitHub/autoblogging
zip -r ai-blog-writer.zip . -x "*.git*" "validate-plugin.php" "*_GUIDE.md" "PLUGIN_SUMMARY.md" "QUICK_REFERENCE.md"
```

### WordPress Admin URLs
- **Dashboard**: `/wp-admin/admin.php?page=ai-blog-writer`
- **Settings**: `/wp-admin/admin.php?page=ai-blog-writer-settings`
- **Generate**: `/wp-admin/admin.php?page=ai-blog-writer-generate`
- **Batch**: `/wp-admin/admin.php?page=ai-blog-writer-batch`

## 🔑 API Keys

### OpenRouter
- **Website**: https://openrouter.ai
- **Dashboard**: https://openrouter.ai/keys
- **Cost**: ~$0.01-0.05 per post

### Perplexity  
- **Website**: https://perplexity.ai
- **Dashboard**: https://perplexity.ai/settings/api
- **Cost**: ~$0.01-0.03 per post

## ⚙️ Recommended Settings

| Setting | Value | Why |
|---------|-------|-----|
| Temperature | 0.7 | Balanced creativity |
| Max Tokens | 2000 | Good post length |
| Model | Llama 3.1 8B | Fast & quality |
| Status | Draft | Review first |
| Category | 1 (Uncategorized) | Easy to organize |

## 📝 Example Topics

### Good Topics
- ✅ "How AI is transforming customer service"
- ✅ "Best practices for remote team management"
- ✅ "Sustainable business strategies for 2024"
- ✅ "Cybersecurity trends every business should know"

### Bad Topics
- ❌ "My day today" (too personal)
- ❌ "Breaking news" (needs real-time data)
- ❌ "Recipe for cookies" (not AI's strength)

## 🎯 Generation Process

### Single Post
1. Go to **Generate Post**
2. Enter topic
3. Check "Use research" (recommended)
4. Select API preference (Auto)
5. Click **Generate**
6. Wait 30-60 seconds
7. Click **View Post**

### Batch Generation
1. Go to **Batch Generator**
2. Enter topics (one per line)
3. Set delay: 2-3 seconds
4. Click **Generate Batch**
5. Monitor progress
6. Review all posts

## 🔍 Troubleshooting

| Problem | Solution |
|---------|----------|
| "API key not configured" | Add keys in Settings |
| "Connection failed" | Check keys & internet |
| "Generation too slow" | Use Llama 8B, reduce tokens |
| "Permission denied" | Use admin account |
| "Empty response" | Check API usage limits |

## 💰 Cost Calculator

**Single Post**: $0.02-0.08
**10 Posts**: $0.20-0.80  
**100 Posts**: $2.00-8.00

*Prices vary by model and content length*

## 📊 Monitoring

### Track Usage
- OpenRouter Dashboard: Usage & billing
- Perplexity Dashboard: API calls & costs
- WordPress: Posts → AI Blog Writer (meta)

### Optimize Costs
- Use cheaper models (Llama 8B)
- Reduce max tokens
- Batch during off-peak
- Review before publishing

## 🛡️ Security Checklist

- ✅ API keys stored securely
- ✅ Admin-only access
- ✅ Input sanitization
- ✅ Nonce protection
- ✅ Output escaping
- ✅ Permission checks

## 🎨 Customization Options

### Models Available
- `openrouter/meta-llama/llama-3.1-8b-instruct` (Fast)
- `openrouter/meta-llama/llama-3.1-70b-instruct` (Quality)
- `openrouter/openai/gpt-4o` (GPT-4)
- `openrouter/anthropic/claude-3-sonnet` (Claude)
- `llama-3.1-8b-instruct` (Perplexity)

### Temperature Range
- **0.1-0.4**: Focused, factual
- **0.5-0.8**: Balanced (recommended)
- **0.9-2.0**: Creative, varied

### Token Limits
- **500-1000**: Short posts
- **1000-2000**: Standard posts
- **2000-4000**: Long-form content

## 📋 Post Meta Fields

When a post is generated, these fields are added:
- `_aibw_generated`: true
- `_aibw_topic`: "Original topic"
- `_aibw_generation_time`: "2024-01-12 10:30:00"
- `_aibw_used_research`: true/false

## 🚨 Emergency Actions

### Reset Settings
1. Go to Settings
2. Click "Reset to Defaults"
3. Re-enter API keys

### Clear Cache
```bash
# If using caching plugins
wp cache flush
```

### Debug Mode
Add to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 📞 Support Resources

1. **README.md**: Full documentation
2. **SETUP_GUIDE.md**: Step-by-step setup
3. **This Card**: Quick commands & troubleshooting
4. **WordPress Debug Log**: `/wp-content/debug.log`

## ✅ Pre-Flight Checklist

Before generating content:
- [ ] API keys added in Settings
- [ ] API connections tested
- [ ] Category selected
- [ ] Post status set (Draft recommended)
- [ ] Budget confirmed
- [ ] Topic researched

---

**Ready to go!** Your plugin is validated and ready for WordPress installation.