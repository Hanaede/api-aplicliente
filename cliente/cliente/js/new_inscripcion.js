
document.addEventListener("DOMContentLoaded", () => {
    // Verificar si el usuario ya está autenticado
    const token = localStorage.getItem("token");
    if (!token) {
        window.location.href = "./login.html";
        return;
    }
    // Decodificar el token para obtener el ID del usuario
    const decodedToken = jwt_decode(token);
    const userId = decodedToken.data[0];

    // Cargar las actividades en el select
    cargarActividades(document.getElementById("actividad"));

    document.getElementById("new-inscripcion-form").addEventListener("submit", function(event) {
        event.preventDefault();
        crearInscripcion(token, userId);
    });
});

// Función para cargar las actividades en el select
function cargarActividades(selectElement) {
    fetch("http://apicentros.local/api/actividades")
        .then(response => response.json())
        .then(actividades => {
            actividades.forEach(actividad => {
                let option = document.createElement("option");
                option.value = actividad.id;
                option.textContent = actividad.nombre + "-" + actividad.nombre_centro;
                selectElement.appendChild(option);
            });
        })
        .catch(error => console.error("Error cargando actividades:", error));
}

// Función para crear una inscripción
function crearInscripcion(token, userId) {
    const nombre = document.getElementById("nombre").value;
    const telefono = document.getElementById("telefono").value;
    const email = document.getElementById("email").value;
    const actividad = parseInt(document.getElementById("actividad").value, 10);
    const fecha_inscripcion = document.getElementById("fecha_inscripcion").value;

    // Validar los datos
    if (!nombre || !telefono || !email || !actividad || !fecha_inscripcion) {
        alert("Por favor, complete todos los campos.");
        return;
    }

    // Enviar los datos al servidor
    const inscripcionData = {
        nombre_solicitante: nombre,
        id_usuario: userId,
        telefono: telefono,
        email: email,
        actividad_id: actividad,
        fecha_inscripcion: fecha_inscripcion,
        estado: "Lista de espera"
    };

    console.log(inscripcionData);

    // Enviar los datos al servidor
    fetch("http://apicentros.local/api/inscripciones", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${token}`
        },
        body: JSON.stringify(inscripcionData)
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                // Si no es JSON, lanzamos un error con el texto que recibimos
                throw new Error(`Error HTTP: ${response.status}, ${text}`);
            });
        }
        // Aseguramos que la respuesta sea JSON
        return response.json().catch(() => {
            throw new Error("Respuesta no es un JSON válido");
        });
    })
    .then(data => {
        if (data.message === "Inscripcion creada con éxito") {
            alert("Inscripción creada con éxito.");
            window.location.href = "./inscripciones.html";
        } else {
            alert("Error al crear la inscripción: " + data.message);
        }
    })
    .catch(error => {
        console.error("Error al crear la inscripción:", error);
        alert("Error al crear la inscripción: " + error.message);
    });
}
