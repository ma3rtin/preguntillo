let filtroDias = null;

document.querySelectorAll('.button-container button').forEach(button => {
    button.addEventListener('click', (e) => {
        switch (e.target.innerText) {
            case 'Todos':
                filtroDias = null;
                break;
            case 'Última semana':
                filtroDias = 7;
                break;
            case 'Último mes':
                filtroDias = 30;
                break;
            case 'Último año':
                filtroDias = 365;
                break;
        }
        actualizarGraficos();
    });
});

function actualizarGraficos() {
    actualizarGraficoPais();
    actualizarGraficoGenero();
    actualizarGraficoEdad();
}

google.charts.load('current', { 'packages': ['corechart', 'bar'] });
google.charts.setOnLoadCallback(() => actualizarGraficoPais());

function crearGraficoPais(dataArray) {
    const data = new google.visualization.DataTable();
    data.addColumn('string', 'País');
    data.addColumn('number', 'Cantidad');

    const processedData = dataArray.map(item => [
        item[0],
        parseInt(item[1], 10)
    ]);

    data.addRows(processedData);

    const options = {
        title: 'Distribución de Usuarios por País',
        chartArea: { width: '50%' },
        hAxis: {
            title: 'Cantidad de Usuarios',
            minValue: 0
        },
        vAxis: {
            title: 'País'
        },
        colors: ['#795df3'],
    };

    const chart = new google.visualization.BarChart(document.getElementById('chart_div_pais'));
    chart.draw(data, options);
}

function actualizarGraficoPais() {
    const xhr = new XMLHttpRequest();
    const url = filtroDias ? `/admin/cantJugadoresPorPais?dias=${filtroDias}` : '/admin/cantJugadoresPorPais';
    xhr.open('GET', url);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send();

    xhr.onload = () => {
        const data = JSON.parse(xhr.responseText);
        const dataArray = data.map(item => {
            return [item.pais, parseInt(item.cantidad_usuarios, 10)];
        });
        crearGraficoPais(dataArray);
    };
}

// Género
google.charts.load('current', { 'packages': ['corechart'] });
google.charts.setOnLoadCallback(() => actualizarGraficoGenero());

function crearGraficoGenero(dataArray) {
    const data = new google.visualization.DataTable();
    data.addColumn('string', 'Género');
    data.addColumn('number', 'Cantidad');

    const processedData = dataArray.map(item => [
        item[0],
        parseInt(item[1], 10)
    ]);

    data.addRows(processedData);

    const options = {
        title: 'Distribución de Usuarios por Género',
        width: 500,
        height: 500,
        legend: { position: 'right' },
        pieHole: 0.4,
        colors: ['#e57373', '#64b5f6', '#81c784']
    };

    const chart = new google.visualization.PieChart(document.getElementById('chart_div_genero'));
    chart.draw(data, options);
}

function actualizarGraficoGenero() {
    const xhr = new XMLHttpRequest();
    const url = filtroDias ? `/admin/cantJugadoresPorGenero?dias=${filtroDias}` : '/admin/cantJugadoresPorGenero';
    xhr.open('GET', url);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send();

    xhr.onload = () => {
        const data = JSON.parse(xhr.responseText);
        const dataArray = data.map(item => [item.genero, item.cantidad_usuarios]);
        crearGraficoGenero(dataArray);
    };
}

// Edad
google.charts.load('current', { 'packages': ['corechart'] });
google.charts.setOnLoadCallback(() => actualizarGraficoEdad());

function crearGraficoEdad(data) {
    const dataTable = new google.visualization.DataTable();
    dataTable.addColumn('string', 'Grupo de Edad');
    dataTable.addColumn('number', 'Cantidad');

    const processedData = [
        ['Menores de 18', parseInt(data.menores)],
        ['18-64 años', parseInt(data.medios)],
        ['Jubilados', parseInt(data.jubilados)]
    ];

    dataTable.addRows(processedData);

    const options = {
        title: 'Distribución de Usuarios por Edad',
        width: 500,
        height: 500,
        pieHole: 0.4,
        colors: ['#1e88e5', '#fbc02d', '#8e24aa']
    };

    const chart = new google.visualization.PieChart(document.getElementById('chart_div_edad'));
    chart.draw(dataTable, options);
}

function actualizarGraficoEdad() {
    const xhr = new XMLHttpRequest();
    const url = filtroDias ? `/admin/cantJugadoresPorEdad?dias=${filtroDias}` : '/admin/cantJugadoresPorEdad';
    xhr.open('GET', url);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send();

    xhr.onload = () => {
        const data = JSON.parse(xhr.responseText);
        crearGraficoEdad(data);
    };
}


