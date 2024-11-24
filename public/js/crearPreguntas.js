document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById('formPregunta');

    if (form) {
        form.addEventListener('submit', (event) => {
            event.preventDefault();

            const formData = new FormData(form);

            const url = form.dataset.action;

            const xhr = new XMLHttpRequest();
            xhr.open("POST", url);

             xhr.onload = () => {
                if (xhr.status === 200) {
                    if (url === "/editor/agregarPregunta") {
                        alert("Pregunta agregada");
                    }else {
                        alert("Pregunta editada");
                    }
                }else{
                    if (url === "/editor/agregarPregunta") {
                        alert("Error al agregar la pregunta");
                    }else {
                        alert("Error al editar la pregunta");
                    }
                }
            };

            xhr.send(formData);
        });
    }
});
