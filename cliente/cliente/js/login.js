
// Verificar si el usuario ya está autenticado

if (localStorage.getItem("token")) {
    window.location.href = "./index.html";
}

// Agregar un evento al formulario de inicio de sesión
document.getElementById('login-form').addEventListener('submit', function (event) {
    event.preventDefault();
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    const loginData = { email, password };
    console.log("Enviando JSON:", JSON.stringify(loginData));

    // Enviar los datos al servidor
    fetch("http://apicentros.local/api/login", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(loginData)
    })
        .then(response => {
            console.log("Respuesta completa:", response);
            return response.json();
        })
        .then(data => {
            const error = document.getElementById('mensajeError');

            // Ocultar el mensaje de error en caso de que se haya mostrado antes
            error.style.display = 'none';

            // Guardar el token en el almacenamiento local si se recibe
            if (data.jwt) {
                localStorage.setItem("token", data.jwt);
                window.location.href = "./sistema.html";
            } else {
                // Mostrar el mensaje de error si las credenciales son incorrectas
                error.innerHTML = "Datos erróneos";
                error.style.display = 'block'; // Asegurarse de que sea visible
            }
        })

        .catch(error => {
            console.error("Error:", error);
            document.getElementById('mensajeError').innerText = "Error en la solicitud";
        });
});