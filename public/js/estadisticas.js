google.charts.load('current', { packages: ['corechart'] });
google.charts.setOnLoadCallback(() => actualizarGrafico(7));

function crearGraficoLineal(dataArray) {
    const data = new google.visualization.DataTable();
    data.addColumn('string', 'Fecha');
    data.addColumn('number', 'Nuevos Usuarios');
    data.addRows(dataArray);

    const options = {
        title: 'Nuevos Usuarios',
        curveType: 'function',
        legend: { position: 'bottom' },
        width: 800,
        height: 400,
        colors: ['#1e88e5']
    };

    const chart = new google.visualization.LineChart(document.getElementById('line_chart_div'));
    chart.draw(data, options);
}

function actualizarGrafico(cantDias) {
    console.log("cantidad de dias: " + cantDias);

    const xhr = new XMLHttpRequest();
    xhr.open('GET', `/admin/obtenerNuevosUsuarios?dias=${cantDias}`);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send();

    xhr.onload = () => {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            const dataArray = response.map(item => [item.fecha, parseInt(item.cant_jugadores)]);
            crearGraficoLineal(dataArray);
        } else {
            console.error('Error al actualizar el gráfico:', xhr.statusText);
        }
    };
    xhr.onerror = () => {
        console.error('Error de red al intentar obtener datos para el gráfico.');
    };
}
