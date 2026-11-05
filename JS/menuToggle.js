
document.addEventListener("DOMContentLoaded", () => {
    const boton = document.getElementById('menuToggleBtn');
    const menu = document.querySelector(".menu")

    boton.addEventListener("click", function () {
        menu.classList.toggle("active")
    })
})
