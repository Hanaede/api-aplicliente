document.addEventListener("DOMContentLoaded", () => {
    // Verificar si el usuario ya está autenticado
    const token = localStorage.getItem("token");
    if (!token) {
        window.location.href = "./login.html";
        return;
    }

    const decodedToken = jwt_decode(token);
    const userId = decodedToken.data[0];

    // Obtener las actividades
    fetch("http://apicentros.local/api/actividades", {
        method: "GET",
        headers: {
            "Authorization": `Bearer ${token}`
        }
    })
    .then(response => response.json())
    .then(actividades => {
        const actividadesMap = new Map();
        actividades.forEach(actividad => {
            actividadesMap.set(actividad.id, actividad.nombre);
        });

        // Obtener las inscripciones del usuario a través de su id
        fetch(`http://apicentros.local/api/inscripciones?user_id=${userId}`, {
            method: "GET",
            headers: {
                "Authorization": `Bearer ${token}`
            }
        })
        .then(response => response.json())
        .then(inscripciones => {
            const inscripcionesList = document.getElementById("inscripciones-list");
            inscripciones.forEach(inscripcion => {
                const nombreActividad = actividadesMap.get(inscripcion.actividad_id) || "Desconocida";
                const nombreCentro = inscripcion.nombre_centro || "Desconocido";
                const li = document.createElement("li");
                li.innerHTML = `<strong>Nombre del Solicitante: ${inscripcion.nombre_solicitante}</strong><br>
                                <span class="info">Teléfono: ${inscripcion.telefono}<br>
                                Email: ${inscripcion.email}<br>
                                Actividad: ${nombreActividad}<br>
                                Centro Cívico: ${nombreCentro}<br>
                                Fecha de Inscripción: ${inscripcion.fecha_inscripcion}<br>
                                Estado: ${inscripcion.estado}</span><br/>
                                <button class="cancel-button" onclick="cancelarInscripcion(${inscripcion.id})">Cancelar Inscripción</button>`;
                inscripcionesList.appendChild(li);
            });
        })
        .catch(error => console.error("Error cargando inscripciones:", error));
    })
    .catch(error => console.error("Error cargando actividades:", error));
});

// Función para cancelar una inscripción
function cancelarInscripcion(inscripcionId) {
    if (confirm("¿Estás seguro de que deseas cancelar esta inscripción? Esta acción no se puede deshacer.")) {
        const token = localStorage.getItem("token");
        fetch(`http://apicentros.local/api/inscripciones/${inscripcionId}`, {
            method: "DELETE",
            headers: {
                "Authorization": `Bearer ${token}`
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.message == "Inscripcion eliminada con éxito") {
                // alert("Inscripción cancelada con éxito.");
                window.location.reload();
            } else {
                window.location.reload();
                // alert("Error al cancelar la inscripción: " + data.message);
            }
        })
        .catch(error => console.error("Error:", error));
    }
}