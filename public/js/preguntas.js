function deshabilitarPregunta(id) {
    console.log("id: ", id);
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "/editor/deshabilitar?pregunta=" + id);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send();
    xhr.onload = () => {
        if (xhr.status === 200) {
            alert("Pregunta deshabilitada");
            document.getElementById(id).disabled = true;
        }else{
            alert("Error al deshabilitar la pregunta");
        }
    }
}

function habilitarPregunta(id) {
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "/editor/habilitar?pregunta=" + id);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send();
    xhr.onload = () => {
        if (xhr.status === 200) {
            alert("Pregunta habilitada");
            document.getElementById(id).disabled = true;
        }else{
            alert("Error al deshabilitar la pregunta");
        }
    }
}

function aceptarPregunta(id){
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "/editor/aceptarPregunta?pregunta=" + id);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send();
    xhr.onload = () => {
        if (xhr.status === 200) {
            alert("Pregunta aceptada");

            document.getElementById("rechazar-" + id).disabled = true;
            document.getElementById("aceptar-" + id).disabled = true;
        }else{
            alert("Error al aceptar la pregunta");
        }
    }
}

function rechazarPregunta(id){
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "/editor/rechazarPregunta?pregunta=" + id);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send();
    xhr.onload = () => {
        if (xhr.status === 200) {
            alert("Pregunta rechazada");
        }else{
            alert("Error al rechazar la pregunta");
        }
        document.getElementById("rechazar-" + id).disabled = true;
        document.getElementById("aceptar-" + id).disabled = true;
    }
}