<style>
    /* Hide scrollbar for Chrome, Safari and Opera */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    /* Hide scrollbar for IE, Edge and Firefox */
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    /* Line clamp utility */
    .line-clamp-1 {
        display: -webkit-box;
        -webkit-line-clamp: 1;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Full Width Hero Slider Styles */
    .hero-slider {
        position: relative;
        width: 100%;
        height: 100vh;
        min-height: 600px;
        max-height: 900px;
    }

    .hero-slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 1s ease-in-out;
    }

    .hero-slide.active {
        opacity: 1;
    }

    .slide-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }

    .slide-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .hero-content {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 5;
        display: flex;
        align-items: center;
    }

    .hero-nav-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        border: none;
        padding: 16px;
        border-radius: 50%;
        cursor: pointer;
        z-index: 10;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .hero-nav-btn:hover {
        background: rgba(255, 255, 255, 1);
        transform: translateY(-50%) scale(1.1);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
    }

    .prev-btn {
        left: 24px;
    }

    .next-btn {
        right: 24px;
    }

    .hero-dots {
        position: absolute;
        bottom: 140px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 12px;
        z-index: 10;
    }

    .hero-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .hero-dot.active {
        background: white;
        width: 32px;
        border-radius: 6px;
        border-color: rgba(255, 255, 255, 0.3);
    }

    .hero-dot:hover {
        background: rgba(255, 255, 255, 0.8);
        transform: scale(1.2);
    }

    /* Countdown Styles - More Professional */
    .countdown-item {
        text-align: center;
    }

    .countdown-box {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 1rem;
        padding: 1.5rem 1rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .countdown-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .countdown-number {
        font-size: 2.5rem;
        font-weight: 800;
        background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        display: block;
        line-height: 1;
    }

    .countdown-label {
        color: #6b7280;
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Action Card Styles - More Refined */
    .action-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 1rem;
        padding: 1.5rem 1rem;
        text-align: center;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
    }

    .action-card:hover {
        border-color: #1f2937;
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    }

    .action-icon {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .action-card:hover .action-icon {
        transform: scale(1.05);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }

    .action-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: #1f2937;
        line-height: 1.2;
    }

    .action-desc {
        font-size: 0.75rem;
        color: #6b7280;
        line-height: 1.3;
    }

    /* Race Pack Slider Styles */
    .racepack-slider-wrapper {
        position: relative;
    }

    .racepack-slide {
        padding: 0 4px;
    }

    .racepack-nav-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        border: none;
        padding: 16px;
        border-radius: 50%;
        cursor: pointer;
        z-index: 10;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .racepack-nav-btn:hover {
        background: white;
        transform: translateY(-50%) scale(1.1);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    }

    .racepack-prev-btn {
        left: -20px;
    }

    .racepack-next-btn {
        right: -20px;
    }

    .racepack-dots {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin-top: 24px;
    }

    .racepack-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #cbd5e1;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .racepack-dot.active {
        background: #1f2937;
        width: 32px;
        border-radius: 6px;
    }

    .racepack-dot:hover {
        background: #94a3b8;
        transform: scale(1.2);
    }

    /* Course Map Styles */
    .category-tab {
        padding: 12px 20px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 14px;
        color: #4b5563;
    }

    .category-tab:hover {
        border-color: #1f2937;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .category-tab.active {
        background: #1f2937;
        border-color: #1f2937;
        color: white;
        box-shadow: 0 4px 16px rgba(31, 41, 55, 0.2);
    }

    .category-tab.active .w-8 {
        background: rgba(255, 255, 255, 0.2) !important;
    }

    .map-category {
        display: none;
        animation: fadeIn 0.5s ease-in-out;
    }

    .map-category.active {
        display: block;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .hero-slider {
            height: auto;
            min-height: unset;
            max-height: unset;
            aspect-ratio: 16 / 9;
        }

        .slide-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-nav-btn {
            padding: 12px;
        }

        .prev-btn {
            left: 16px;
        }

        .next-btn {
            right: 16px;
        }

        .hero-dots {
            bottom: 120px;
        }

        .hero-dot {
            width: 10px;
            height: 10px;
        }

        .hero-dot.active {
            width: 24px;
        }

        .countdown-number {
            font-size: 2rem;
        }

        .countdown-box {
            padding: 1rem 0.75rem;
        }

        .action-icon {
            width: 3rem;
            height: 3rem;
        }

        .racepack-prev-btn {
            left: 8px;
        }

        .racepack-next-btn {
            right: 8px;
        }

        .racepack-nav-btn {
            padding: 12px;
        }
    }

    /* Prose styling */
    .prose p {
        margin-bottom: 1rem;
    }

    .prose p:last-child {
        margin-bottom: 0;
    }

    /* NEW: About Section Grid Layout */
    .about-grid {
        display: grid;
        gap: 2rem;
        align-items: start;
    }

    @media (min-width: 1024px) {
        .about-grid {
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
        }
    }

    .about-image-container {
        position: relative;
        overflow: hidden;
        border-radius: 1rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .about-image-container img {
        width: 100%;
        height: auto;
        display: block;
        transition: transform 0.5s ease;
    }

    .about-image-container:hover img {
        transform: scale(1.02);
    }

    .about-content {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .about-text {
        font-size: 1rem;
        line-height: 1.75;
        color: #4b5563;
    }

    /* Benefits List */
    .benefits-list {
        display: grid;
        gap: 0.75rem;
        margin-top: 2rem;
    }

    .benefit-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.875rem 1rem;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        font-size: 0.9375rem;
        font-weight: 500;
        color: #374151;
        transition: all 0.2s ease;
    }

    .benefit-item:hover {
        border-color: #1f2937;
        transform: translateX(4px);
    }

    .benefit-icon {
        width: 1.25rem;
        height: 1.25rem;
        color: #1f2937;
        flex-shrink: 0;
    }
</style>
