// Verificar si el usuario ya está autenticado
if (localStorage.getItem("token")) {
    // Redirigir al usuario a la página de inicio si ya está autenticado
    window.location.href = "./index.html";
}

// Agregar un evento al formulario de registro
document.getElementById('register-form').addEventListener('submit', function(event) {
    event.preventDefault();
    // Obtener los datos del formulario
    const nombre = document.getElementById('nombre').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const repassword = document.getElementById('repassword').value;

    // Validar los datos
    if (!nombre || !email || !password || !repassword) {
        alert("Todos los campos son obligatorios");
        return;
    }

    if (password !== repassword) {
        alert("Las contraseñas no coinciden");
        return;
    }

    // Enviar los datos al servidor
    const registerData = { nombre, email, password };
    console.log("Enviando JSON:", JSON.stringify(registerData));

    fetch("http://apicentros.local/api/register", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(registerData)
    })
    .then(response => {
        console.log("Respuesta completa:", response);
        return response.json(); // Convertir la respuesta a JSON
    })
    .then(data => {
        console.log("Datos recibidos:", data);
        const error = document.getElementById('mensajeError');
        const success = document.getElementById('registroExitoso');
        if (data.message === "Usuario creado con éxito") {
            // Mostrar un mensaje de éxito y redirigir al usuario a la página de inicio de sesión
            success.innerHTML = "Usuario creado con éxito";
            success.style.display = 'block';
            error.style.display = 'none';
            setTimeout(() => {
                window.location.href = "./login.html";
            }, 2000);
        } else {
            // Mostrar un mensaje de error si no se puede registrar el usuario
            error.innerHTML = "Error al registrar el usuario";
            error.style.display = 'block'; // Asegurarse de que sea visible
        }
    })
    .catch(error => console.error("Error:", error)); // Manejar errores en la solicitud
});