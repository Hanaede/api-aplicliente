document.addEventListener("DOMContentLoaded", () => {
    // Verificar si el usuario ya está autenticado
    if (localStorage.getItem("token")) {
        // Ocultar los botones de login y registro si el usuario está autenticado
        document.getElementById("login").style.display = "none";
        document.getElementById("register").style.display = "none";
    } else {
        // Ocultar los botones de sistema, reservas e inscripciones si el usuario no está autenticado
        document.getElementById("sistema").style.display = "none";
        document.getElementById("reservas").style.display = "none";
        document.getElementById("inscripciones").style.display = "none";
    }

    // Cargar los centros
    cargarCentros();
});

// Función para cargar los centros
function cargarCentros() {
    fetch("http://apicentros.local/api/centros")
        .then(response => response.json()) // Convertir la respuesta a JSON
        .then(centros => {
            let lista = document.getElementById("centros-list");
            centros.forEach(centro => {
                let li = document.createElement("li");
                li.innerHTML = `<h2><strong>${centro.nombre}</strong></h2>
                                <p class="info">
                                    <span>Dirección: ${centro.direccion}</span><br><br>
                                    <span>Horario: ${centro.horario} </span><br><br>
                                    <span>Teléfono: ${centro.telefono}</span>
                                </p>`;
                
                let sublista = document.createElement("ul");
                // Cargar las instalaciones y actividades asociadas al centro
                cargarInstalacionesCentro(centro.id, sublista);
                cargarActividadesCentro(centro.id, sublista);
                li.appendChild(sublista);
                lista.appendChild(li);
            });
        })
        .catch(error => console.error("Error cargando centros:", error)); // Manejar errores en la solicitud
}

// Función para cargar las instalaciones asociadas al centro
function cargarInstalacionesCentro(id, lista) {
    fetch(`http://apicentros.local/api/centros/${id}/instalaciones`)
        .then(response => response.json()) // Convertir la respuesta a JSON
        .then(instalaciones => {
            instalaciones.forEach(instalacion => {
                let li = document.createElement("li");
                li.classList.add("listas");
                li.classList.add("instalaciones");
                li.innerHTML = `<strong> ${instalacion.nombre}</strong><br><br>
                                <span class="info"> <b>Descripción</b>: ${instalacion.descripcion}<br><br>
                                 Capacidad Máxima: ${instalacion.capacidad_maxima}</span>`;
                lista.appendChild(li);
            });
        });
}

// Función para cargar las actividades asociadas al centro
function cargarActividadesCentro(id, lista) {
    fetch(`http://apicentros.local/api/centros/${id}/actividades`)
        .then(response => response.json()) // Convertir la respuesta a JSON
        .then(actividades => {
            actividades.forEach(actividad => {
                let li = document.createElement("li");
                li.classList.add("listas");
                li.classList.add("actividades");
                li.innerHTML = `<strong>${actividad.nombre}</strong><br>
                                <span class="info">Descripción: ${actividad.descripcion}<br><br>
                                Inicio: ${actividad.fecha_inicio} - Fin: ${actividad.fecha_final}<br><br>
                                Horario: ${actividad.horario}<br><br>
                                Plazas: ${actividad.plazas}</span>`;
                lista.appendChild(li);
            });
        });
}

