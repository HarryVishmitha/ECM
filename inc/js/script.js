// Navbar
document.addEventListener("DOMContentLoaded", function () {
  const navToggle = document.querySelector(".nav-toggle");
  const topbar = document.querySelector(".topbar");
  const navOverlay = document.querySelector(".nav-overlay");

  function toggleMenu() {
    topbar.classList.toggle("open");
    navOverlay.classList.toggle("active");

    const expanded =
      navToggle.getAttribute("aria-expanded") === "true" || false;
    navToggle.setAttribute("aria-expanded", !expanded);
  }

  navToggle.addEventListener("click", toggleMenu);

  navOverlay.addEventListener("click", () => {
    topbar.classList.remove("open");
    navOverlay.classList.remove("active");
    navToggle.setAttribute("aria-expanded", false);
  });
});

// Nav bar sticky positioning
document.addEventListener("DOMContentLoaded", () => {
  const navbar = document.querySelector(".topNav-wrapper");
  const hero = document.querySelector(".hero");

  const observer = new IntersectionObserver(
    ([entry]) => {
      if (!entry.isIntersecting) {
        navbar.classList.add("sticky");
      } else {
        navbar.classList.remove("sticky");
      }
    },
    {
      root: null,
      threshold: 0,
      rootMargin: "-1px",
    }
  );

  observer.observe(hero);
});

// slide show
let slideIndex = 0;
showSlides(slideIndex);
showSlidesAuto();

// Next/previous controls
function plusSlides(n) {
  showSlides((slideIndex += n));
}

// Thumbnail image controls
function currentSlide(n) {
  showSlides((slideIndex = n));
}

function showSlides(n) {
  let i;
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");
  if (n > slides.length) {
    slideIndex = 1;
  }
  if (n < 1) {
    slideIndex = slides.length;
  }
  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";
  }
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }
  slides[slideIndex - 1].style.display = "block";
  dots[slideIndex - 1].className += " active";
}

function showSlidesAuto() {
  let i;
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");
  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";
  }
  slideIndex++;
  if (slideIndex > slides.length) {
    slideIndex = 1;
  }
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }
  slides[slideIndex - 1].style.display = "block";
  dots[slideIndex - 1].className += " active";
  setTimeout(showSlidesAuto, 4000); // Change image every 4 seconds
}

// quick view modal
function openQuickView(title, price, image, description) {
  document.getElementById("quickViewTitle").innerText = title;
  document.getElementById("quickViewPrice").innerText = price;
  document.getElementById("quickViewImg").src = image;
  document.getElementById("quickViewDescription").innerText = description;
  document.getElementById("quickViewModal").style.display = "flex";
}

function closeQuickView() {
  document.getElementById("quickViewModal").style.display = "none";
}

function openSizeGuide(event) {
  event.preventDefault();
  const modal = document.getElementById("prodSizeGuideModal");
  if (modal) modal.classList.add("active");
}

function closeSizeGuide() {
  const modal = document.getElementById("prodSizeGuideModal");
  if (modal) modal.classList.remove("active");
}

function adjustQty(delta) {
  const input = document.getElementById("qty");
  let value = parseInt(input.value);
  if (!isNaN(value)) {
    input.value = Math.max(1, value + delta);
  }
}

function closeSizeGuide() {
  const modal = document.getElementById("prodSizeGuideModal");
  modal.classList.remove("active");
  if (window.innerWidth < 768) {
    document.querySelector(".prod-form").scrollIntoView({ behavior: "smooth" });
  }
}

function changeMainImage(thumb) {
    const mainImage = document.getElementById('mainProductImage');
    mainImage.src = thumb.src;
    mainImage.alt = thumb.alt;
}
