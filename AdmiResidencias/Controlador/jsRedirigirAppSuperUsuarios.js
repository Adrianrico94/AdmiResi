// Función que redirige al ancla correspondiente al seleccionar una opción
function redirigir() {
  var select = document.getElementById("seccionSelect"); // Obtiene el <select> por su id
  var valorSeleccionado = select.value; // Obtiene el valor seleccionado

  if (valorSeleccionado) {
    // Si se selecciona una opción, redirige a la parte correspondiente
    window.location.href = valorSeleccionado;
  }
}
