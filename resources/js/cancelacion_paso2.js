document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.cc2-container');
    if (!container) return;

    if (container.dataset.cc2Initialized === '1') return;
    container.dataset.cc2Initialized = '1';

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const urlBaseUpload = container.dataset.urlBaseUpload || '';
    const urlRiesgoUpload = container.dataset.urlRiesgoUpload || '';
    const urlFlexUpload = container.dataset.urlFlexUpload || '';
    const urlValidar = container.dataset.urlValidar || '';
    const urlEliminarTemplate = container.dataset.urlEliminarTemplate || '';

    initUploadForms({
        csrf,
        urlBaseUpload,
        urlRiesgoUpload,
        urlFlexUpload
    });

    initDeleteButtons({
        csrf,
        urlEliminarTemplate
    });

    initValidateButton({
        csrf,
        urlValidar
    });
});

/* =========================================================
 * FORMULARIOS DE CARGA
 * ========================================================= */
function initUploadForms({ csrf, urlBaseUpload, urlRiesgoUpload, urlFlexUpload }) {
    const forms = document.querySelectorAll('.cc2-upload-form');

    forms.forEach((form) => {
        if (form.dataset.uploadFormBound === '1') return;
        form.dataset.uploadFormBound = '1';

        const uploadArea = form.querySelector('[data-upload-area]');
        const fileInput = form.querySelector('[data-file-input]');
        const fileName = form.querySelector('[data-file-name]');
        const formMsg = form.querySelector('[data-form-msg]');
        const refToggle = form.querySelector('[data-ref-toggle]');
        const refField = form.querySelector('[data-ref-field]');
        const tipoDocumentoFijo = form.dataset.tipoDocumento || '';

        if (uploadArea && fileInput && fileName) {
            setupUploadArea(uploadArea, fileInput, fileName);
        }

        if (refToggle && refField) {
            const syncRefFieldState = () => {
                const active = refToggle.checked;
                refField.hidden = !active;

                const input = refField.querySelector('input[name="numero_folio"]');
                if (input) {
                    input.required = active;
                    if (!active) input.value = '';
                }
            };

            refToggle.addEventListener('change', syncRefFieldState);
            syncRefFieldState();
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearFormMsg(formMsg);

            const uploadKind = form.dataset.uploadKind;
            const file = fileInput?.files?.[0];

            if (!file) {
                showFormMsg(formMsg, 'Debe seleccionar un archivo antes de continuar.', 'error');
                return;
            }

            const validation = validateFileByForm(form, file);
            if (!validation.ok) {
                showFormMsg(formMsg, validation.message, 'error');
                return;
            }

            const formData = new FormData();
            formData.append('archivo', file);

            let url = '';

            if (uploadKind === 'base') {
                url = urlBaseUpload;
                formData.append('tipo_documento', tipoDocumentoFijo);
            } else if (uploadKind === 'riesgo') {
                url = urlRiesgoUpload;
                formData.append('tipo_documento', tipoDocumentoFijo);

                const hasRef = refToggle ? refToggle.checked : false;
                formData.append('tiene_referencia', hasRef ? '1' : '0');

                const refInput = form.querySelector('input[name="numero_folio"]');
                if (hasRef && refInput) {
                    const folio = refInput.value.trim();
                    if (!folio) {
                        showFormMsg(formMsg, 'Debe ingresar la referencia visible del documento.', 'error');
                        return;
                    }
                    formData.append('numero_folio', folio);
                }
            } else if (uploadKind === 'flex') {
                url = urlFlexUpload;
                const selectTipo = form.querySelector('select[name="tipo_documento"]');
                formData.append('tipo_documento', selectTipo ? selectTipo.value : 'RESPALDO_CALAMIDAD');
            }

            toggleFormLoading(form, true);

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const data = await parseJsonSafe(response);

                if (!response.ok || !data.ok) {
                    let message = 'No fue posible guardar el documento.';

                    if (data?.mensaje) {
                        message = normalizeMessage(data.mensaje);
                    } else if (data?.errors) {
                        message = flattenLaravelErrors(data.errors);
                    }

                    showFormMsg(formMsg, message, 'error');
                    return;
                }

                showFormMsg(formMsg, data.mensaje || 'Documento guardado correctamente.', 'success');

                setTimeout(() => {
                    window.location.reload();
                }, 900);

            } catch (error) {
                showFormMsg(formMsg, 'Ocurrió un problema al procesar el documento. Intente nuevamente.', 'error');
            } finally {
                toggleFormLoading(form, false);
            }
        });
    });
}

/* =========================================================
 * ÁREAS DE CARGA
 * ========================================================= */
function setupUploadArea(uploadArea, fileInput, fileNameDisplay) {
    if (uploadArea.dataset.uploadAreaBound === '1') return;
    uploadArea.dataset.uploadAreaBound = '1';

    uploadArea.style.cursor = 'pointer';

    let pickerOpenLock = false;

    const openFilePicker = (event) => {
        event.preventDefault();
        event.stopPropagation();

        if (pickerOpenLock) return;
        pickerOpenLock = true;

        fileInput.click();

        setTimeout(() => {
            pickerOpenLock = false;
        }, 800);
    };

    uploadArea.addEventListener('click', openFilePicker);

    fileInput.addEventListener('click', (event) => {
        event.stopPropagation();
    });

    fileInput.addEventListener('change', () => {
        updateSelectedFile(fileInput, fileNameDisplay, uploadArea);
        pickerOpenLock = false;
    });

    uploadArea.addEventListener('dragover', (event) => {
        event.preventDefault();
        uploadArea.classList.add('cc2-upload-area--drag');
    });

    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('cc2-upload-area--drag');
    });

    uploadArea.addEventListener('drop', (event) => {
        event.preventDefault();
        event.stopPropagation();

        uploadArea.classList.remove('cc2-upload-area--drag');

        if (!event.dataTransfer.files.length) return;

        fileInput.files = event.dataTransfer.files;
        updateSelectedFile(fileInput, fileNameDisplay, uploadArea);
        pickerOpenLock = false;
    });
}

function updateSelectedFile(fileInput, fileNameDisplay, uploadArea) {
    if (!fileInput.files || !fileInput.files[0]) {
        fileNameDisplay.style.display = 'none';
        fileNameDisplay.textContent = '';
        uploadArea.classList.remove('cc2-upload-area--selected');
        return;
    }

    const file = fileInput.files[0];
    const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);

    fileNameDisplay.textContent = `✅ ${file.name} (${fileSizeMB} MB)`;
    fileNameDisplay.style.display = 'block';
    uploadArea.classList.add('cc2-upload-area--selected');
}

/* =========================================================
 * VALIDACIONES
 * ========================================================= */
function validateFileByForm(form, file) {
    const kind = form.dataset.uploadKind;
    const fixedType = form.dataset.tipoDocumento || '';
    const extension = getFileExtension(file.name);
    const sizeMb = file.size / 1024 / 1024;

    if (kind === 'base') {
        if (fixedType === 'HISTORIAL_ACADEMICO') {
            if (extension !== 'pdf') {
                return { ok: false, message: 'El historial académico debe subirse en PDF.' };
            }
            if (sizeMb > 10) {
                return { ok: false, message: 'El historial académico supera el tamaño máximo de 10 MB.' };
            }
            return { ok: true };
        }

        if (['DNI_FRENTE', 'DNI_REVERSO', 'FORMA_003'].includes(fixedType)) {
            if (!['pdf', 'jpg', 'jpeg', 'png'].includes(extension)) {
                return { ok: false, message: 'El archivo debe ser PDF, JPG o PNG.' };
            }
            if (sizeMb > 8) {
                return { ok: false, message: 'El archivo supera el tamaño máximo de 8 MB.' };
            }
            return { ok: true };
        }
    }

    if (kind === 'riesgo' || kind === 'flex') {
        if (!['pdf', 'jpg', 'jpeg', 'png'].includes(extension)) {
            return { ok: false, message: 'El archivo debe ser PDF, JPG o PNG.' };
        }
        if (sizeMb > 10) {
            return { ok: false, message: 'El archivo supera el tamaño máximo de 10 MB.' };
        }
        return { ok: true };
    }

    return { ok: true };
}

/* =========================================================
 * ELIMINAR DOCUMENTOS
 * ========================================================= */
function initDeleteButtons({ csrf, urlEliminarTemplate }) {
    const buttons = document.querySelectorAll('[data-delete-doc]');

    buttons.forEach((btn) => {
        if (btn.dataset.deleteBound === '1') return;
        btn.dataset.deleteBound = '1';

        btn.addEventListener('click', async () => {
            const idDocumento = btn.dataset.idDocumento;
            if (!idDocumento) return;

            const confirmed = window.confirm('¿Desea eliminar este documento cargado?');
            if (!confirmed) return;

            const url = urlEliminarTemplate.replace('__ID__', idDocumento);

            try {
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await parseJsonSafe(response);

                if (!response.ok || !data.ok) {
                    const message = data?.mensaje || 'No fue posible eliminar el documento.';
                    showGlobalAlert(normalizeMessage(message), 'error');
                    return;
                }

                showGlobalAlert(data.mensaje || 'Documento eliminado correctamente.', 'success');

                setTimeout(() => {
                    window.location.reload();
                }, 700);

            } catch (error) {
                showGlobalAlert('Ocurrió un problema al eliminar el documento.', 'error');
            }
        });
    });
}

/* =========================================================
 * VALIDAR PASO 2
 * ========================================================= */
function initValidateButton({ csrf, urlValidar }) {
    const button = document.getElementById('btn-validar-paso2');
    const msgBox = document.getElementById('cc2-validate-msg');

    if (!button || !msgBox) return;
    if (button.dataset.validateBound === '1') return;
    button.dataset.validateBound = '1';

    button.addEventListener('click', async () => {
        clearFormMsg(msgBox);
        button.disabled = true;
        button.classList.add('cc2-btn--loading');

        try {
            const response = await fetch(urlValidar, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await parseJsonSafe(response);

            if (!response.ok || !data.ok) {
                let message = data?.mensaje || 'No fue posible validar el paso 2.';

                if (Array.isArray(data?.faltantes) && data.faltantes.length) {
                    message += ' Faltan: ' + data.faltantes.join(', ') + '.';
                }

                showFormMsg(msgBox, normalizeMessage(message), 'error');
                return;
            }

            showFormMsg(msgBox, data.mensaje || 'Paso 2 validado correctamente.', 'success');

            const redirectUrl =
                data?.redirect ||
                data?.redirect_url ||
                data?.url ||
                data?.next_step_url ||
                '';

            if (redirectUrl) {
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 700);
            }

        } catch (error) {
            showFormMsg(msgBox, 'Ocurrió un problema al validar el paso 2.', 'error');
        } finally {
            button.disabled = false;
            button.classList.remove('cc2-btn--loading');
        }
    });
}

/* =========================================================
 * UTILIDADES
 * ========================================================= */
function showFormMsg(container, text, type = 'error') {
    if (!container) return;
    container.innerHTML = `<div class="cc2-inline-alert cc2-inline-alert--${type}">${text}</div>`;
}

function clearFormMsg(container) {
    if (!container) return;
    container.innerHTML = '';
}

function showGlobalAlert(text, type = 'error') {
    const box = document.getElementById('cc2-global-alert');
    if (!box) return;

    box.innerHTML = `
        <div class="cc2-alert ${type === 'success' ? 'cc2-alert--success' : 'cc2-alert--error'}">
            <span class="cc2-alert__icon">${type === 'success' ? '✅' : '⚠️'}</span>
            <div class="cc2-alert__content">
                <p>${text}</p>
            </div>
        </div>
    `;
}

function toggleFormLoading(form, state) {
    const submit = form.querySelector('button[type="submit"]');
    if (!submit) return;

    submit.disabled = state;
    submit.classList.toggle('cc2-btn--loading', state);

    if (state) {
        submit.dataset.originalText = submit.textContent;
        submit.textContent = 'Procesando...';
    } else if (submit.dataset.originalText) {
        submit.textContent = submit.dataset.originalText;
    }
}

function getFileExtension(fileName) {
    return fileName.split('.').pop().toLowerCase();
}

async function parseJsonSafe(response) {
    try {
        return await response.json();
    } catch {
        return {};
    }
}

function flattenLaravelErrors(errors) {
    const parts = [];
    Object.keys(errors || {}).forEach((key) => {
        const msgs = errors[key];
        if (Array.isArray(msgs)) {
            parts.push(...msgs);
        }
    });
    return parts.join(' ');
}

function normalizeMessage(message) {
    if (!message) return 'Ocurrió un problema al procesar la solicitud.';

    if (typeof message === 'object') {
        return flattenLaravelErrors(message);
    }

    return String(message)
        .replace(/^SQLSTATE\[.*?\]:\s*/i, '')
        .replace(/^ERROR:\s*/i, '')
        .trim();
}