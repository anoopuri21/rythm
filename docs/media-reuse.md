# Media reuse — ek image upload karo, kahin bhi use karo

> **M-10** (`docs/media-architecture.md`): ek image jitni baar bhi use ho, disk /
> CDN par **ek hi file** store hoti hai. Pehla upload file ki owner hoti hai;
> baaki har jagah "reuse" (shared row) hoti hai jo usi file par resolve karti hai.
>
> Admin page: **Admin → CONTENT → Media library** (`/admin/media-library`)

## 1. Kab kaam aata hai

| Situation | Pehle | Ab |
|---|---|---|
| Ek image 5 products me | 5 uploads = 5 copies | 1 upload + 4× "Use elsewhere" |
| Gallery image hi social (og) image | dobara upload | khaali chhodo — pehli gallery image use hoti hai |
| Product image hi category icon / homepage block | dobara upload | Media library → Use elsewhere |
| Galti se wahi image dobara upload | 2 copies | `php artisan media:dedupe` merge kar deta hai (1 file) |

## 2. Admin me kaise use karo (2 minute)

1. Image **ek baar** upload karo — jahan natural jagah ho (product gallery, category icon, …).
2. **Admin → CONTENT → Media library**. Yahan har stored image dikhti hai: thumbnail, file name, *Row* (Original/Reused), *Place* (collection), disk, size, **Used in** (kitni jagah use ho rahi hai — is row samet; hover karo to list dikhti hai).
3. Us image ki row me **"Use elsewhere"** dabao → *Use in* (Product / Variant / Category / Brand / Hero slide / Hero banner / Homepage block) → *Item* (search) → *Place* (kaunsi collection) → **Use this image**.
4. Bas. Koi upload nahi hota, koi nayi file nahi banti — wahi URL dono jagah lag jaata hai. Storefront/cache apne aap refresh ho jaate hain.

**Filters:** Place (collection), disk, *Original uploads only / Reused rows only*, aur **"Same file stored more than once"** (duplicates dekhne ke liye).

**Delete** behaviour:
* Reused row delete → sirf wahan se hatti hai, file safe rehti hai.
* Owner row delete → jab tak koi aur jagah use ho rahi hai, file safe rehti hai; **aakhri usage** hatne par file (aur uske conversions) bhi saaf ho jaate hain. Confirm dialog me "Used in: …" dikh jaata hai.

## 3. Server par duplicates clean karna (`media:dedupe`)

Pehle jo duplicates ban chuke hain (same bytes 2+ baar upload), unko merge karne ke liye:

```bash
php artisan media:dedupe --dry-run     # report: kitne groups, kya hoga (kuch badalta nahi)
php artisan media:dedupe               # merge: sabse purani copy owner, baaki usages
php artisan media:dedupe --disk=public # sirf ek disk par
php artisan media:doctor               # verify: duplicates 0, reused rows healthy
```

Kya hota hai:
1. Har file owner ka **sha256 checksum** nikala jaata hai (jo pehle se hai wo reuse hota hai; Cloudinary row ek baar network se padhi jaati hai — isliye warning).
2. Same checksum + same disk wali rows ek group: **sabse purani row owner** rehti hai, baaki copies us file par **re-point** ho jaati hain (unki saari usages ke saath).
3. Uske baad hi extra copy ki files (original + `conversions/` + `responsive-images/`) delete hoti hain — re-point fail hone par file kabhi delete nahi hoti.
4. URL columns (M-7) aur storefront caches apne aap refresh.

Idempotent hai: dobara chalane par "No duplicate images found" aata hai. Interrupt ho jaye to bhi safe hai — dobara chala do.

## 4. Technical rules (kya todna nahi hai)

* Disk ka faisla `App\Support\MediaPathGenerator` karta hai — `shared_path` wali row apne `{id}/` ke bajaye owner ka base path use karti hai (original + `conversions/` + `responsive-images/` sab).
* `App\Observers\MediaFileObserver` (config: `media-library.media_observer`) file ka maalik hai: shared row file delete/rename nahi karti; owner ki file tab tak nahi jaati jab tak koi usage baaki hai; conversion complete hone par saari usages ko mirror hota hai.
* Reused rows **conversions generate nahi karti** (`registerMediaConversions()` me `isShared()` guard) — owner ki conversions hi sabke liye chalti hain.
* `media:relocate` reused rows ko move nahi karta (owner ke saath unka `disk` update ho jaata hai); `media:doctor` unhe misplaced nahi ginta.
* Cloudinary par bhi wahi: ek image = ek asset (ek public_id), chahe 10 jagah use ho.
* Naya reuse path sirf `App\Services\MediaReuseService` se banao (`attach()`), taaki file copy na ho aur observers chalein.

## 5. Troubleshooting

| Symptom | Check |
|---|---|
| "Use elsewhere" list me item nahi mil raha | *Item* search me naam/slug likho (max 50 results, newest first) |
| Reuse karne par storefront purana URL dikha raha | `php artisan media:sync-urls`; cache: `php artisan cache:clear` (observer normally khud flush karta hai) |
| Image kisi ek jagah gayab | `php artisan media:doctor` → "reused image(s) point at a file that is gone" |
| Duplicates wapas ban gaye | Upload karne se pehle Media library me search karo; phir `media:dedupe` |
| `media:dedupe` ek group fail bata raha | Us group ki rows manually Media library se dekho (owner row disk par hai?) — baaki groups merge ho jaate hain |
