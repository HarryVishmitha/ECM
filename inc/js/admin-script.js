function toggleDropdown() {
  const menu = document.getElementById("adminDropdown");
  menu.classList.toggle("show");
}

window.addEventListener("click", function (e) {
  const toggle = document.querySelector(".dropdown-toggle");
  if (!toggle.contains(e.target)) {
    document.getElementById("adminDropdown").classList.remove("show");
  }
});

function openModal(id = "", name = "", status = "active") {
  document.getElementById("modalTitle").innerText = id
    ? "Edit Category"
    : "Add Category";
  document.getElementById("catId").value = id;
  document.getElementById("catName").value = name;
  document.getElementById("catStatus").value = status;
  document.getElementById("categoryModal").classList.add("show");
}

function closeModal() {
  document.getElementById("categoryModal").classList.remove("show");
}
