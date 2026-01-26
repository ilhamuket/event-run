<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hero Slider
        const slides = document.querySelectorAll('.hero-slide');
        const dots = document.querySelectorAll('.hero-dot');
        const prevBtn = document.querySelector('.prev-btn');
        const nextBtn = document.querySelector('.next-btn');

        if (slides.length > 0) {
            let currentSlide = 0;
            let autoSlideInterval;

            function showSlide(index) {
                slides.forEach(slide => slide.classList.remove('active'));
                dots.forEach(dot => dot.classList.remove('active'));

                if (index >= slides.length) {
                    currentSlide = 0;
                } else if (index < 0) {
                    currentSlide = slides.length - 1;
                } else {
                    currentSlide = index;
                }

                slides[currentSlide].classList.add('active');
                if (dots.length > 0) {
                    dots[currentSlide].classList.add('active');
                }
            }

            function nextSlide() {
                showSlide(currentSlide + 1);
            }

            function prevSlide() {
                showSlide(currentSlide - 1);
            }

            function startAutoSlide() {
                autoSlideInterval = setInterval(nextSlide, 5000);
            }

            function stopAutoSlide() {
                clearInterval(autoSlideInterval);
            }

            // Event listeners
            if (prevBtn && nextBtn) {
                nextBtn.addEventListener('click', function() {
                    nextSlide();
                    stopAutoSlide();
                    startAutoSlide();
                });

                prevBtn.addEventListener('click', function() {
                    prevSlide();
                    stopAutoSlide();
                    startAutoSlide();
                });
            }

            dots.forEach((dot, index) => {
                dot.addEventListener('click', function() {
                    showSlide(index);
                    stopAutoSlide();
                    startAutoSlide();
                });
            });

            // Pause auto-slide on hover
            const heroSlider = document.querySelector('.hero-slider');
            if (heroSlider) {
                heroSlider.addEventListener('mouseenter', stopAutoSlide);
                heroSlider.addEventListener('mouseleave', startAutoSlide);
            }

            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowLeft') {
                    prevSlide();
                    stopAutoSlide();
                    startAutoSlide();
                } else if (e.key === 'ArrowRight') {
                    nextSlide();
                    stopAutoSlide();
                    startAutoSlide();
                }
            });

            // Start auto-slide only if there are multiple slides
            if (slides.length > 1) {
                startAutoSlide();
            }
        }

        // Countdown Timer
        const eventDate = new Date('{{ $event->start_time }}').getTime();

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = eventDate - now;

            if (distance < 0) {
                document.getElementById('days').textContent = '00';
                document.getElementById('hours').textContent = '00';
                document.getElementById('minutes').textContent = '00';
                document.getElementById('seconds').textContent = '00';
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById('days').textContent = String(days).padStart(2, '0');
            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
        }

        // Update countdown every second
        updateCountdown();
        setInterval(updateCountdown, 1000);

        // Race Pack Slider
        const racepackSlider = document.querySelector('.racepack-slider');
        const racepackSlides = document.querySelectorAll('.racepack-slide');
        const racepackDots = document.querySelectorAll('.racepack-dot');
        const racepackPrevBtn = document.querySelector('.racepack-prev-btn');
        const racepackNextBtn = document.querySelector('.racepack-next-btn');

        if (racepackSlider && racepackSlides.length > 0) {
            let currentRacepackSlide = 0;

            function showRacepackSlide(index) {
                if (index >= racepackSlides.length) {
                    currentRacepackSlide = 0;
                } else if (index < 0) {
                    currentRacepackSlide = racepackSlides.length - 1;
                } else {
                    currentRacepackSlide = index;
                }

                racepackSlider.style.transform = `translateX(-${currentRacepackSlide * 100}%)`;

                racepackDots.forEach(dot => dot.classList.remove('active'));
                if (racepackDots[currentRacepackSlide]) {
                    racepackDots[currentRacepackSlide].classList.add('active');
                }
            }

            if (racepackPrevBtn && racepackNextBtn) {
                racepackPrevBtn.addEventListener('click', () => {
                    showRacepackSlide(currentRacepackSlide - 1);
                });

                racepackNextBtn.addEventListener('click', () => {
                    showRacepackSlide(currentRacepackSlide + 1);
                });
            }

            racepackDots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    showRacepackSlide(index);
                });
            });

            // Auto slide for race pack (only if there are multiple items)
            if (racepackSlides.length > 1) {
                setInterval(() => {
                    showRacepackSlide(currentRacepackSlide + 1);
                }, 5000);
            }
        }

        // Course Map Category Tabs
        const categoryTabs = document.querySelectorAll('.category-tab');
        const mapCategories = document.querySelectorAll('.map-category');

        categoryTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const category = this.getAttribute('data-category');

                // Remove active class from all tabs and categories
                categoryTabs.forEach(t => t.classList.remove('active'));
                mapCategories.forEach(m => m.classList.remove('active'));

                // Add active class to clicked tab and corresponding category
                this.classList.add('active');
                const targetCategory = document.querySelector(`.map-category[data-category="${category}"]`);
                if (targetCategory) {
                    targetCategory.classList.add('active');
                }
            });
        });
    });
</script>
