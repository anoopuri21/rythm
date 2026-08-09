{{--
    ============================================================
    s11 · Video Showcase — "Feel the music. Live the moment."
    Dark cinematic band with poster + play button → modal video.
    Poster image: AI Generated (hero/banner) — [AI Generated]
    Video source: Pexels (Pixabay, CC0) — config/rythme.php
    ============================================================
--}}
<section id="video-showcase" class="relative overflow-hidden bg-rythme-black text-white" data-reveal="fade">
    <div class="relative flex min-h-[80vh] items-center justify-center overflow-hidden">
        {{-- Poster: AI Generated — [AI Generated] --}}
        <img
            src="{{ asset('images/video-showcase-poster.jpg') }}"
            alt="Golden spotlight on a live guitarist — AI Generated"
            class="absolute inset-0 h-full w-full scale-[1.12] object-cover opacity-70"
            data-parallax="10"
            loading="lazy"
            decoding="async"
        >
        <div class="absolute inset-0 bg-gradient-to-b from-black/75 via-black/30 to-black/90"></div>
        <div class="pointer-events-none absolute left-1/2 top-1/2 h-72 w-72 -translate-x-1/2 -translate-y-1/2 rounded-full bg-gold/15 blur-[100px]"></div>

        <div class="relative z-10 mx-auto max-w-3xl px-5 text-center sm:px-8">
            <p class="section-kicker justify-center text-gold-light" data-reveal="up">Rhythm Exports Sound</p>
            <h2 class="section-title mx-auto text-white" data-reveal="up">
                Feel the music.<br><em class="text-red-gradient">Live the moment.</em>
            </h2>
            <p class="mx-auto mt-6 max-w-xl text-base leading-7 text-white/70 sm:text-lg" data-reveal="up">
                Watch what happens when the right instrument meets the right hands — a short film about sound, craft and the people who chase it.
            </p>

            <div class="mt-10" x-data="{ open: false }" data-reveal="up">
                <button
                    type="button"
                    @click="open = true"
                    class="group inline-flex items-center gap-4 text-left"
                    aria-haspopup="dialog"
                    aria-controls="video-showcase-modal"
                >
                    <span class="relative flex h-20 w-20 items-center justify-center rounded-full bg-gold text-rythme-black shadow-[0_0_50px_rgba(213,8,8,0.45)] transition-transform duration-300 group-hover:scale-110">
                        <svg class="ml-1 h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13l11-6.5-11-6.5z"/></svg>
                    </span>
                    <span class="flex flex-col">
                        <span class="text-sm font-bold uppercase tracking-[0.25em] text-white transition group-hover:text-gold-light">Watch the film</span>
                        <span class="mt-1 text-xs text-white/45">2 min · Behind the sound</span>
                    </span>
                </button>

                {{-- Video modal --}}
                <div
                    id="video-showcase-modal"
                    x-cloak
                    x-show="open"
                    x-transition.opacity.duration.300ms
                    @keydown.escape.window="open = false"
                    class="fixed inset-0 z-[90] flex items-center justify-center bg-black/90 p-4 sm:p-8"
                    role="dialog"
                    aria-modal="true"
                    aria-label="Rhythm Exports film"
                >
                    <button type="button" @click="open = false" class="absolute right-5 top-5 flex h-11 w-11 items-center justify-center rounded-full border border-white/20 text-2xl text-white transition hover:border-gold hover:text-gold" aria-label="Close video">&times;</button>
                    <div class="w-full max-w-4xl" @click.outside="open = false">
                        {{-- Video: Pexels free license (CC0) — https://www.pexels.com/video/man-playing-guitar-854924/ --}}
                        <video
                            controls
                            autoplay
                            playsinline
                            class="aspect-video w-full rounded-xl bg-black shadow-2xl ring-1 ring-white/10"
                            poster="{{ asset('images/video-showcase-poster.jpg') }}"
                        >
                            <source src="{{ config('rythme.video_showcase_url') }}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
