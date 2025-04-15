/**
 * Funciones para gráficos y reportes
 * Requiere la biblioteca Chart.js
 */

/**
 * Inicializa un gráfico de barras
 * @param {string} canvasId ID del canvas donde renderizar el gráfico
 * @param {object} data Datos para el gráfico
 * @param {object} options Opciones adicionales
 * @return {Chart} Instancia del gráfico
 */
function initBarChart(canvasId, data, options = {}) {
    // Validar que Chart.js esté disponible
    if (typeof Chart === 'undefined') {
        console.error('Chart.js no está disponible. Asegúrate de incluir la biblioteca.');
        return null;
    }
    
    const canvas = document.getElementById(canvasId);
    if (!canvas) {
        console.error(`Canvas con ID "${canvasId}" no encontrado.`);
        return null;
    }
    
    // Opciones por defecto
    const defaultOptions = {
        responsive: true,
       maintainAspectRatio: false,
       plugins: {
           legend: {
               position: 'top',
           },
           title: {
               display: true,
               text: 'Gráfico de Barras'
           },
           tooltip: {
               mode: 'index',
               intersect: false,
           }
       },
       scales: {
           y: {
               beginAtZero: true
           }
       }
   };
   
   // Combinar opciones
   const chartOptions = Object.assign({}, defaultOptions, options);
   
   // Crear y retornar el gráfico
   return new Chart(canvas, {
       type: 'bar',
       data: data,
       options: chartOptions
   });
}

/**
* Inicializa un gráfico de líneas
* @param {string} canvasId ID del canvas donde renderizar el gráfico
* @param {object} data Datos para el gráfico
* @param {object} options Opciones adicionales
* @return {Chart} Instancia del gráfico
*/
function initLineChart(canvasId, data, options = {}) {
   // Validar que Chart.js esté disponible
   if (typeof Chart === 'undefined') {
       console.error('Chart.js no está disponible. Asegúrate de incluir la biblioteca.');
       return null;
   }
   
   const canvas = document.getElementById(canvasId);
   if (!canvas) {
       console.error(`Canvas con ID "${canvasId}" no encontrado.`);
       return null;
   }
   
   // Opciones por defecto
   const defaultOptions = {
       responsive: true,
       maintainAspectRatio: false,
       plugins: {
           legend: {
               position: 'top',
           },
           title: {
               display: true,
               text: 'Gráfico de Líneas'
           },
           tooltip: {
               mode: 'index',
               intersect: false,
           }
       },
       scales: {
           y: {
               beginAtZero: true
           }
       }
   };
   
   // Combinar opciones
   const chartOptions = Object.assign({}, defaultOptions, options);
   
   // Crear y retornar el gráfico
   return new Chart(canvas, {
       type: 'line',
       data: data,
       options: chartOptions
   });
}

/**
* Inicializa un gráfico de pastel
* @param {string} canvasId ID del canvas donde renderizar el gráfico
* @param {object} data Datos para el gráfico
* @param {object} options Opciones adicionales
* @return {Chart} Instancia del gráfico
*/
function initPieChart(canvasId, data, options = {}) {
   // Validar que Chart.js esté disponible
   if (typeof Chart === 'undefined') {
       console.error('Chart.js no está disponible. Asegúrate de incluir la biblioteca.');
       return null;
   }
   
   const canvas = document.getElementById(canvasId);
   if (!canvas) {
       console.error(`Canvas con ID "${canvasId}" no encontrado.`);
       return null;
   }
   
   // Opciones por defecto
   const defaultOptions = {
       responsive: true,
       maintainAspectRatio: false,
       plugins: {
           legend: {
               position: 'top',
           },
           title: {
               display: true,
               text: 'Gráfico de Pastel'
           },
           tooltip: {
               mode: 'index',
               intersect: false,
           }
       }
   };
   
   // Combinar opciones
   const chartOptions = Object.assign({}, defaultOptions, options);
   
   // Crear y retornar el gráfico
   return new Chart(canvas, {
       type: 'pie',
       data: data,
       options: chartOptions
   });
}

/**
* Inicializa un gráfico de dona
* @param {string} canvasId ID del canvas donde renderizar el gráfico
* @param {object} data Datos para el gráfico
* @param {object} options Opciones adicionales
* @return {Chart} Instancia del gráfico
*/
function initDoughnutChart(canvasId, data, options = {}) {
   // Validar que Chart.js esté disponible
   if (typeof Chart === 'undefined') {
       console.error('Chart.js no está disponible. Asegúrate de incluir la biblioteca.');
       return null;
   }
   
   const canvas = document.getElementById(canvasId);
   if (!canvas) {
       console.error(`Canvas con ID "${canvasId}" no encontrado.`);
       return null;
   }
   
   // Opciones por defecto
   const defaultOptions = {
       responsive: true,
       maintainAspectRatio: false,
       plugins: {
           legend: {
               position: 'top',
           },
           title: {
               display: true,
               text: 'Gráfico de Dona'
           },
           tooltip: {
               mode: 'index',
               intersect: false,
           }
       }
   };
   
   // Combinar opciones
   const chartOptions = Object.assign({}, defaultOptions, options);
   
   // Crear y retornar el gráfico
   return new Chart(canvas, {
       type: 'doughnut',
       data: data,
       options: chartOptions
   });
}

/**
* Genera colores aleatorios para gráficos
* @param {number} count Cantidad de colores a generar
* @return {string[]} Array de colores en formato rgba()
*/
function generateChartColors(count) {
   const colors = [];
   
   for (let i = 0; i < count; i++) {
       // Generar componentes RGB aleatorios
       const r = Math.floor(Math.random() * 255);
       const g = Math.floor(Math.random() * 255);
       const b = Math.floor(Math.random() * 255);
       
       // Agregar color con opacidad 0.7
       colors.push(`rgba(${r}, ${g}, ${b}, 0.7)`);
   }
   
   return colors;
}

/**
* Actualiza un gráfico con nuevos datos
* @param {Chart} chart Instancia de Chart.js
* @param {object} newData Nuevos datos para el gráfico
*/
function updateChart(chart, newData) {
   if (!chart) {
       console.error('Se requiere una instancia válida de Chart.js');
       return;
   }
   
   // Actualizar datasets
   chart.data.datasets = newData.datasets || chart.data.datasets;
   
   // Actualizar etiquetas si se proporcionan
   if (newData.labels) {
       chart.data.labels = newData.labels;
   }
   
   // Aplicar cambios
   chart.update();
}

/**
* Crea un dataset para gráficos de ventas por mes
* @param {object[]} data Datos de ventas con propiedades mes y monto
* @return {object} Objeto de datos formateado para Chart.js
*/
function createMonthlySalesData(data) {
   // Nombres de los meses
   const monthNames = [
       'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
       'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
   ];
   
   // Preparar arrays de etiquetas y datos
   const labels = [];
   const values = [];
   
   // Procesar datos
   data.forEach(item => {
       // El mes debe ser un número entre 1 y 12
       const monthIndex = parseInt(item.mes) - 1;
       if (monthIndex >= 0 && monthIndex < 12) {
           labels.push(monthNames[monthIndex]);
           values.push(parseFloat(item.monto));
       }
   });
   
   // Generar colores
   const backgroundColor = generateChartColors(values.length);
   
   // Crear objeto de datos para el gráfico
   return {
       labels: labels,
       datasets: [{
           label: 'Ventas Mensuales',
           data: values,
           backgroundColor: backgroundColor,
           borderColor: backgroundColor.map(color => color.replace('0.7', '1')),
           borderWidth: 1
       }]
   };
}