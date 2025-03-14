// Obtener el token almacenado en el localStorage
const token = localStorage.getItem("token");
let decodedToken;

// Verificar si el usuario está autenticado
if (!localStorage.getItem("token")) {
    // Redirigir al usuario a la página de inicio de sesión si no está autenticado
    window.location.href = "./index.html";
    exit();
}

// Decodificar el token para obtener el ID del usuario
try {
    decodedToken = jwt_decode(token);
} catch (error) {
    // Mostrar un mensaje de error si la decodificación falla
    console.error("Error al decodificar el token:", error);
    alert("Token inválido.");
}

// Obtener la información del usuario
const userId = decodedToken.data[0];

// Realizar una solicitud para obtener la información del usuario
fetch(`http://apicentros.local/api/user?id=${userId}`, {
    method: "GET",
    headers: {
        "Authorization": `Bearer ${token}`
    }
})
.then(response => response.json()) // Convertir la respuesta a JSON
.then(data => {
    if (data) {
        // Mostrar la información del usuario en la página
        document.getElementById("user-name").textContent = data.nombre;
        document.getElementById("user-email").textContent = data.email;
        document.getElementById("user-info").style.display = "block";
    } else {
        // Mostrar un mensaje de error si no se puede obtener la información del usuario
        alert("Error al obtener la información del usuario.");
    }
})
.catch(error => console.error("Error:", error)); // Manejar errores en la solicitud

// Función para eliminar el usuario
function eliminarUsuario() {
    const token = localStorage.getItem("token");
    if (!token) {
        alert("No estás autenticado.");
        return;
    }

    const decodedToken = jwt_decode(token);
    const userId = decodedToken.data[0];
    // Verificar si el usuario está seguro de eliminar su cuenta
    if (confirm("¿Estás seguro de que deseas eliminar tu cuenta?")) {
        fetch(`http://apicentros.local/api/user?id=${userId}`, {
            method: "DELETE",
            headers: {
                "Content-Type": "application/json",
                "Authorization": `Bearer ${token}`
            }
        })
        .then(response => response.json()) // Convertir la respuesta a JSON
        .then(data => {
            console.log(data);
            if (data.message == "Usuario eliminado con éxito") {
                // Mostrar un mensaje de éxito y redirigir al usuario a la página de inicio de sesión
                alert("Usuario eliminado con éxito.");
                localStorage.removeItem("token");
                window.location.href = "./login.html";
            } else {
                // Mostrar un mensaje de error si no se puede eliminar el usuario
                alert("Error al eliminar el usuario: " + data.message);
            }
        })
        .catch(error => console.error("Error:", error)); // Manejar errores en la solicitud
    }
}

// Función para cerrar sesión
function cerrarSesion() {
    // Eliminar el token del localStorage y redirigir al usuario a la página de inicio de sesión
    localStorage.removeItem("token");
    window.location.href = "./index.html";
}

// Función para refrescar el token
function refrescarToken() {
    const token = localStorage.getItem("token");
    
    fetch("http://apicentros.local/api/token/refresh", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": `Bearer ${token}`
        }
    })
    .then(response => {
        console.log("Respuesta completa:", response);
        if (response.status === 401) {
            // Mostrar un mensaje de error si el token ha caducado y cerrar sesión
            alert("El token ha caducado. Por favor, inicie sesión de nuevo.");
            cerrarSesion();
            return;
        }
        return response.json(); // Convertir la respuesta a JSON
    })
    .then(data => {
        console.log("Datos recibidos:", data);
        
        if (data.jwt) {
            // Actualizar el token en el localStorage y mostrar un mensaje de éxito
            localStorage.setItem("token", data.jwt);
            alert("Token Actualizado");
        } else {
            // Mostrar un mensaje de error si no se puede actualizar el token
            alert("Error al actualizar el token");
        }
    })
    .catch(error => console.error("Error:", error)); // Manejar errores en la solicitud
}