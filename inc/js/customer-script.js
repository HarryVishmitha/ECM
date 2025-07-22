const toggleBtn = document.getElementById("userNavToggle");
const navMenu = document.getElementById("userNavMenu");
const navOverlay = document.getElementById("userNavOverlay");

toggleBtn.addEventListener("click", () => {
  navMenu.classList.toggle("open");
  navOverlay.classList.toggle("active");
});

navOverlay.addEventListener("click", () => {
  navMenu.classList.remove("open");
  navOverlay.classList.remove("active");
});
