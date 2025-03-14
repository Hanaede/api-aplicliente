document.addEventListener("DOMContentLoaded", function () {
    // Verificar si el token está almacenado en localStorage
    if (localStorage.getItem("token")) {
        // Ocultar los botones de login y registro si el usuario está autenticado
        document.getElementById("login").style.display = "none";
        document.getElementById("register").style.display = "none";
    } else {
        // Ocultar las secciones de inscripciones, reservas y sistema si el usuario no está autenticado
        document.getElementById("inscripciones").style.display = "none";
        document.getElementById("reservas").style.display = "none";
        document.getElementById("sistema").style.display = "none";
    }

    // Cargar las actividades disponibles
    fetch("http://apicentros.local/api/actividades")
        .then(response => {
            // Verificar si la respuesta HTTP es exitosa
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            // Convertir la respuesta a JSON
            return response.json();
        })
        .then(actividades => {
            // Obtener el elemento de la lista de actividades
            let lista = document.getElementById("actividades-list");
            // Iterar sobre las actividades y agregarlas a la lista
            actividades.forEach(actividad => {
                let li = document.createElement("li");
                li.innerHTML = `<strong>${actividad.nombre} - ${actividad.nombre_centro}</strong><br>
                            <span class="info"><strong>Descripción</strong>: ${actividad.descripcion}<br>
                            <strong>Inicio</strong>: ${actividad.fecha_inicio} - Fin: ${actividad.fecha_final}<br>
                            <strong>Horario</strong>: ${actividad.horario}<br>
                            <strong>Plazas</strong>: ${actividad.plazas}</span>`;
                lista.appendChild(li);
            });
        });

    // Buscar actividades
    const searchForm = document.getElementById("search-form");
    const nombreInput = document.getElementById("search-nombre-actividad");
    const descripcionInput = document.getElementById("search-descripcion-actividad");

    // Función para evitar múltiples búsquedas mientras se escribe
    function debounce(fn, delay) {
        let timeoutId; // Variable para almacenar el identificador del temporizador
        return function (...args) {
            clearTimeout(timeoutId); // Cancela cualquier temporizador previo para evitar ejecución prematura
            timeoutId = setTimeout(() => fn(...args), delay); // Establece un nuevo temporizador para ejecutar la función después del delay
        };
    }

    // Función para buscar actividades
    function buscarActividades() {
        let query = {};

        // Agregar el nombre de la actividad a la consulta si no está vacío
        if (nombreInput.value.trim() !== "") {
            query.nombre = nombreInput.value.trim();
        }

        // Agregar la descripción de la actividad a la consulta si no está vacío
        if (descripcionInput.value.trim() !== "") {
            query.descripcion = descripcionInput.value.trim();
        }

        // Construir la URL de búsqueda con los parámetros de consulta
        const queryString = new URLSearchParams(query).toString();
        const url = queryString ? `http://apicentros.local/api/actividades?${queryString}` : `http://apicentros.local/api/actividades`;
        const error = document.getElementById("mensajeError");
        fetch(url)
            .then(response => {
                // Verificar si la respuesta HTTP es exitosa
                if (!response.ok) {
                    if (response.status === 404) {
                        // Mostrar mensaje de error si no se encuentran actividades
                        error.style.display = 'block';
                        error.innerHTML = 'No se encontraron actividades que coincidan con su búsqueda';
                        console.log('Error 404: Actividades no encontradas');
                    } else {
                        console.log(`Error HTTP: ${response.status}`);
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return [];
                }
                // Ocultar el mensaje de error si la búsqueda es exitosa
                error.style.display = 'none';

                return response.json();
            })
            .then(actividades => {
                console.log("Actividades encontradas:", actividades);
                console.log("URL de búsqueda:", url);
                // Limpiar la lista antes de mostrar los resultados
                let lista = document.getElementById("actividades-list");
                lista.innerHTML = '';
                // Iterar sobre las actividades y agregarlas a la lista
                actividades.forEach(actividad => {
                    let li = document.createElement("li");
                    li.innerHTML = `<strong>${actividad.nombre}</strong><br>
                                    <span class="info"><strong>Descripción</strong>: ${actividad.descripcion}<br>
                                    <strong>Inicio</strong>: ${actividad.fecha_inicio} - Fin: ${actividad.fecha_final}<br>
                                    <strong>Horario</strong>: ${actividad.horario}<br>
                                    <strong>Plazas</strong>: ${actividad.plazas}</span>`;
                    lista.appendChild(li);
                });
            })
            .catch(error => console.error("Error buscando actividades:", error));
    }

    // Crear una versión de la función buscarActividades con debounce
    const buscarActividadesDebounced = debounce(buscarActividades, 300);

    // Ejecutar la búsqueda en tiempo real mientras se escribe
    nombreInput.addEventListener("input", buscarActividadesDebounced);
    descripcionInput.addEventListener("input", buscarActividadesDebounced);

    // Evitar que el formulario se envíe al presionar Enter
    searchForm.addEventListener("submit", function (event) {
        event.preventDefault();
        buscarActividades();
    });
});
