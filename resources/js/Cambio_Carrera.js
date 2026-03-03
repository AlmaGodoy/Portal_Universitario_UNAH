(function () {
  const alertas = document.getElementById("alertas");
  const respuesta = document.getElementById("respuesta");
  const resultadoConsulta = document.getElementById("resultadoConsulta");

  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");

  function showAlert(type, msg) {
    alertas.innerHTML = `
      <div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${msg}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    `;
  }

  function printJSON(el, data) {
    if (!el) return;
    el.textContent = JSON.stringify(data, null, 2);
  }

  async function requestJSON(url, method, bodyObj = null) {
    const headers = {
      "Accept": "application/json",
    };

    // Si tus rutas API están en web.php con middleware web, puede pedir CSRF.
    if (csrf) headers["X-CSRF-TOKEN"] = csrf;

    if (bodyObj) headers["Content-Type"] = "application/json";

    const res = await fetch(url, {
      method,
      headers,
      body: bodyObj ? JSON.stringify(bodyObj) : null
    });

    const data = await res.json().catch(() => ({}));
    if (!res.ok) {
      const msg = data?.mensaje || data?.message || "Error en la solicitud";
      throw new Error(msg);
    }
    return data;
  }

  // CREAR
  document.getElementById("formCrear")?.addEventListener("submit", async (e) => {
    e.preventDefault();
    alertas.innerHTML = "";
    printJSON(respuesta, "");

    const fd = new FormData(e.target);
    const payload = {
      id_persona: Number(fd.get("id_persona")),
      id_calendario: Number(fd.get("id_calendario")),
      id_carrera_destino: Number(fd.get("id_carrera_destino")),
      direccion: String(fd.get("direccion") || "").trim(),
    };

    try {
      const data = await requestJSON("/api/cambio-carrera/crear", "POST", payload);
      showAlert("success", "Solicitud creada correctamente.");
      printJSON(respuesta, data);
      e.target.reset();
    } catch (err) {
      showAlert("danger", err.message);
    }
  });

  // CONSULTAR
  document.getElementById("formConsultar")?.addEventListener("submit", async (e) => {
    e.preventDefault();
    alertas.innerHTML = "";
    printJSON(resultadoConsulta, "");

    const fd = new FormData(e.target);
    const codigo = Number(fd.get("codigo"));

    try {
      const data = await requestJSON(`/api/cambio-carrera/ver/${codigo}`, "GET");
      showAlert("info", "Consulta realizada.");
      printJSON(resultadoConsulta, data);
    } catch (err) {
      showAlert("danger", err.message);
    }
  });

  // ACTUALIZAR ESTADO
  document.getElementById("formEstado")?.addEventListener("submit", async (e) => {
    e.preventDefault();
    alertas.innerHTML = "";
    printJSON(respuesta, "");

    const fd = new FormData(e.target);
    const id_tramite = Number(fd.get("id_tramite"));
    const estado = String(fd.get("estado") || "").trim();

    try {
      const data = await requestJSON(`/api/cambio-carrera/estado/${id_tramite}`, "PUT", { estado });
      showAlert("success", "Estado actualizado.");
      printJSON(respuesta, data);
      e.target.reset();
    } catch (err) {
      showAlert("danger", err.message);
    }
  });

  // CANCELAR
  document.getElementById("formCancelar")?.addEventListener("submit", async (e) => {
    e.preventDefault();
    alertas.innerHTML = "";
    printJSON(respuesta, "");

    const fd = new FormData(e.target);
    const id_tramite = Number(fd.get("id_tramite"));

    if (!confirm(`¿Seguro que deseas cancelar el trámite ${id_tramite}?`)) return;

    try {
      const data = await requestJSON(`/api/cambio-carrera/eliminar/${id_tramite}`, "DELETE");
      showAlert("warning", "Trámite cancelado/inactivado.");
      printJSON(respuesta, data);
      e.target.reset();
    } catch (err) {
      showAlert("danger", err.message);
    }
  });

})();