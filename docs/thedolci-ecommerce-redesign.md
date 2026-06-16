# thedolci eCommerce Redesign Blueprint

## 1) Sitemap Structure

### Public Pages
- `/` Home
- `/shop` Product listing (all tiramisu)
- `/shop/classic-tiramisu` Product detail (dynamic slug)
- `/collections/seasonal` Seasonal and limited editions
- `/about` Brand story and quality commitment
- `/reviews` Customer reviews and ratings
- `/loyalty` Rewards program
- `/cart` Shopping cart
- `/checkout` Checkout (shipping, pickup, payment)
- `/order/success` Confirmation page
- `/track-order` Order tracking
- `/faq` FAQ
- `/contact` Contact and support
- `/policy/privacy` Privacy policy
- `/policy/terms` Terms and conditions

### Conversion Utility Endpoints (UI or modal driven)
- `/api/coupons/validate`
- `/api/delivery-slots`
- `/api/instagram-feed`
- `/api/reviews/featured`
- `/subscribe` Email capture

### Customer Account
- `/account` Dashboard
- `/account/orders` Order history
- `/account/rewards` Points and rewards balance
- `/account/addresses` Saved addresses
- `/account/profile` Profile settings

### Admin Panel
- `/admin` Dashboard (sales, conversion, AOV, repeat rate)
- `/admin/products` Product management
- `/admin/products/seasonal` Seasonal inventory and release dates
- `/admin/orders` Order management
- `/admin/inventory` Inventory tracking
- `/admin/coupons` Coupon management
- `/admin/reviews` Review moderation
- `/admin/customers` Customer and loyalty tiers
- `/admin/analytics` Sales and funnel analytics

## 2) Wireframe Layout

### Home (Mobile-first)
1. Sticky top nav:
   - Logo `thedolci`
   - `Shop`, `Seasonal`, `About`
   - Cart icon with count
2. Hero:
   - Full-width luxury dessert image
   - Headline: `Authentic Italian Tiramisu, Made Fresh Daily`
   - Primary CTA: `Order Now`
   - Secondary CTA: `Explore Flavors`
3. Seasonal highlight card:
   - Limited badge
   - Countdown timer
   - `Pre-order Now`
4. Best sellers carousel:
   - Product card with image, flavor, price, quick add
5. Trust row:
   - `Fresh daily`, `Premium mascarpone`, `Same-day delivery`, `Secure checkout`
6. Review preview:
   - Star summary + 3 short testimonials
7. Loyalty + first-order offer block:
   - Popup trigger and email capture CTA
8. Instagram feed strip:
   - 6 latest posts
9. Footer:
   - Policies, contact, social, newsletter

### Product Detail
1. Breadcrumb
2. Image gallery (zoom + thumbnails)
3. Product info column:
   - Flavor title
   - Rating and review count
   - Short description
   - Price by size
4. Configuration module:
   - Size selector: `Small`, `Medium`, `Large`, `Family Box`
   - Sugar level selector
   - Checkbox: add candles
   - Input: name on box
   - Textarea: gift message
5. Delivery options:
   - Pickup or delivery
   - Date and time scheduling
6. Actions:
   - Quantity stepper
   - `Add to Cart`
   - `Buy Now`
7. Product reassurance:
   - Ingredients, allergens, freshness guarantee
8. Related products and seasonal upsell

### Checkout
1. Contact info
2. Delivery or pickup selection
3. Delivery time slot picker
4. Address and notes
5. Coupon field
6. Payment methods:
   - Card
   - Apple Pay
   - Cash on Delivery
7. Order summary
8. Place order CTA

## 3) Design Concept

### Creative Direction
- Premium Italian patisserie aesthetic with emotional, food-first storytelling.
- Minimal composition with generous spacing and intentional hierarchy.
- Warm neutrals and cocoa tones to increase appetite and trust.

### Visual Language
- Full-bleed hero photography with soft depth and warm highlights.
- Rounded cards, subtle borders, soft shadows, no harsh UI chrome.
- Elegant motion:
  - Fade-in stagger for product cards
  - Countdown pulse for seasonal SKU
  - Smooth sticky CTA transitions on mobile

### Conversion Psychology Plan
- Above-the-fold single dominant CTA: `Order Now`.
- Scarcity triggers:
  - Limited quantity badge
  - Real countdown for seasonal item
  - Pre-order urgency copy
- Trust boosters:
  - Ratings visible early
  - Secure checkout badges
  - Freshness and quality guarantees near CTA
- Friction reduction:
  - Quick add from listing cards
  - Persistent cart indicator
  - One-page checkout with express payment
- AOV growth:
  - Size upsell defaults
  - Candles add-on
  - Seasonal bundle suggestions in cart

## 4) Suggested Tech Stack

### Core Platform (best fit for current repo)
- Backend: Laravel 12
- Frontend: Blade + Tailwind CSS v4
- Interactivity: Alpine.js (lightweight for modals, countdowns, selectors)
- Cart/Checkout state: Laravel session cart + database order persistence

### Commerce and Payments
- Payments: Stripe (Cards + Apple Pay), COD as offline payment option
- Coupons: custom Laravel coupon engine (or package-backed)
- Delivery slot scheduling: custom availability table + admin slot manager

### Reviews, Loyalty, and Email
- Reviews: product review tables with verified-order flag
- Loyalty: points ledger per order and redeem rules
- Email: Laravel notifications + queue (welcome, order, loyalty updates)
- Newsletter: Mailchimp/Klaviyo integration endpoint

### Admin and Analytics
- Admin UI: Blade + Tailwind (reuse existing admin auth/middleware)
- Analytics:
  - Internal KPIs in `/admin/analytics`
  - GA4 + Meta pixel for conversion funnel tracking

### Performance + SEO
- Image optimization: AVIF/WebP + lazy loading
- Caching: full-page/fragment caching for catalog pages
- SEO:
  - Product schema
  - OpenGraph
  - XML sitemap
  - Canonical URLs

## 5) UI Style Guide

### Color Tokens
- `--color-cream-50: #f8f3ec`
- `--color-beige-100: #efe4d6`
- `--color-cocoa-600: #6b3f2b`
- `--color-espresso-800: #3d2418`
- `--color-espresso-900: #28170f`
- `--color-gold-accent: #c7a66a`
- `--color-success: #2f7a4a`
- `--color-error: #a63a2f`

### Typography
- Headings (luxury serif): `Cormorant Garamond`, `Playfair Display`, serif
- Body/UI (clean sans): `Manrope`, `Nunito Sans`, sans-serif
- Scale:
  - H1: 42/48 desktop, 32/38 mobile
  - H2: 32/38 desktop, 26/32 mobile
  - Body: 16/26
  - Caption: 13/18

### Spacing + Shape
- Layout grid: 12 columns desktop, 4 columns mobile
- Container width: 1200px max
- Spacing system: 4, 8, 12, 16, 24, 32, 48, 64
- Radius:
  - Cards: 16px
  - Inputs/buttons: 12px
  - Pills/badges: 999px

### Components
- Primary button:
  - Espresso background, cream text
  - Hover: slightly lighter espresso
  - Disabled: beige tone
- Secondary button:
  - Transparent, cocoa border, cocoa text
- Product card:
  - Image 4:5 ratio
  - Flavor, size-from price, rating, quick add
- Input fields:
  - Warm light background
  - 1px cocoa border
  - Clear error state and helper text

### Motion
- Duration: 180ms to 280ms
- Easing: `cubic-bezier(0.22, 1, 0.36, 1)`
- Use only purposeful transitions:
  - CTA hover lift
  - Card reveal
  - Modal entrance

## 6) Build Phases (Execution Sequence)

1. Foundation:
   - New brand tokens, typography, layout shell, navigation, footer.
2. Commerce MVP:
   - Product listing/detail, cart, checkout, payments, delivery scheduling.
3. Conversion Layer:
   - Seasonal countdown, limited stock badges, first-order popup, reviews.
4. Retention Layer:
   - Loyalty dashboard, email automations, reorder flows.
5. Admin + Analytics:
   - Product/order/inventory management and conversion reporting.

## 7) Success Metrics (90-day targets)
- Conversion rate: +30%
- Average order value: +18%
- Cart abandonment: -20%
- Repeat purchase rate: +15%
- Mobile checkout completion: +25%
