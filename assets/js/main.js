// main.js — бургер/дроуэр для шапки
// main.js — бургер/дроуэр

document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".faq-toggle").forEach(function (btn) {
    btn.addEventListener("click", function () {
      const wrapper = btn.closest(".faq-item").querySelector(".faq-answer");
      wrapper.classList.toggle("truncated");
      btn.textContent = wrapper.classList.contains("truncated")
        ? "▼"
        : "Свернуть";
    });
  });
});
