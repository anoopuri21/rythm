# 🎵 RYTHME MUSIC STORE - Master Project Plan

## Project Overview
- **Project Name:** Rythme Music Store
- **Type:** E-commerce Website for Musical Instruments
- **Reference:** https://bajaao.com
- **Store Type:** Single Seller Store
- **Store Name:** Rythme

---

## Tech Stack
| Technology | Purpose |
|---|---|
| PHP 8.3+ | Backend Language |
| Laravel 12 | Backend Framework |
| Tailwind CSS v4 | Styling |
| Livewire 3 | Interactive Components |
| Mary UI | Blade Component Library |
| Alpine.js | Lightweight JS (comes with Livewire) |
| Filament v3 | Admin Panel |
| GSAP + ScrollTrigger | Cinematic Scroll Animations |
| Lenis | Smooth Scrolling |
| Swiper.js | Carousels & Sliders |
| CountUp.js | Number Animations |
| Razorpay | Payment Gateway (Test Mode) |
| MySQL 8.0+ | Database |
| Cloudinary | Image & Video Storage |
| Playwright | UI/E2E Testing |

---

## Design System

### Color Palette
| Token | Hex | Usage |
|---|---|---|
| gold-light | #F5D061 | Highlights, Badges |
| gold | #D4A843 | Primary Accent, CTAs |
| gold-dark | #B8860B | Hover States |
| black | #0A0A0A | Dark Sections |
| black-soft | #1A1A1A | Cards on Dark |
| black-muted | #2D2D2D | Borders on Dark |
| red | #C41E3A | Sale Badges, Alerts |
| red-dark | #8B0000 | Hover on Red |
| cream | #FFFDF7 | Page Background |
| cream-dark | #F5F0E8 | Alternate Section Bg |
| warm-white | #FAFAF5 | Card Backgrounds |

### Typography
| Font | Usage | Weights |
|---|---|---|
| Playfair Display | Headings, Titles | 400, 700 |
| Inter | Body Text, UI | 400, 500, 600, 700 |
| Bebas Neue | Numbers, Stats, CTAs | 400 |

### Theme
- Base: Light Premium (cream/white)
- Dark sections: 3-4 sections with black bg
- Parallax: 2 sections (Why Rythme + Numbers)
- Cinematic scroll: Full page scroll-driven reveals

---

## Product Categories (from bajaao.com reference)
1. Guitars (Acoustic, Electric, Bass, Classical, Ukulele)
2. Keyboards & Pianos (Digital Piano, Synthesizer, MIDI Controller, Arranger)
3. Drums & Percussion (Acoustic Drums, Electronic Drums, Cajons, Hand Drums)
4. Pro Audio (Microphones, Audio Interface, Studio Monitors, Mixers, PA Systems)
5. Live Sound (Speakers, Amplifiers, DJ Equipment)
6. Wind Instruments (Harmonicas, Flutes, Saxophone, Trumpet)
7. Indian Instruments (Tabla, Sitar, Harmonium, Tanpura, Dholak)
8. Accessories (Strings, Picks, Cases, Stands, Cables, Tuners)
9. Recording (Headphones, Studio Accessories, Software)
10. Brands (Fender, Yamaha, Gibson, Roland, Casio, etc.)

---

## Home Page Sections (in order)
1. Navbar (fixed, transparent → solid on scroll)
2. Hero Section (Video background OR Image Carousel - admin toggle)
3. Featured Categories (10 category cards grid)
4. Best Sellers / Trending Products (dark section, filter tabs, product cards)
5. Why Rythme (6 USP cards, parallax background)
6. Brand Showcase (logo marquee + featured brand banner)
7. Numbers Section (animated counters, parallax, dark section)
8. New Arrivals (bento grid layout)
9. Deals/Offers Banner (countdown timer, cinematic banner)
10. Latest Stories (3 blog cards)
11. Testimonials/Reviews (swiper carousel, dark section)
12. Footer (newsletter + links + social)

---

## Security Measures (Day 1)
- CSRF protection on all forms
- Server-side validation on all inputs
- Eloquent ORM for SQL injection prevention
- Blade auto-escaping for XSS prevention
- Rate limiting on auth routes (5/minute)
- Email verification mandatory for registration
- Razorpay webhook signature verification
- HTTPS mandatory in production
- Secure session cookies
- Content-Security-Policy headers
- Mass assignment protection via $fillable
- Cloudinary direct upload (files skip server)

---

## Execution Phases
| Phase | Task | Status |
|---|---|---|
| 1 | Project Setup + Master Layout + Navbar | ⬜ |
| 2 | Hero Section (Video + Slider) | ⬜ |
| 3 | Featured Categories Section | ⬜ |
| 4 | Best Sellers Section | ⬜ |
| 5 | Why Rythme Section | ⬜ |
| 6 | Brand Showcase Section | ⬜ |
| 7 | Numbers Section | ⬜ |
| 8 | New Arrivals Section | ⬜ |
| 9 | Deals Banner Section | ⬜ |
| 10 | Latest Stories Section | ⬜ |
| 11 | Testimonials Section | ⬜ |
| 12 | Footer Section | ⬜ |
| 13 | Cinematic Scroll Integration + Polish | ⬜ |
