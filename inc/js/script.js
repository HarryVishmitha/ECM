document.addEventListener('DOMContentLoaded', () => {
  const slider      = document.querySelector('.header-slider .slides');
  const slides      = Array.from(slider.children);
  const dots        = document.querySelectorAll('.header-slider .dot');
  const prevBtn     = document.querySelector('.header-slider .prev');
  const nextBtn     = document.querySelector('.header-slider .next');
  let   currentIdx  = 0;
  const totalSlides = slides.length;

  function goTo(idx) {
    slider.style.transform = `translateX(-${idx * 100}%)`;
    dots.forEach((d, i) => d.classList.toggle('active', i === idx));
  }

  prevBtn.addEventListener('click', () => {
    currentIdx = (currentIdx - 1 + totalSlides) % totalSlides;
    goTo(currentIdx);
  });

  nextBtn.addEventListener('click', () => {
    currentIdx = (currentIdx + 1) % totalSlides;
    goTo(currentIdx);
  });

  dots.forEach((dot, i) => {
    dot.addEventListener('click', () => {
      currentIdx = i;
      goTo(i);
    });
  });

  // auto-slide every 5s
  setInterval(() => {
    currentIdx = (currentIdx + 1) % totalSlides;
    goTo(currentIdx);
  }, 5000);
});