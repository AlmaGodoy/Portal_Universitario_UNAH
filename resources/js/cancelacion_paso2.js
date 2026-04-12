document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.cc2-container');
    if (!container) return;

    if (container.dataset.cc2Initialized === '1') return;
    container.dataset.cc2Initialized = '1';

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const urls = {
        baseUpload: container.dataset.urlBaseUpload || '',
        riesgoUpload: container.dataset.urlRiesgoUpload || '',
        flexUpload: container.dataset.urlFlexUpload || '',
        validar: container.dataset.urlValidar || '',
        eliminarTemplate: container.dataset.urlEliminarTemplate || ''
    };

    initUploadForms();
    initSaveAllButton({ csrf, urls });
    initDeleteButtons({ csrf, urlEliminarTemplate: urls.eliminarTemplate });
    initDeleteIdentityButton({ csrf, urlEliminarTemplate: urls.eliminarTemplate });
    initValidateButton({ csrf, urlValidar: urls.validar });
});

/* =========================================================
 * INICIALIZAR FORMULARIOS / ÁREAS DE CARGA
 * ========================================================= */
function initUploadForms() {
    const forms = document.querySelectorAll('.cc2-upload-form');

    forms.forEach((form) => {
        if (form.dataset.bound === '1') return;
        form.dataset.bound = '1';

        const uploadArea = form.querySelector('[data-upload-area]');
        const fileInput = form.querySelector('[data-file-input]');
        const fileName = form.querySelector('[data-file-name]');
        const refToggle = form.querySelector('[data-ref-toggle]');
        const refField = form.querySelector('[data-ref-field]');

        form.addEventListener('submit', (event) => {
            event.preventDefault();
        });

        if (uploadArea && fileInput && fileName) {
            setupUploadArea(uploadArea, fileInput, fileName, form);
        }

        if (refToggle && refField) {
            const syncReferenceField = () => {
                const active = refToggle.checked;
                refField.hidden = !active;

                const refInput = refField.querySelector('input[name="numero_folio"]');
                if (refInput) {
                    refInput.required = active;
                    if (!active) refInput.value = '';
                }
            };

            refToggle.addEventListener('change', () => {
                syncReferenceField();
                clearFormMsg(form);
                clearSaveAllMsg();
                clearGlobalAlert();
            });

            syncReferenceField();
        }

        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach((input) => {
            input.addEventListener('change', () => {
                clearFormMsg(form);
                clearSaveAllMsg();
                clearGlobalAlert();
            });

            input.addEventListener('input', () => {
                clearFormMsg(form);
                clearSaveAllMsg();
                clearGlobalAlert();
            });
        });
    });
}

/* =========================================================
 * BOTÓN ÚNICO: GUARDAR DOCUMENTOS
 * ========================================================= */
function initSaveAllButton({ csrf, urls }) {
    const button = document.getElementById('btn-guardar-documentos');

    if (!button) return;
    if (button.dataset.bound === '1') return;
    button.dataset.bound = '1';

    button.addEventListener('click', async () => {
        clearSaveAllMsg();
        clearAllFormMsgs();
        clearGlobalAlert();

        const forms = Array.from(document.querySelectorAll('.cc2-upload-form'));
        const selectedForms = forms.filter((form) => formHasSelectedFile(form));

        if (!selectedForms.length) {
            showSaveAllMsg('Debe seleccionar al menos un archivo antes de guardar.', 'error');
            return;
        }

        for (const form of selectedForms) {
            const validation = validateSelectedForm(form);
            if (!validation.ok) {
                showFormMsg(form, validation.message, 'error');
                showSaveAllMsg('Revise los archivos marcados antes de continuar.', 'error');
                return;
            }
        }

        toggleButtonLoading(button, true, 'Guardando documentos...');

        try {
            for (const form of selectedForms) {
                const result = await uploadSelectedForm(form, { csrf, urls });

                if (!result.ok) {
                    showFormMsg(form, result.message || 'No fue posible guardar este documento.', 'error');
                    showSaveAllMsg(result.message || 'No fue posible guardar uno de los documentos.', 'error');
                    showGlobalAlert(result.message || 'No fue posible guardar uno de los documentos.', 'error');
                    return;
                }

                showFormMsg(form, result.message || 'Documento guardado correctamente.', 'success');
            }

            showSaveAllMsg('Todos los documentos seleccionados se guardaron correctamente.', 'success');
            showGlobalAlert('Los documentos seleccionados se guardaron correctamente.', 'success');

            setTimeout(() => {
                window.location.reload();
            }, 900);

        } catch (error) {
            showSaveAllMsg('Ocurrió un problema al guardar los documentos. Intente nuevamente.', 'error');
            showGlobalAlert('Ocurrió un problema al guardar los documentos. Intente nuevamente.', 'error');
        } finally {
            toggleButtonLoading(button, false);
        }
    });
}

function formHasSelectedFile(form) {
    const kind = form.dataset.uploadKind;

    if (kind === 'identidad-unificada') {
        const fileInput = form.querySelector('input[name="archivo_identidad"]');
        return !!fileInput?.files?.[0];
    }

    const fileInput = form.querySelector('[data-file-input]');
    return !!fileInput?.files?.[0];
}

function validateSelectedForm(form) {
    const kind = form.dataset.uploadKind;

    if (kind === 'identidad-unificada') {
        const file = form.querySelector('input[name="archivo_identidad"]')?.files?.[0];
        if (!file) return { ok: true };

        const extension = getFileExtension(file.name);
        const sizeMb = file.size / 1024 / 1024;

        if (extension !== 'pdf') {
            return { ok: false, message: 'La tarjeta de identidad debe subirse en PDF.' };
        }

        if (sizeMb > 10) {
            return { ok: false, message: 'La tarjeta de identidad supera el tamaño máximo de 10 MB.' };
        }

        return { ok: true };
    }

    const fileInput = form.querySelector('[data-file-input]');
    const file = fileInput?.files?.[0];
    if (!file) return { ok: true };

    const extension = getFileExtension(file.name);
    const sizeMb = file.size / 1024 / 1024;
    const tipoDocumento = form.dataset.tipoDocumento || '';

    if (tipoDocumento === 'HISTORIAL_ACADEMICO') {
        if (extension !== 'pdf') {
            return { ok: false, message: 'El historial académico debe subirse únicamente en PDF.' };
        }

        if (sizeMb > 10) {
            return { ok: false, message: 'El historial académico supera el tamaño máximo de 10 MB.' };
        }

        return { ok: true };
    }

    if (!['pdf', 'jpg', 'jpeg', 'png'].includes(extension)) {
        return { ok: false, message: 'Solo se permiten archivos PDF, JPG, JPEG o PNG.' };
    }

    if (sizeMb > 10) {
        return { ok: false, message: 'El archivo supera el tamaño máximo de 10 MB.' };
    }

    if (form.dataset.uploadKind === 'riesgo') {
        const refToggle = form.querySelector('[data-ref-toggle]');
        const refInput = form.querySelector('input[name="numero_folio"]');

        if (refToggle?.checked) {
            const folio = refInput?.value?.trim() || '';
            if (!folio) {
                return { ok: false, message: 'Debe ingresar la referencia visible del documento.' };
            }
        }
    }

    return { ok: true };
}

/* =========================================================
 * SUBIDAS
 * ========================================================= */
async function uploadSelectedForm(form, { csrf, urls }) {
    const kind = form.dataset.uploadKind;

    if (kind === 'identidad-unificada') {
        return uploadIdentityUnified(form, { csrf, urlBaseUpload: urls.baseUpload });
    }

    if (kind === 'base') {
        return uploadBaseForm(form, { csrf, urlBaseUpload: urls.baseUpload });
    }

    if (kind === 'riesgo') {
        return uploadRiesgoForm(form, { csrf, urlRiesgoUpload: urls.riesgoUpload });
    }

    if (kind === 'flex') {
        return uploadFlexForm(form, { csrf, urlFlexUpload: urls.flexUpload });
    }

    return { ok: false, message: 'Tipo de carga no reconocido.' };
}

async function uploadIdentityUnified(form, { csrf, urlBaseUpload }) {
    const file = form.querySelector('input[name="archivo_identidad"]')?.files?.[0];
    if (!file) return { ok: true, message: 'Sin archivo para guardar.' };

    const existingIdentityButton = document.querySelector('[data-delete-identidad]');
    if (existingIdentityButton) {
        const idFrente = existingIdentityButton.dataset.idFrente || '';
        const idReverso = existingIdentityButton.dataset.idReverso || '';

        if (idFrente || idReverso) {
            return {
                ok: false,
                message: 'Ya existe una identidad cargada para este trámite. Quite la actual antes de volver a subir otra.'
            };
        }
    }

    const formDataFront = new FormData();
    formDataFront.append('archivo', file);
    formDataFront.append('tipo_documento', 'DNI_FRENTE');

    const frontResult = await postDocument(urlBaseUpload, formDataFront, csrf);
    if (!frontResult.ok) {
        return frontResult;
    }

    const formDataBack = new FormData();
    formDataBack.append('archivo', file);
    formDataBack.append('tipo_documento', 'DNI_REVERSO');

    const backResult = await postDocument(urlBaseUpload, formDataBack, csrf);
    if (!backResult.ok) {
        if (isDuplicateMessage(backResult.message)) {
            return {
                ok: false,
                message: 'El backend detectó el mismo PDF como documento repetido al intentar guardarlo como frente y reverso. Para que la identidad unificada funcione, también debes aplicar el ajuste del controller.'
            };
        }

        return backResult;
    }

    return {
        ok: true,
        message: 'Tarjeta de identidad guardada correctamente.'
    };
}

async function uploadBaseForm(form, { csrf, urlBaseUpload }) {
    const file = form.querySelector('[data-file-input]')?.files?.[0];
    const tipoDocumento = form.dataset.tipoDocumento || '';

    if (!file) return { ok: true, message: 'Sin archivo para guardar.' };

    if (documentAlreadyLoaded(form)) {
        return {
            ok: false,
            message: 'Ya existe un documento cargado en este apartado. Quite el actual antes de volver a subir otro.'
        };
    }

    const formData = new FormData();
    formData.append('archivo', file);
    formData.append('tipo_documento', tipoDocumento);

    return postDocument(urlBaseUpload, formData, csrf);
}

async function uploadRiesgoForm(form, { csrf, urlRiesgoUpload }) {
    const file = form.querySelector('[data-file-input]')?.files?.[0];
    const tipoDocumento = form.dataset.tipoDocumento || '';

    if (!file) return { ok: true, message: 'Sin archivo para guardar.' };

    if (documentAlreadyLoaded(form)) {
        return {
            ok: false,
            message: 'Ya existe un documento cargado en este apartado. Quite el actual antes de volver a subir otro.'
        };
    }

    const formData = new FormData();
    formData.append('archivo', file);
    formData.append('tipo_documento', tipoDocumento);

    const refToggle = form.querySelector('[data-ref-toggle]');
    const refInput = form.querySelector('input[name="numero_folio"]');
    const hasReference = !!refToggle?.checked;

    formData.append('tiene_referencia', hasReference ? '1' : '0');

    if (hasReference && refInput) {
        formData.append('numero_folio', refInput.value.trim());
    }

    return postDocument(urlRiesgoUpload, formData, csrf);
}

async function uploadFlexForm(form, { csrf, urlFlexUpload }) {
    const file = form.querySelector('[data-file-input]')?.files?.[0];
    const selectTipo = form.querySelector('select[name="tipo_documento"]');

    if (!file) return { ok: true, message: 'Sin archivo para guardar.' };

    if (documentAlreadyLoaded(form)) {
        return {
            ok: false,
            message: 'Ya existe un documento cargado en este apartado. Quite el actual antes de volver a subir otro.'
        };
    }

    const formData = new FormData();
    formData.append('archivo', file);
    formData.append('tipo_documento', selectTipo ? selectTipo.value : 'RESPALDO_CALAMIDAD');

    return postDocument(urlFlexUpload, formData, csrf);
}

function documentAlreadyLoaded(form) {
    const docCard = form.closest('.cc2-doc-card');
    if (!docCard) return false;

    const okState = docCard.querySelector('.cc2-doc-state__ok');
    return !!okState;
}

async function postDocument(url, formData, csrf) {
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
        const ok = response.ok && (typeof data.ok === 'undefined' || data.ok === true);

        if (!ok) {
            let message = 'No fue posible guardar el documento.';

            if (data?.mensaje) {
                message = normalizeMessage(data.mensaje);
            } else if (data?.message) {
                message = normalizeMessage(data.message);
            } else if (data?.errors) {
                message = flattenLaravelErrors(data.errors);
            }

            return { ok: false, message };
        }

        return {
            ok: true,
            message: data?.mensaje || data?.message || 'Documento guardado correctamente.'
        };
    } catch (error) {
        return {
            ok: false,
            message: 'Ocurrió un problema al procesar el documento.'
        };
    }
}

/* =========================================================
 * ELIMINAR DOCUMENTOS
 * ========================================================= */
function initDeleteButtons({ csrf, urlEliminarTemplate }) {
    const buttons = document.querySelectorAll('[data-delete-doc]');

    buttons.forEach((btn) => {
        if (btn.dataset.bound === '1') return;
        btn.dataset.bound = '1';

        btn.addEventListener('click', async () => {
            const idDocumento = btn.dataset.idDocumento;
            if (!idDocumento) return;

            const confirmed = window.confirm('¿Desea eliminar este documento cargado?');
            if (!confirmed) return;

            clearGlobalAlert();
            toggleButtonLoading(btn, true, 'Quitando...');

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
                const ok = response.ok && (typeof data.ok === 'undefined' || data.ok === true);

                if (!ok) {
                    const message = data?.mensaje || data?.message || 'No fue posible eliminar el documento.';
                    showGlobalAlert(normalizeMessage(message), 'error');
                    return;
                }

                showGlobalAlert(data?.mensaje || data?.message || 'Documento eliminado correctamente.', 'success');

                setTimeout(() => {
                    window.location.reload();
                }, 700);

            } catch (error) {
                showGlobalAlert('Ocurrió un problema al eliminar el documento.', 'error');
            } finally {
                toggleButtonLoading(btn, false);
            }
        });
    });
}

function initDeleteIdentityButton({ csrf, urlEliminarTemplate }) {
    const button = document.querySelector('[data-delete-identidad]');
    if (!button) return;
    if (button.dataset.bound === '1') return;
    button.dataset.bound = '1';

    button.addEventListener('click', async () => {
        const idFrente = button.dataset.idFrente || '';
        const idReverso = button.dataset.idReverso || '';

        if (!idFrente && !idReverso) return;

        const confirmed = window.confirm('¿Desea eliminar la tarjeta de identidad cargada?');
        if (!confirmed) return;

        clearGlobalAlert();
        toggleButtonLoading(button, true, 'Quitando...');

        try {
            if (idFrente) {
                const frontUrl = urlEliminarTemplate.replace('__ID__', idFrente);
                const frontResponse = await fetch(frontUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const frontData = await parseJsonSafe(frontResponse);
                const frontOk = frontResponse.ok && (typeof frontData.ok === 'undefined' || frontData.ok === true);

                if (!frontOk) {
                    const message = frontData?.mensaje || frontData?.message || 'No fue posible eliminar el frente de identidad.';
                    showGlobalAlert(normalizeMessage(message), 'error');
                    return;
                }
            }

            if (idReverso) {
                const backUrl = urlEliminarTemplate.replace('__ID__', idReverso);
                const backResponse = await fetch(backUrl, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const backData = await parseJsonSafe(backResponse);
                const backOk = backResponse.ok && (typeof backData.ok === 'undefined' || backData.ok === true);

                if (!backOk) {
                    const message = backData?.mensaje || backData?.message || 'No fue posible eliminar el reverso de identidad.';
                    showGlobalAlert(normalizeMessage(message), 'error');
                    return;
                }
            }

            showGlobalAlert('Tarjeta de identidad eliminada correctamente.', 'success');

            setTimeout(() => {
                window.location.reload();
            }, 700);

        } catch (error) {
            showGlobalAlert('Ocurrió un problema al eliminar la tarjeta de identidad.', 'error');
        } finally {
            toggleButtonLoading(button, false);
        }
    });
}

/* =========================================================
 * VALIDAR PASO 2
 * ========================================================= */
function initValidateButton({ csrf, urlValidar }) {
    const button = document.getElementById('btn-validar-paso2');

    if (!button) return;
    if (button.dataset.bound === '1') return;
    button.dataset.bound = '1';

    button.addEventListener('click', async () => {
        clearValidateMsg();
        clearGlobalAlert();
        toggleButtonLoading(button, true, 'Validando...');

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
            const ok = response.ok && (typeof data.ok === 'undefined' || data.ok === true);

            if (!ok) {
                let message = data?.mensaje || data?.message || 'No fue posible validar el paso 2.';

                if (Array.isArray(data?.faltantes) && data.faltantes.length) {
                    message += ' Faltan: ' + data.faltantes.join(', ') + '.';
                }

                showValidateMsg(normalizeMessage(message), 'error');
                return;
            }

            showValidateMsg(data?.mensaje || data?.message || 'Paso 2 validado correctamente.', 'success');

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
            showValidateMsg('Ocurrió un problema al validar el paso 2.', 'error');
        } finally {
            toggleButtonLoading(button, false);
        }
    });
}

/* =========================================================
 * ÁREAS DE CARGA / DRAG & DROP
 * ========================================================= */
function setupUploadArea(uploadArea, fileInput, fileNameDisplay, form) {
    if (uploadArea.dataset.bound === '1') return;
    uploadArea.dataset.bound = '1';

    uploadArea.style.cursor = 'pointer';

    uploadArea.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        fileInput.click();
    });

    fileInput.addEventListener('click', (event) => {
        event.stopPropagation();
    });

    fileInput.addEventListener('change', () => {
        updateSelectedFile(fileInput, fileNameDisplay, uploadArea);
        clearFormMsg(form);
        clearSaveAllMsg();
        clearGlobalAlert();
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
        clearFormMsg(form);
        clearSaveAllMsg();
        clearGlobalAlert();
    });
}

function updateSelectedFile(fileInput, fileNameDisplay, uploadArea) {
    if (!fileInput.files || !fileInput.files[0]) {
        fileNameDisplay.textContent = '';
        uploadArea.classList.remove('cc2-upload-area--selected');
        return;
    }

    const file = fileInput.files[0];
    const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);

    fileNameDisplay.textContent = `✅ ${file.name} (${fileSizeMB} MB)`;
    uploadArea.classList.add('cc2-upload-area--selected');
}

/* =========================================================
 * MENSAJES
 * ========================================================= */
function showFormMsg(form, text, type = 'error') {
    const box = form?.querySelector('[data-form-msg]');
    if (!box) return;

    box.className = 'cc2-form-msg';
    box.classList.add(type === 'success' ? 'cc2-form-msg--success' : 'cc2-form-msg--error');
    box.innerHTML = `<p>${escapeHtml(text)}</p>`;
}

function clearFormMsg(form) {
    const box = form?.querySelector('[data-form-msg]');
    if (!box) return;

    box.className = 'cc2-form-msg';
    box.innerHTML = '';
}

function clearAllFormMsgs() {
    document.querySelectorAll('.cc2-upload-form').forEach((form) => {
        clearFormMsg(form);
    });
}

function showSaveAllMsg(text, type = 'error') {
    const box = document.getElementById('cc2-upload-msg');
    if (!box) return;

    box.className = 'cc2-form-msg cc2-form-msg--final';
    box.classList.add(type === 'success' ? 'cc2-form-msg--success' : 'cc2-form-msg--error');
    box.innerHTML = `<p>${escapeHtml(text)}</p>`;
}

function clearSaveAllMsg() {
    const box = document.getElementById('cc2-upload-msg');
    if (!box) return;

    box.className = 'cc2-form-msg cc2-form-msg--final';
    box.innerHTML = '';
}

function showValidateMsg(text, type = 'error') {
    const box = document.getElementById('cc2-validate-msg');
    if (!box) return;

    box.className = 'cc2-form-msg cc2-form-msg--final';
    box.classList.add(type === 'success' ? 'cc2-form-msg--success' : 'cc2-form-msg--error');
    box.innerHTML = `<p>${escapeHtml(text)}</p>`;
}

function clearValidateMsg() {
    const box = document.getElementById('cc2-validate-msg');
    if (!box) return;

    box.className = 'cc2-form-msg cc2-form-msg--final';
    box.innerHTML = '';
}

function showGlobalAlert(text, type = 'error') {
    const box = document.getElementById('cc2-global-alert');
    if (!box) return;

    box.innerHTML = `
        <div class="cc2-alert ${type === 'success' ? 'cc2-alert--success' : 'cc2-alert--error'}">
            <span class="cc2-alert__icon">${type === 'success' ? '✅' : '⚠️'}</span>
            <div class="cc2-alert__content">
                <p>${escapeHtml(text)}</p>
            </div>
        </div>
    `;
}

function clearGlobalAlert() {
    const box = document.getElementById('cc2-global-alert');
    if (!box) return;
    box.innerHTML = '';
}

/* =========================================================
 * UTILIDADES
 * ========================================================= */
function toggleButtonLoading(button, state, loadingText = 'Procesando...') {
    if (!button) return;

    if (state) {
        button.disabled = true;
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = loadingText;
    } else {
        button.disabled = false;
        if (button.dataset.originalText) {
            button.innerHTML = button.dataset.originalText;
        }
    }
}

function getFileExtension(fileName) {
    return String(fileName).split('.').pop().toLowerCase();
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

function isDuplicateMessage(message) {
    const text = String(message || '').toLowerCase();
    return text.includes('ya fue subido anteriormente') || text.includes('duplic');
}

function escapeHtml(text) {
    return String(text)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}