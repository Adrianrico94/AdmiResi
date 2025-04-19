// Variable global para almacenar el tema seleccionado temporalmente
let selectedTheme = null;
//Los valores se guardan en el caché
let currentTheme = localStorage.getItem("theme") || "theme-default";

// Aplica el cambio de color temporal
function previewTheme(themeName) {
  selectedTheme = themeName;

  // Aplicar el tema temporalmente
  document.body.classList.remove(currentTheme);
  document.body.classList.add(themeName + "-preview");

  // Actualizar qué círculo está activo
  const circles = document.querySelectorAll(".theme-circle");
  circles.forEach((circle) => {
    circle.classList.toggle("active", circle.dataset.theme === themeName);
  });
}

// Guardar el cambio de color definitivamente
function saveTheme() {
  if (selectedTheme) {
    // Guardar la preferencia en localStorage
    localStorage.setItem("theme", selectedTheme);
    currentTheme = selectedTheme;

    // Aplica el cambio de color definitivo
    document.body.classList.remove(
      document.body.className.split(" ").find((c) => c.includes("-preview"))
    );
    document.body.classList.add(selectedTheme);
  }

  // Cerra el modal
  const modal = bootstrap.Modal.getInstance(
    document.getElementById("modalConfiguraciones")
  );
  if (modal) modal.hide();
}

// Cargar el cambio de color al iniciar la página
document.addEventListener("DOMContentLoaded", function () {
  // Aplica el cambio de color guardado
  document.body.classList.add(currentTheme);

  // Agrega evento click a cada círculo de tema
  document.querySelectorAll(".theme-circle").forEach((circle) => {
    circle.addEventListener("click", () => previewTheme(circle.dataset.theme));
  });

  // Botón para guardar cambios
  document
    .getElementById("saveThemeChanges")
    ?.addEventListener("click", saveTheme);

  // Restaura el cambio de color al cerrar modal sin guardar
  document
    .getElementById("modalConfiguraciones")
    ?.addEventListener("hidden.bs.modal", function () {
      if (selectedTheme && selectedTheme !== currentTheme) {
        document.body.classList.remove(selectedTheme + "-preview");
        document.body.classList.add(currentTheme);
      }
      selectedTheme = null;
    });
});
