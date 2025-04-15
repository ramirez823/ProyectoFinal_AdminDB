/**
 * Funciones de validación de formularios
 */

/**
 * Valida un formulario utilizando las restricciones de HTML5 y Bootstrap
 * @param {HTMLFormElement} form Formulario a validar
 * @return {boolean} True si el formulario es válido, False si no
 */
function validateForm(form) {
    // Si el navegador no soporta la validación nativa
    if (!form.checkValidity) {
        console.warn('Este navegador no soporta la validación nativa de formularios.');
        return true; // Asumir que es válido y depender de la validación del servidor
    }
    
    // Agregar la clase was-validated para mostrar los estilos de Bootstrap
    form.classList.add('was-validated');
    
    // Verificar validez del formulario
    return form.checkValidity();
}

/**
 * Inicializa la validación en un formulario
 * @param {string} formId ID del formulario
 * @param {function} successCallback Función a ejecutar si el formulario es válido
 */
function initFormValidation(formId, successCallback) {
    const form = document.getElementById(formId);
    
    if (!form) {
        console.error(`Formulario con ID "${formId}" no encontrado.`);
        return;
    }
    
    form.addEventListener('submit', function(event) {
        // Prevenir el envío por defecto
        event.preventDefault();
        
        // Validar el formulario
        if (validateForm(form)) {
            // Si es válido, ejecutar el callback
            if (typeof successCallback === 'function') {
                successCallback(form);
            } else {
                // Si no se proporciona callback, enviar el formulario
                form.submit();
            }
        }
    });
    
    // Validación en vivo al escribir
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(function(input) {
        input.addEventListener('input', function() {
            // Validar solo este campo
            if (this.checkValidity()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        });
    });
}

/**
 * Valida una cédula costarricense
 * @param {string} cedula Cédula a validar
 * @return {boolean} True si la cédula es válida, False si no
 */
function validarCedula(cedula) {
    // Eliminar espacios y guiones
    cedula = cedula.replace(/[\s-]/g, '');
    
    // Cédula nacional: 9 dígitos
    if (/^[0-9]{9}$/.test(cedula)) {
        return true;
    }
    
    // DIMEX: 12 dígitos
    if (/^[0-9]{12}$/.test(cedula)) {
        return true;
    }
    
    return false;
}

/**
 * Valida un número de teléfono costarricense
 * @param {string} telefono Teléfono a validar
 * @return {boolean} True si el teléfono es válido, False si no
 */
function validarTelefono(telefono) {
    // Eliminar espacios, guiones y paréntesis
    telefono = telefono.replace(/[\s\-()]/g, '');
    
    // Teléfono debe comenzar con 2, 4, 6, 7 o 8 y tener 8 dígitos
    return /^[2|4|6|7|8]\d{7}$/.test(telefono);
}

/**
 * Valida una dirección de correo electrónico
 * @param {string} email Correo electrónico a validar
 * @return {boolean} True si el correo es válido, False si no
 */
function validarEmail(email) {
    // Expresión regular para validar emails
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return emailRegex.test(email);
}

/**
 * Valida un precio o monto
 * @param {number|string} monto Monto a validar
 * @param {number} min Valor mínimo (opcional)
 * @param {number} max Valor máximo (opcional)
 * @return {boolean} True si el monto es válido, False si no
 */
function validarMonto(monto, min = 0, max = Number.MAX_SAFE_INTEGER) {
    // Convertir a número si es string
    if (typeof monto === 'string') {
        // Eliminar caracteres no numéricos excepto el punto decimal
        monto = monto.replace(/[^\d.]/g, '');
        monto = parseFloat(monto);
    }
    
    // Validar que sea un número y esté en el rango
    return !isNaN(monto) && monto >= min && monto <= max;
}

/**
 * Valida una fecha
 * @param {string|Date} fecha Fecha a validar
 * @param {string|Date} fechaMin Fecha mínima (opcional)
 * @param {string|Date} fechaMax Fecha máxima (opcional)
 * @return {boolean} True si la fecha es válida, False si no
 */
function validarFecha(fecha, fechaMin = null, fechaMax = null) {
    // Convertir a objeto Date si es string
    if (typeof fecha === 'string') {
        fecha = new Date(fecha);
    }
    
    // Verificar que sea una fecha válida
    if (isNaN(fecha.getTime())) {
        return false;
    }
    
    // Validar fecha mínima si se proporciona
    if (fechaMin) {
        if (typeof fechaMin === 'string') {
            fechaMin = new Date(fechaMin);
        }
        
        if (fecha < fechaMin) {
            return false;
        }
    }
    
    // Validar fecha máxima si se proporciona
    if (fechaMax) {
        if (typeof fechaMax === 'string') {
            fechaMax = new Date(fechaMax);
        }
        
        if (fecha > fechaMax) {
            return false;
        }
    }
    
    return true;
}

/**
 * Formatea un input mientras el usuario escribe
 * @param {HTMLInputElement} input Input a formatear
 * @param {string} tipo Tipo de formato (cedula, telefono, moneda)
 */
function formatInputOnType(input, tipo) {
    input.addEventListener('input', function() {
        let valor = this.value.replace(/\D/g, ''); // Eliminar no numéricos
        
        switch (tipo) {
            case 'cedula':
                // Formato: 0-0000-0000
                if (valor.length > 9) {
                    valor = valor.substring(0, 9);
                }
                
                if (valor.length > 4) {
                    valor = valor.substring(0, 1) + '-' + valor.substring(1, 5) + '-' + valor.substring(5);
                } else if (valor.length > 1) {
                    valor = valor.substring(0, 1) + '-' + valor.substring(1);
                }
                break;
                
            case 'telefono':
                // Formato: 0000-0000
                if (valor.length > 8) {
                    valor = valor.substring(0, 8);
                }
                
                if (valor.length > 4) {
                    valor = valor.substring(0, 4) + '-' + valor.substring(4);
                }
                break;
                
            case 'moneda':
                // Formato: 0,000.00
                valor = valor.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                valor = '₡ ' + valor;
                break;
        }
        
        this.value = valor;
    });
}