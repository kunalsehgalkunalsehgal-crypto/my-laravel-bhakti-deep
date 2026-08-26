# SEO Blog CMS - Setup Checklist

## Step 1: Run Migration
```bash
php artisan migrate
```
✅ This adds all new SEO fields to the blogs table

---

## Step 2: Verify Files Created

### Backend Files
- ✅ `app/Http/Controllers/Admin/AdminBlogController.php` - Updated with SEO logic
- ✅ `app/Models/Admin/Blog.php` - Updated with new fields
- ✅ `app/Http/Controllers/BlogController.php` - Updated with show() method
- ✅ `database/migrations/2026_07_03_000001_upgrade_blogs_to_seo_cms.php` - Migration file

### Admin Views
- ✅ `resources/views/admin/blogs/form.blade.php` - Create/Edit form with CKEditor
- ✅ `resources/views/admin/blogs/index.blade.php` - Blog listing with new columns

### Frontend Views
- ✅ `resources/views/pages/blog-detail.blade.php` - Blog detail page with schema
- ✅ `resources/views/pages/blogs.blade.php` - Updated blog listing

### Routes
- ✅ `routes/web.php` - Added `/blogs/{slug}` route

---

## Step 3: Test Admin Blog Creation

1. Go to `/admin/login`
2. Login with: `admin@bhaktideep.com` / `BhaktiDeep@123`
3. Navigate to **Blogs** → **Add New**
4. Fill in all fields:
   - Category: Select one
   - Title: "Test Blog Post"
   - Slug: Auto-generates
   - Focus Keyword: "test keyword"
   - Excerpt: "This is a test"
   - Content: Use CKEditor to add content
   - Featured Image: Upload an image
   - Image Alt: "Test image"
   - Meta Title: "Test Blog - 50 chars"
   - Meta Description: "This is a test blog description - 150 chars"
   - Tags: "test, blog, seo"
   - Author Name: "Your Name"
   - Status: Published
5. Click **Save Blog**

---

## Step 4: Test FAQ Repeater

1. In the blog form, scroll to **FAQs** section
2. Click **+ Add FAQ**
3. Enter:
   - Question: "What is this blog about?"
   - Answer: "This blog is about testing the FAQ system"
4. Click **Save FAQ**
5. Add another FAQ to test multiple items
6. Click **Save Blog**

---

## Step 5: Test Frontend Blog Display

1. Go to `/blogs` - Should see blog listing
2. Click on blog card - Should go to `/blogs/{slug}`
3. Verify:
   - Blog title displays
   - Featured image shows with alt text
   - Content renders properly
   - Author name shows
   - Publication date shows
   - Tags display
   - FAQs show as accordion
   - Related blogs show at bottom

---

## Step 6: Verify SEO Meta Tags

1. Open blog detail page in browser
2. Right-click → **View Page Source**
3. Search for:
   - `<meta property="og:title"` - Should show OG title
   - `<meta property="og:description"` - Should show OG description
   - `<meta property="og:image"` - Should show OG image
   - `<link rel="canonical"` - Should show canonical URL
   - `<script type="application/ld+json">` - Should show schema markup

---

## Step 7: Test Schema Markup

1. Go to [Google Rich Results Test](https://search.google.com/test/rich-results)
2. Enter blog URL: `http://localhost/blogs/{slug}`
3. Verify:
   - BlogPosting schema detected
   - FAQPage schema detected (if FAQs exist)
   - All fields populated correctly

---

## Step 8: Test Character Counters

1. In admin blog form, go to **SEO Settings**
2. Type in **Meta Title** field
3. Verify counter shows: `(X/60)`
4. Type in **Meta Description** field
5. Verify counter shows: `(X/160)`

---

## Step 9: Test Auto-Generation

1. In admin blog form, enter Title: "Hawan Guide Step by Step Process"
2. Verify **Slug** auto-generates: `hawan-guide-step-by-step-process`
3. Leave **Meta Title** empty
4. Save blog
5. Verify **Meta Title** auto-filled with first 60 chars of title

---

## Step 10: Test Scheduled Publishing

1. Create a new blog
2. Set Status: **Scheduled**
3. Set **Published At**: Future date/time
4. Save blog
5. Go to `/blogs` - Blog should NOT appear
6. Go to admin blogs list - Blog should show as "Scheduled"

---

## Step 11: Test Category Filtering

1. Go to `/blogs`
2. Click on category pill (e.g., "Pooja")
3. URL should change to `/blogs/category/pooja`
4. Only blogs in that category should display

---

## Step 12: Test Search

1. Go to admin blogs list
2. Search for blog title or focus keyword
3. Results should filter correctly

---

## Troubleshooting

### CKEditor Not Loading
- Check browser console for errors
- Verify CDN link: `https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js`
- Clear browser cache

### Images Not Uploading
- Check `storage/app/public/blogs/` directory exists
- Run: `php artisan storage:link`
- Verify file permissions

### Schema Not Showing
- Check blog is published (not draft)
- View page source to verify JSON-LD script tag
- Use Google Rich Results Test

### FAQ Modal Not Opening
- Check browser console for JavaScript errors
- Verify CKEditor script loaded
- Try in different browser

### Slug Not Unique
- Ensure slug is unique in database
- Edit slug manually if needed
- Check for soft-deleted blogs

---

## Database Backup

Before running migration, backup your database:

```bash
# MySQL
mysqldump -u root -p bhaktideep > backup_$(date +%Y%m%d_%H%M%S).sql

# Or use Laravel
php artisan backup:run
```

---

## Rollback (If Needed)

```bash
php artisan migrate:rollback
```

This will remove all new columns added by the migration.

---

## Performance Optimization

### Enable Query Caching
```php
// config/cache.php
'default' => 'redis'
```

### Index JSON Fields
```sql
ALTER TABLE blogs ADD INDEX idx_tags (tags(100));
ALTER TABLE blogs ADD INDEX idx_faqs (faqs(100));
```

### Optimize Images
- Use WebP format
- Compress before upload
- Set max file size to 2MB

---

## Security Notes

1. **CSRF Protection**: All forms include `@csrf`
2. **File Upload**: Only images allowed, max 2MB
3. **HTML Content**: Use `{!! !!}` carefully, sanitize if needed
4. **JSON Validation**: All JSON fields validated

---

## Next Steps

1. ✅ Run migration
2. ✅ Test admin blog creation
3. ✅ Test frontend display
4. ✅ Verify SEO meta tags
5. ✅ Test schema markup
6. ✅ Deploy to production

---

## Support Resources

- **Laravel Docs**: https://laravel.com/docs
- **CKEditor Docs**: https://ckeditor.com/docs
- **Schema.org**: https://schema.org
- **Google Search Console**: https://search.google.com/search-console

---

## Deployment Checklist

- [ ] Run migration on production
- [ ] Set proper file permissions
- [ ] Configure storage symlink
- [ ] Test blog creation on production
- [ ] Verify SEO meta tags
- [ ] Submit sitemap to Google Search Console
- [ ] Monitor search console for errors

---

**Setup Complete!** 🎉

Your SEO-friendly blog CMS is ready to use.

