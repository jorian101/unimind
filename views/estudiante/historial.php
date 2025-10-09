<?php
require_once dirname(__DIR__) . '/./pageHeader.php';
renderPageHeader('Historial de evaluaciones', ['Dashboard', 'Historial de evaluaciones']);
?>
<link rel="stylesheet" href="views/estudiante/historial.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="historial">
    <section class="historial__card historial__card--history">
        <h2 class="historial__title">Evaluaciones</h2>
        <p class="historial__subtitle">Registro completo de tus mediciones de estrés y ansiedad</p>

        <div class="historial__table-container">
            <table class="historial__table" id="history-table">
                <thead class="historial__table-head">
                    <tr class="historial__table-row">
                        <th class="historial__table-header">Fecha</th>
                        <th class="historial__table-header">Nivel de Estrés</th>
                        <th class="historial__table-header">Nivel de Ansiedad</th>
                        <th class="historial__table-header">Tendencia</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </section>

    <section class="historial__card historial__card--stats">
        <h2 class="historial__title">Estadísticas del Mes</h2>
        <p class="historial__subtitle">Resumen de tu progreso mensual</p>

        <div class="historial__stats-grid">
            <div class="historial__stat-item">
                <p class="historial__stat-label">Promedio de Estrés</p>
                <p class="historial__stat-value" id="avg-stress">--%</p>
            </div>
            <div class="historial__stat-item">
                <p class="historial__stat-label">Promedio de Ansiedad</p>
                <p class="historial__stat-value" id="avg-anxiety">--%</p>
            </div>
            <div class="historial__stat-item">
                <p class="historial__stat-label">Tendencia General</p>
                <p class="historial__stat-value" id="trend">--</p>
            </div>
        </div>
    </section>
</div>

<script>
// Simulando datos desde una "base de datos"
const evaluations = [
    { fecha: "14 de enero de 2025", estres: 65, ansiedad: 48 },
    { fecha: "7 de enero de 2025", estres: 72, ansiedad: 55 },
    { fecha: "31 de diciembre de 2024", estres: 58, ansiedad: 42 },
    { fecha: "24 de diciembre de 2024", estres: 45, ansiedad: 38 },
    { fecha: "17 de diciembre de 2024", estres: 68, ansiedad: 52 },
];

// Función para clasificar niveles
function nivel(valor) {
    if (valor < 40) return { texto: "Bajo", clase: "bajo" };
    if (valor < 70) return { texto: "Moderado", clase: "moderado" };
    return { texto: "Alto", clase: "alto" };
}

// Cálculo de tendencia general (simplificada)
function tendencia(evals) {
    let mejoras = 0;
    for (let i = 1; i < evals.length; i++) {
        const prev = (evals[i - 1].estres + evals[i - 1].ansiedad) / 2;
        const curr = (evals[i].estres + evals[i].ansiedad) / 2;
        if (curr < prev) mejoras++;
    }
    return mejoras > evals.length / 2 ? "Mejorando" : "Incrementando";
}

// Render tabla
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.querySelector("#history-table tbody");
    evaluations.forEach((e, i) => {
        const nEstres = nivel(e.estres);
        const nAnsiedad = nivel(e.ansiedad);
        const tendenciaText = i > 0 
            ? (e.estres + e.ansiedad) < (evaluations[i - 1].estres + evaluations[i - 1].ansiedad)
                ? "Mejorando" 
                : "Incrementando"
            : "-";
        const tendenciaClass = tendenciaText === "Mejorando" ? "historial__badge--trend-mejorando" : "historial__badge--trend-empeorando";
        const arrow = tendenciaText === "Mejorando" ? '<i class="fas fa-arrow-up"></i>' : tendenciaText === "Incrementando" ? '<i class="fas fa-arrow-down"></i>' : "";

        tbody.innerHTML += `
            <tr class="historial__table-row">
                <td class="historial__table-cell">${e.fecha}</td>
                <td class="historial__table-cell">
                    <div class="historial__cell-content">
                        <span class="historial__percentage">${e.estres}%</span>
                        <span class="historial__badge historial__badge--${nEstres.clase}">${nEstres.texto}</span>
                    </div>
                </td>
                <td class="historial__table-cell">
                    <div class="historial__cell-content">
                        <span class="historial__percentage">${e.ansiedad}%</span>
                        <span class="historial__badge historial__badge--${nAnsiedad.clase}">${nAnsiedad.texto}</span>
                    </div>
                </td>
                <td class="historial__table-cell"><span class="historial__badge ${tendenciaClass}">${arrow} ${tendenciaText}</span></td>
            </tr>
        `;
    });

    // Calcular promedios
    const avgStress = evaluations.reduce((acc, e) => acc + e.estres, 0) / evaluations.length;
    const avgAnxiety = evaluations.reduce((acc, e) => acc + e.ansiedad, 0) / evaluations.length;
    const trend = tendencia(evaluations);

    // Mostrar estadísticas
    document.getElementById("avg-stress").textContent = avgStress.toFixed(1) + "%";
    document.getElementById("avg-anxiety").textContent = avgAnxiety.toFixed(0) + "%";
    const trendArrow = trend === "Mejorando" ? '<i class="fas fa-arrow-up"></i> ' : trend === "Incrementando" ? '<i class="fas fa-arrow-down"></i> ' : "";
    document.getElementById("trend").innerHTML = trendArrow + trend;
    document.getElementById("trend").style.color = trend === "Mejorando" ? "var(--acc-500)" : "var(--pri-500)";
});
</script>
