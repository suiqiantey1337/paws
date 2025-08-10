<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paws & Preferences</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://hammerjs.github.io/dist/hammer.min.js"></script>
    <style>
        .card-container {
            perspective: 1000px;
            height: 24rem;
            position: relative;
        }
        .card {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            transform-origin: center;
            touch-action: none;
            user-select: none;
            backface-visibility: hidden;
            will-change: transform;
        }
        .card img {
            pointer-events: none;
            -webkit-user-drag: none;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .action-btn {
            transition: all 0.2s;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .action-btn:active {
            transform: scale(0.95);
        }
        .swipe-out-right {
            animation: swipeOutRight 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        .swipe-out-left {
            animation: swipeOutLeft 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        .fade-in {
            animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        @keyframes swipeOutRight {
            to { transform: translateX(300px) rotate(30deg); opacity: 0; }
        }
        @keyframes swipeOutLeft {
            to { transform: translateX(-300px) rotate(-30deg); opacity: 0; }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .slideshow-container {
            height: 20rem;
        }
        .slide {
            transition: transform 0.5s ease;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div
        x-data="catSwipe()"
        x-init="init()"
        class="relative w-full max-w-sm"
    >
        <!-- Loading State -->
        <template x-if="loading">
            <div class="flex items-center justify-center h-96">
                <p class="text-xl font-semibold">Loading cute cats...</p>
            </div>
        </template>

        <!-- Card Container (only shown when not in summary) -->
        <template x-if="!showSummary">
            <div class="card-container" x-show="!loading">
                <!-- Current Card -->
                <div
                    x-show="currentIndex < cats.length"
                    x-transition.opacity.duration.400ms
                    :class="{
                        'swipe-out-right': isSwipingRight,
                        'swipe-out-left': isSwipingLeft
                    }"
                    class="card bg-white shadow-lg rounded-2xl overflow-hidden"
                    :style="{
                        transform: showDragEffect
                            ? `translateX(${offsetX}px) rotate(${rotation}deg)`
                            : 'none',
                        transition: showDragEffect ? 'none' : 'transform 0.4s cubic-bezier(0.4, 0, 0.2, 1)',
                        zIndex: 20
                    }"
                >
                    <img
                        :src="cats[currentIndex]?.url"
                        draggable="false"
                    >
                </div>

                <!-- Next Card (preloaded but hidden) -->
                <div
                    x-show="currentIndex + 1 < cats.length"
                    x-transition.opacity.duration.400ms
                    class="card bg-white shadow-lg rounded-2xl overflow-hidden"
                    style="z-index: 10; opacity: 0;"
                >
                    <img
                        :src="cats[currentIndex + 1]?.url"
                        draggable="false"
                    >
                </div>
            </div>
        </template>

        <!-- Action Buttons -->
        <div class="flex justify-center gap-8 my-6" x-show="!loading && currentIndex < cats.length">
            <button
                @click="triggerSwipe(false)"
                class="action-btn bg-red-500 text-white p-5 rounded-full hover:bg-red-600"
            >
                <span class="text-3xl">👎</span>
            </button>
            <button
                @click="triggerSwipe(true)"
                class="action-btn bg-green-500 text-white p-5 rounded-full hover:bg-green-600"
            >
                <span class="text-3xl">👍</span>
            </button>
        </div>

        <!-- Progress Indicator -->
        <div class="flex justify-center gap-1 mb-6" x-show="!loading && currentIndex < cats.length">
            <template x-for="(_, index) in cats.length" :key="index">
                <div
                    class="h-2 w-2 rounded-full"
                    :class="{
                        'bg-gray-400': index > currentIndex,
                        'bg-green-500': index < currentIndex,
                        'bg-indigo-500': index === currentIndex,
                        'w-4': index === currentIndex
                    }"
                ></div>
            </template>
        </div>

        <!-- Summary Screen -->
        <template x-if="showSummary">
            <div class="bg-white rounded-2xl shadow-lg p-6 flex flex-col w-full">
                <h2 class="text-2xl font-bold mb-6 text-center">You liked <span x-text="likes.length"></span> cats!</h2>

                <!-- Slideshow Container -->
                <div class="slideshow-container relative mb-6" x-data="{ currentSlide: 0 }">
                    <!-- Slides -->
                    <div class="flex overflow-hidden h-full">
                        <template x-for="(cat, index) in likes" :key="cat.id">
                            <div
                                class="w-full h-full flex-shrink-0 slide"
                                :style="`transform: translateX(-${currentSlide * 100}%)`"
                            >
                                <img
                                    :src="cat.url"
                                    class="w-full h-full object-cover rounded-lg"
                                    draggable="false"
                                >
                            </div>
                        </template>
                    </div>

                    <!-- Navigation Arrows -->
                    <template x-if="likes.length > 1">
                        <div class="absolute inset-0 flex items-center justify-between px-2">
                            <button
                                @click="currentSlide = Math.max(0, currentSlide - 1)"
                                class="bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-70 transition"
                                :class="{ 'invisible': currentSlide === 0 }"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <button
                                @click="currentSlide = Math.min(likes.length - 1, currentSlide + 1)"
                                class="bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-70 transition"
                                :class="{ 'invisible': currentSlide === likes.length - 1 }"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </template>

                    <!-- Slide Indicators -->
                    <div class="flex justify-center mt-2 space-x-2" x-show="likes.length > 1">
                        <template x-for="(_, index) in likes.length" :key="index">
                            <button
                                @click="currentSlide = index"
                                class="w-2 h-2 rounded-full transition-colors"
                                :class="{
                                    'bg-indigo-500 w-4': currentSlide === index,
                                    'bg-gray-300': currentSlide !== index
                                }"
                            ></button>
                        </template>
                    </div>
                </div>

                <button
                    @click="reset()"
                    class="bg-indigo-500 text-white py-3 px-6 rounded-lg hover:bg-indigo-600 transition mt-auto"
                >
                    Try Again
                </button>
            </div>
        </template>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('catSwipe', () => ({
                cats: [],
                currentIndex: 0,
                likes: [],
                offsetX: 0,
                rotation: 0,
                dragging: false,
                loading: true,
                isAnimating: false,
                isSwipingRight: false,
                isSwipingLeft: false,
                showDragEffect: false,

                init() {
                    this.$root.addEventListener('contextmenu', (e) => {
                        e.preventDefault();
                    });

                    this.fetchCats();
                    this.setupSwipeListener();
                },

                fetchCats() {
                    this.cats = Array.from({ length: 15 }, (_, i) => ({
                        id: `cat-${Date.now()}-${i}`,
                        url: `https://cataas.com/cat?width=300&height=400&cache=${Math.random()}`
                    }));
                    this.loading = false;
                },

                setupSwipeListener() {
                    const container = this.$root;
                    const mc = new Hammer.Manager(container, {
                        recognizers: [
                            [Hammer.Pan, { direction: Hammer.DIRECTION_HORIZONTAL, threshold: 5 }]
                        ]
                    });

                    mc.on('hammer.input', (ev) => {
                        if (ev.isFirst) {
                            ev.preventDefault();
                        }
                    });

                    mc.on('panstart', () => {
                        if (!this.isAnimating) {
                            this.dragging = true;
                            this.showDragEffect = true;
                        }
                    });

                    mc.on('panmove', (ev) => {
                        if (!this.isAnimating && this.dragging) {
                            const resistance = 0.5;
                            this.offsetX = ev.deltaX * resistance;
                            this.rotation = (ev.deltaX / 5) * resistance;
                        }
                    });

                    mc.on('panend', (ev) => {
                        if (!this.isAnimating && this.dragging) {
                            this.dragging = false;
                            this.showDragEffect = false;

                            if (Math.abs(ev.deltaX) > 50 || Math.abs(ev.velocityX) > 0.3) {
                                this.triggerSwipe(ev.deltaX > 0);
                            } else {
                                this.resetCardPosition();
                            }
                        }
                    });
                },

                async triggerSwipe(liked) {
                    if (this.isAnimating || this.currentIndex >= this.cats.length) return;

                    this.isAnimating = true;
                    if (liked) {
                        this.isSwipingRight = true;
                        this.likes.push(this.cats[this.currentIndex]);
                    } else {
                        this.isSwipingLeft = true;
                    }

                    await new Promise(resolve => setTimeout(resolve, 400));

                    this.isSwipingRight = false;
                    this.isSwipingLeft = false;
                    this.currentIndex++;
                    this.resetCardPosition();
                    this.isAnimating = false;
                },

                resetCardPosition() {
                    this.offsetX = 0;
                    this.rotation = 0;
                },

                reset() {
                    this.currentIndex = 0;
                    this.likes = [];
                    this.fetchCats();
                },

                get showSummary() {
                    return !this.loading && this.currentIndex >= this.cats.length;
                }
            }));
        });
    </script>
</body>
</html>
