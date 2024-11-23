function deshabilitarPregunta(id) {
    console.log("id: ", id);
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "/editor/deshabilitar?pregunta=" + id);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send();
    xhr.onload = () => {
        if (xhr.status === 200) {
            alert("Pregunta deshabilitada");
        }else{
            alert("Error al deshabilitar la pregunta");
        }
    document.getElementById(id).disabled = true;
    }
}