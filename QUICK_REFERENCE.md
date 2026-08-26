# SEO Blog CMS - Quick Reference Guide

## Installation Commands

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Clear Cache (if needed)
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### 3. Storage Link (for image uploads)
```bash
php artisan storage:link
```

---

## Admin URLs

| Page | URL |
|------|-----|
| Admin Login | `/admin/login` |
| Admin Dashboard | `/admin/dashboard` |
| Blogs List | `/admin/blogs` |
| Add New Blog | `/admin/blogs/create` |
| Edit Blog | `/admin/blogs/{id}/edit` |

**Credentials**:
- Email: `admin@bhaktideep.com`
- Password: `BhaktiDeep@123`

---

## Frontend URLs

| Page | URL |
|------|-----|
| All Blogs | `/blogs` |
| Category Filter | `/blogs/category/{slug}` |
| Blog Detail | `/blogs/{slug}` |

**Example URLs**:
- `/blogs/category/pooja`
- `/blogs/category/hawan`
- `/blogs/category/diya`
- `/blogs/hawan-guide-step-by-step`

---

## Database Queries

### View All Blogs
```sql
SELECT id, title, slug, status, published_at FROM blogs ORDER BY published_at DESC;
```

### View Published Blogs Only
```sql
SELECT id, title, slug, published_at FROM blogs WHERE status = 'published' ORDER BY published_at DESC;
```

### View Blogs by Category
```sql
SELECT b.id, b.title, b.slug, bc.name as category 
FROM blogs b 
JOIN blog_categories bc ON b.blog_category_id = bc.id 
WHERE bc.slug = 'pooja' 
ORDER BY b.published_at DESC;
```

### View Blogs with FAQs
```sql
SELECT id, title, slug, faqs FROM blogs WHERE faqs IS NOT NULL AND faqs != '[]';
```

### Count Blogs by Status
```sql
SELECT status, COUNT(*) as count FROM blogs GROUP BY status;
```

---

## File Locations

### Backend
```
app/
├── Http/Controllers/
│   ├── Admin/AdminBlogController.php
│   └── BlogController.php
└── Models/Admin/Blog.php

database/
└── migrations/
    └── 2026_07_03_000001_upgrade_blogs_to_seo_cms.php
```

### Frontend
```
resources/views/
├── admin/blogs/
│   ├── form.blade.php
│   └── index.blade.php
└── pages/
    ├── blog-detail.blade.php
    └── blogs.blade.php
```

### Routes
```
routes/web.php
```

---

## Key Features Summary

### Admin Features
- ✅ Rich text editor (CKEditor 5)
- ✅ Auto-slug generation
- ✅ Character counters for meta fields
- ✅ FAQ repeater with add/remove
- ✅ Auto-schema generation
- ✅ Image upload (featured + OG)
- ✅ Draft/Published/Scheduled status
- ✅ Tag management
- ✅ Author name field
- ✅ Canonical URL support

### Frontend Features
- ✅ Blog listing with pagination
- ✅ Category filtering
- ✅ Blog detail page
- ✅ SEO meta tags
- ✅ Schema markup (BlogPosting + FAQPage)
- ✅ FAQ accordion
- ✅ Related articles
- ✅ Author information
- ✅ Read time calculation
- ✅ Social sharing meta tags

---

## Form Fields Reference

### Basic Details
- Category (required)
- Title (required)
- Slug (auto-generated, editable)
- Focus Keyword

### Content
- Excerpt
- Content (CKEditor)

### Image & Media
- Featured Image
- Featured Image Alt Text
- OG Image

### SEO Settings
- Meta Title (50-60 chars)
- Meta Description (150-160 chars)
- Canonical URL
- Tags (comma-separated)

### Social Sharing
- OG Title
- OG Description

### FAQs
- Question
- Answer
- Add/Remove buttons

### Schema Markup
- Auto-generated JSON-LD
- Editable textarea

### Publish Settings
- Author Name
- Status (Draft/Published/Scheduled)
- Published At (date/time)

---

## Validation Rules

```
category_id: required, exists in blog_categories
title: required, max 255 chars
slug: unique, max 255 chars
focus_keyword: max 100 chars
excerpt: max 500 chars
content: any length
featured_image: image, max 2MB
image_alt: max 255 chars
tags: comma-separated string
author_name: max 255 chars
canonical_url: valid URL format
meta_title: max 60 chars
meta_description: max 160 chars
og_title: max 255 chars
og_description: max 255 chars
og_image: image, max 2MB
faqs: valid JSON
schema_markup: valid JSON
status: draft|published|scheduled
published_at: valid date
```

---

## API Endpoints (if needed)

### Get All Blogs
```
GET /api/blogs
```

### Get Blog by Slug
```
GET /api/blogs/{slug}
```

### Get Blogs by Category
```
GET /api/blogs/category/{slug}
```

---

## Troubleshooting Commands

### Clear All Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Rebuild Cache
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Check Database Connection
```bash
php artisan tinker
>>> DB::connection()->getPdo()
```

### View Logs
```bash
tail -f storage/logs/laravel.log
```

### Reset Database (Development Only)
```bash
php artisan migrate:reset
php artisan migrate
php artisan db:seed
```

---

## Performance Tips

### Enable Query Logging
```php
// In routes or controller
DB::enableQueryLog();
// ... your code ...
dd(DB::getQueryLog());
```

### Optimize Queries
```php
// Use eager loading
Blog::with('category')->get();

// Use select specific columns
Blog::select('id', 'title', 'slug')->get();

// Use pagination
Blog::paginate(15);
```

### Cache Frequently Accessed Data
```php
$categories = Cache::remember('blog_categories', 3600, function () {
    return BlogCategory::where('status', 'active')->get();
});
```

---

## SEO Checklist

- [ ] Meta title filled (50-60 chars)
- [ ] Meta description filled (150-160 chars)
- [ ] Focus keyword set
- [ ] Featured image uploaded with alt text
- [ ] Canonical URL set (if republishing)
- [ ] OG title and description filled
- [ ] Tags added
- [ ] Content has H2/H3 headings
- [ ] Internal links added
- [ ] Schema markup verified

---

## Testing Checklist

- [ ] Create blog in admin
- [ ] Verify blog appears on `/blogs`
- [ ] Click blog to view detail page
- [ ] Check meta tags in page source
- [ ] Verify schema markup with Google Rich Results Test
- [ ] Test category filtering
- [ ] Test search functionality
- [ ] Test FAQ accordion
- [ ] Test related articles
- [ ] Test scheduled publishing

---

## Deployment Steps

1. **Backup Database**
   ```bash
   mysqldump -u root -p bhaktideep > backup.sql
   ```

2. **Run Migration**
   ```bash
   php artisan migrate --force
   ```

3. **Clear Cache**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

4. **Verify Storage Link**
   ```bash
   php artisan storage:link
   ```

5. **Test Blog Creation**
   - Create test blog
   - Verify on frontend
   - Check SEO meta tags

6. **Monitor Logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

---

## Useful Links

- **Laravel Documentation**: https://laravel.com/docs
- **CKEditor Documentation**: https://ckeditor.com/docs
- **Schema.org**: https://schema.org
- **Google Rich Results Test**: https://search.google.com/test/rich-results
- **Google Search Console**: https://search.google.com/search-console
- **Lighthouse**: https://developers.google.com/web/tools/lighthouse

---

## Support

For issues:
1. Check `storage/logs/laravel.log`
2. Review browser console for JavaScript errors
3. Verify database migration ran successfully
4. Check file permissions on `storage/` directory

---

**Last Updated**: 2024
**Version**: 1.0
**Status**: Production Ready ✅

