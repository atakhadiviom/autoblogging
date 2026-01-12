# Quick Setup Guide - AI Blog Writer Plugin

## 🚀 5-Minute Setup

### Step 1: Get API Keys

**OpenRouter API Key:**
1. Go to [OpenRouter.ai](https://openrouter.ai)
2. Sign up or log in
3. Go to Dashboard → API Keys
4. Click "Create Key" and copy it

**Perplexity API Key:**
1. Go to [Perplexity.ai](https://perplexity.ai)
2. Sign up or log in
3. Go to Account → API
4. Generate API key and copy it

### Step 2: Install Plugin

**Option A: Upload**
1. Zip the `ai-blog-writer` folder
2. WordPress Admin → Plugins → Add New → Upload Plugin
3. Upload and activate

**Option B: Manual**
1. Copy folder to `/wp-content/plugins/`
2. Go to Plugins → Find "AI Blog Writer" → Activate

### Step 3: Configure

1. Go to **AI Blog Writer → Settings**
2. Paste your OpenRouter API key
3. Paste your Perplexity API key
4. Click **Save Settings**
5. Click **Test All APIs** to verify connections

### Step 4: Generate Your First Post

1. Go to **AI Blog Writer → Generate Post**
2. Enter a topic: "The Impact of AI on Modern Marketing"
3. Keep "Use research" checked
4. Click **Generate Post**
5. View your new post!

## 💡 Pro Tips

### Best Topics
- ✅ "How blockchain is changing finance"
- ✅ "Latest trends in remote work"
- ✅ "AI tools for small businesses"
- ❌ "My personal story" (too personal)
- ❌ "Breaking news today" (needs real-time data)

### Settings Recommendations
- **Temperature**: 0.7 (balanced)
- **Max Tokens**: 2000 (good length)
- **Model**: Llama 3.1 8B (fast & good)
- **Status**: Draft (review first)

### Batch Generation
```
Topic 1: Future of electric vehicles
Topic 2: Cybersecurity best practices 2024
Topic 3: Sustainable business practices
```

## 🔍 Troubleshooting

### "API Connection Failed"
- ✅ Check API keys are correct
- ✅ Verify API service is online
- ✅ Check billing status on API accounts

### "Generation Takes Too Long"
- ✅ Reduce max tokens to 1000
- ✅ Use Llama 8B instead of 70B
- ✅ Check your internet speed

### "Permission Denied"
- ✅ Make sure you're admin user
- ✅ Check user role in WordPress

## 💰 Cost Estimates

**Per Post:**
- OpenRouter: ~$0.01-0.05
- Perplexity: ~$0.01-0.03
- **Total: ~$0.02-0.08 per post**

**Batch of 10 Posts:**
- **Total: ~$0.20-0.80**

*Prices vary by model and content length*

## 📊 Monitoring Usage

### Track API Calls
1. Check your API provider dashboards
2. Monitor monthly spending
3. Set up billing alerts

### Optimize Costs
- Use cheaper models (Llama 8B)
- Reduce token limits
- Batch during off-peak hours

## 🎯 Next Steps

1. **Experiment** with different topics
2. **Adjust** temperature for creativity
3. **Try** batch generation
4. **Review** and edit generated posts
5. **Add** images and custom formatting

## 🆘 Need Help?

1. Check the full README.md
2. Enable debug mode in wp-config.php
3. Check browser console for JavaScript errors
4. Review WordPress debug log

---

**Ready to start?** Go to your WordPress admin and try it out!