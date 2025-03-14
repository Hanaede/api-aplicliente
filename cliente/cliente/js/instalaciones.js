// Espera a que el DOM esté completamente cargado antes de ejecutar el código
document.addEventListener("DOMContentLoaded", function () {
    // Verificar si el usuario ya está autenticado
    if (localStorage.getItem("token")) {
        // Si el usuario está autenticado, oculta los botones de login y registro
        document.getElementById("login").style.display = "none";
        document.getElementById("register").style.display = "none";
    } else {
        // Si el usuario no está autenticado, oculta las secciones de inscripciones, reservas y sistema
        document.getElementById("inscripciones").style.display = "none";
        document.getElementById("reservas").style.display = "none";
        document.getElementById("sistema").style.display = "none";
    }

    // Cargar las instalaciones desde la API
    fetch("http://apicentros.local/api/instalaciones")
    .then(response => response.json()) // Convertir la respuesta a JSON
    .then(instalaciones => {
        // Obtener el elemento de la lista de instalaciones
        let lista = document.getElementById("instalaciones-list");
        // Iterar sobre cada instalación y agregarla a la lista
        instalaciones.forEach(instalacion => {
            let li = document.createElement("li");
            // Crear el contenido HTML para cada instalación
            li.innerHTML = `<strong>${instalacion.nombre} - ${instalacion.nombre_centro}</strong><br>
                            <span class="info"><strong>Descripción</strong>: ${instalacion.descripcion}<br>
                            <strong>Capacidad máxima</strong>: ${instalacion.capacidad_maxima}</span>`;
            // Agregar el elemento a la lista
            lista.appendChild(li);
        });
    });

    // Obtener los elementos del formulario de búsqueda
    const searchForm = document.getElementById("search-form");
    const nombreInput = document.getElementById("search-nombre-instalacion");
    const descripcionInput = document.getElementById("search-descripcion-Instalacion");
    
    // Función de debounce para limitar la frecuencia de ejecución de la búsqueda
    function debounce(fn, delay) {
        let timeoutId;
        return function (...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => fn(...args), delay);
        };
    }

    // Función para buscar instalaciones
    function buscarInstalaciones() {
        let query = {};
        
        // Agregar el nombre al query si no está vacío
        if (nombreInput.value.trim() !== "") {
            query.nombre = nombreInput.value.trim();
        }
    
        // Agregar la descripción al query si no está vacío
        if (descripcionInput.value.trim() !== "") {
            query.descripcion = descripcionInput.value.trim();
        }
    
        // Construir la URL con los parámetros de búsqueda
        const queryString = new URLSearchParams(query).toString();
        const url = queryString ? `http://apicentros.local/api/instalaciones?${queryString}` : `http://apicentros.local/api/instalaciones`;
        const error = document.getElementById("mensajeError");
        
        // Realizar la búsqueda en la API
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    // Mostrar mensaje de error si no se encuentran instalaciones
                    if (response.status === 404) {
                        error.style.display = 'block'; // Mostrar el mensaje
                        error.innerHTML = 'No se encontraron instalaciones que coincidan con su búsqueda';
                        console.log('Error 404: Instalaciones no encontradas'); // Verificar en la consola
                    } else {
                        console.log(`Error HTTP: ${response.status}`); // Verificar otros errores HTTP
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return [];
                }
                error.style.display = 'none'; // Asegurarse de que el mensaje de error se oculta
                return response.json();
            })
            .then(instalaciones => {
                console.log("Instalaciones encontradas:", instalaciones);
                console.log("URL de búsqueda:", url);
                let lista = document.getElementById("instalaciones-list");
                lista.innerHTML = ''; // Limpiar la lista de instalaciones
                instalaciones.forEach(instalacion => {
                    let li = document.createElement("li");
                    // Crear el contenido HTML para cada instalación
                    li.innerHTML = `<strong>${instalacion.nombre} - ${instalacion.nombre_centro}</strong><br>
                                    <span class="info"><strong>Descripción</strong>: ${instalacion.descripcion}<br>
                                    <strong>Capacidad máxima</strong>: ${instalacion.capacidad_maxima}</span>`;
                    // Agregar el elemento a la lista
                    lista.appendChild(li);
                });
            })
            .catch(error => console.error("Error buscando Instalaciones:", error));
    }

    // Crear una versión debounced de la función de búsqueda
    const buscarInstalacionesDebounced = debounce(buscarInstalaciones, 300);

    // Ejecutar la búsqueda en tiempo real mientras se escribe
    if (nombreInput) {
        nombreInput.addEventListener("input", buscarInstalacionesDebounced);
    }
    
    if (descripcionInput) {
        descripcionInput.addEventListener("input", buscarInstalacionesDebounced);
    }

    // Evitar que el formulario se envíe al presionar Enter
    if (searchForm) {
        searchForm.addEventListener("submit", function (event) {
            event.preventDefault();
            buscarInstalaciones();
        });
    }
});