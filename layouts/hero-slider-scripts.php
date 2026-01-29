<script>
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slide-x');
    const indicators = document.querySelectorAll('.indicator');
    const prevArrow = document.querySelector('.nav-arrow.prev');
    const nextArrow = document.querySelector('.nav-arrow.next');
    const container = document.querySelector('.carousel-container');

    // Update arrow backgrounds with next/prev slide images
    function updateArrowBackgrounds() {
      const totalSlides = slides.length;
      const prevIndex = (currentSlide - 1 + totalSlides) % totalSlides;
      const nextIndex = (currentSlide + 1) % totalSlides;
      
      const prevBg = slides[prevIndex].style.backgroundImage;
      const nextBg = slides[nextIndex].style.backgroundImage;
      
      prevArrow.style.setProperty('--bg-image', prevBg);
      nextArrow.style.setProperty('--bg-image', nextBg);
      
      prevArrow.querySelector('::before') || (prevArrow.style.backgroundImage = prevBg);
      nextArrow.querySelector('::before') || (nextArrow.style.backgroundImage = nextBg);
      
      // Set the before pseudo-element background
      const style = document.createElement('style');
      style.textContent = `
        .nav-arrow.prev::before { background-image: ${prevBg}; }
        .nav-arrow.next::before { background-image: ${nextBg}; }
      `;
      document.head.appendChild(style);
    }

    function changeSlide(direction) {
      const oldSlide = currentSlide;
      currentSlide = (currentSlide + direction + slides.length) % slides.length;
      
      // Create wipe overlay - wipes from right to left when going forward, left to right when going back
      const wipe = document.createElement('div');
      wipe.className = 'wipe-overlay ' + (direction > 0 ? 'wipe-left' : 'wipe-right');
      container.appendChild(wipe);
      
      // Mark old slide as exiting
      slides[oldSlide].classList.add('exiting');
      
      // Switch slides at the midpoint of wipe
      setTimeout(() => {
        slides[oldSlide].classList.remove('active', 'exiting');
        slides[currentSlide].classList.add('active');
        indicators[oldSlide].classList.remove('active');
        indicators[currentSlide].classList.add('active');
        updateArrowBackgrounds();
      }, 400);
      
      // Remove wipe overlay after animation
      setTimeout(() => {
        wipe.remove();
      }, 800);
    }

    function goToSlide(index) {
      if (index === currentSlide) return;
      const direction = index > currentSlide ? 1 : -1;
      currentSlide = index - direction;
      changeSlide(direction);
    }

    // Auto-play
    let autoPlayInterval = setInterval(() => changeSlide(1), 6000);

    // Pause auto-play on hover
    container.addEventListener('mouseenter', () => {
      clearInterval(autoPlayInterval);
    });

    container.addEventListener('mouseleave', () => {
      autoPlayInterval = setInterval(() => changeSlide(1), 6000);
    });

    // Initialize arrow backgrounds
    updateArrowBackgrounds();

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowLeft') changeSlide(-1);
      if (e.key === 'ArrowRight') changeSlide(1);
    });
  </script>