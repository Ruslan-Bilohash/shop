## Shop CMS v1.7.3

### Product reviews (storefront)
- Google-style star ratings on product pages — aggregate score, review list, submit form
- reCAPTCHA v2 on `api/product-review.php` (spam protection)
- `AggregateRating` in Schema.org Product from live MySQL reviews
- Demo seed reviews for showcase products

### Homepage
- About block rewritten — production demo runs on **MySQL** (catalog, orders, reviews)
- Use-case links: fashion, electronics, home, sports, beauty, food, B2B, marketplace

### Database
- New table `{prefix}product_reviews` — auto-created on first use

### Packages
- `shop-install-v1.7.3.zip` — MySQL commercial package
- `shop-not-mysql-v1.7.3.zip` — JSON storage edition