/* ═══════════════════════════════════════════
   PumaGestión – dashboard.js
   Ubicación: resources/js/dashboard.js
   Importar en app.js: import './dashboard.js'
═══════════════════════════════════════════ */

// ── Storage ──────────────────────────────────
function getTramites()   { return JSON.parse(localStorage.getItem('unah_tramites') || '[]'); }
function getEventos()    { return JSON.parse(localStorage.getItem('unah_eventos')  || '[]'); }
function saveTramites(d) { localStorage.setItem('unah_tramites', JSON.stringify(d)); }
function saveEventos(d)  { localStorage.setItem('unah_eventos',  JSON.stringify(d)); }
function hoy()           { return new Date().toISOString().split('T')[0]; }

// ── Modales con Bootstrap nativo (sin jQuery) ─
function showModal(id)   { bootstrap.Modal.getOrCreateInstance(document.getElementById(id)).show(); }
function hideModal(id)   { bootstrap.Modal.getInstance(document.getElementById(id))?.hide(); }

const MESES = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
let barChart, pieChart, lineChart;

// ════════════════════════════════════════════
//  GRÁFICAS
// ════════════════════════════════════════════
function initCharts() {
  const bCtx = document.getElementById('barChart').getContext('2d');
  barChart = new Chart(bCtx, {
    type: 'bar',
    data: { labels: MESES, datasets: [
      { label:'Ingresados', data: new Array(12).fill(0), backgroundColor:'rgba(30,94,166,.82)', borderRadius:4, borderSkipped:false },
      { label:'Pendientes', data: new Array(12).fill(0), backgroundColor:'rgba(240,184,0,.85)',  borderRadius:4, borderSkipped:false },
      { label:'Aprobados',  data: new Array(12).fill(0), borderColor:'#28a745', backgroundColor:'rgba(40,167,69,.08)', type:'line', borderWidth:2, pointRadius:3, fill:true, tension:.4 }
    ]},
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true, grid:{ color:'rgba(0,0,0,.05)' }, ticks:{ font:{ size:9 }, precision:0 } }, x:{ grid:{ display:false }, ticks:{ font:{ size:9 } } } } }
  });

  const pCtx = document.getElementById('pieChart').getContext('2d');
  pieChart = new Chart(pCtx, {
    type: 'doughnut',
    data: { labels:['Pendiente','Revisión','Aprobado'], datasets:[{ data:[0,0,0], backgroundColor:['#f5c518','#1a3a6b','#28a745'], borderWidth:3, borderColor:'#fff', hoverOffset:6 }] },
    options: { responsive:false, plugins:{ legend:{ display:false }, tooltip:{ callbacks:{ label: c=>' '+c.label+': '+c.parsed } } }, cutout:'60%' }
  });

  const lCtx = document.getElementById('lineChart').getContext('2d');
  lineChart = new Chart(lCtx, {
    type: 'line',
    data: { labels: MESES, datasets: [
      { label:'Cambio Carrera', data: new Array(12).fill(0), borderColor:'#0e2348', backgroundColor:'transparent', borderWidth:2, pointRadius:3, tension:.4 },
      { label:'Cancelación',   data: new Array(12).fill(0), borderColor:'#17a2b8', backgroundColor:'transparent', borderWidth:2, pointRadius:3, tension:.4 },
      { label:'Otros',         data: new Array(12).fill(0), borderColor:'#28a745', backgroundColor:'transparent', borderWidth:2, pointRadius:3, tension:.4 }
    ]},
    options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true, grid:{ color:'rgba(0,0,0,.05)' }, ticks:{ font:{ size:9 }, precision:0 } }, x:{ grid:{ display:false }, ticks:{ font:{ size:9 } } } } }
  });
}

// ════════════════════════════════════════════
//  DASHBOARD
// ════════════════════════════════════════════
function actualizarDashboard() {
  const tramites = getTramites();
  const eventos  = getEventos();

  const pendientes = tramites.filter(t => t.estado === 'Pendiente').length;
  const aprobados  = tramites.filter(t => t.estado === 'Aprobado').length;
  const revision   = tramites.filter(t => t.estado === 'Revisión').length;
  const ests = tramites.filter(t => t.rol === 'Estudiante').length;
  const secs = tramites.filter(t => t.rol === 'Secretario').length;

  document.getElementById('cnt-tramites').textContent    = tramites.length;
  document.getElementById('cnt-pendientes').textContent  = pendientes;
  document.getElementById('cnt-aprobados').textContent   = aprobados;
  document.getElementById('cnt-estudiantes').textContent = ests;
  document.getElementById('cnt-secretarios').textContent = secs;

  document.getElementById('footer-tramites').textContent    = tramites.length ? `Último: ${tramites[tramites.length-1].fecha}` : 'Sin trámites aún';
  document.getElementById('footer-pendientes').textContent  = pendientes ? `${pendientes} esperando revisión` : 'Sin solicitudes aún';
  document.getElementById('footer-aprobados').textContent   = aprobados  ? `${aprobados} resueltos`           : 'Sin aprobados aún';
  document.getElementById('footer-estudiantes').textContent = ests ? `${ests} trámites de estudiantes`        : 'Sin registros aún';
  document.getElementById('footer-secretarios').textContent = secs ? `${secs} trámites de secretarios`        : 'Sin registros aún';
  document.getElementById('notif-count').textContent = pendientes;

  // ── Datos gráficas ──
  const barIng  = new Array(12).fill(0), barPend = new Array(12).fill(0), barApr  = new Array(12).fill(0);
  const lineCam = new Array(12).fill(0), lineCan = new Array(12).fill(0), lineOtr = new Array(12).fill(0);

  tramites.forEach(t => {
    const d = new Date(t.fecha);
    const m = isNaN(d) ? 0 : d.getMonth();
    barIng[m]++;
    if (t.estado === 'Pendiente') barPend[m]++;
    if (t.estado === 'Aprobado')  barApr[m]++;
    if      (t.tipo === 'Cambio de Carrera')       lineCam[m]++;
    else if (t.tipo === 'Cancelación Excepcional') lineCan[m]++;
    else                                           lineOtr[m]++;
  });

  barChart.data.datasets[0].data = barIng;
  barChart.data.datasets[1].data = barPend;
  barChart.data.datasets[2].data = barApr;
  barChart.update();
  lineChart.data.datasets[0].data = lineCam;
  lineChart.data.datasets[1].data = lineCan;
  lineChart.data.datasets[2].data = lineOtr;
  lineChart.update();
  pieChart.data.datasets[0].data = [pendientes, revision, aprobados];
  pieChart.update();
  document.getElementById('pl-pend').textContent = `Pendiente: ${pendientes}`;
  document.getElementById('pl-rev').textContent  = `Revisión: ${revision}`;
  document.getElementById('pl-apr').textContent  = `Aprobado: ${aprobados}`;

  const sinDatos = tramites.length === 0;
  document.getElementById('bar-wrap').style.display    = sinDatos ? 'none' : 'block';
  document.getElementById('bar-empty').style.display   = sinDatos ? 'flex' : 'none';
  document.getElementById('pie-wrap').style.display    = sinDatos ? 'none' : 'block';
  document.getElementById('pie-empty').style.display   = sinDatos ? 'flex' : 'none';
  document.getElementById('line-wrap').style.display   = sinDatos ? 'none' : 'block';
  document.getElementById('line-empty').style.display  = sinDatos ? 'flex' : 'none';

  // ── Tabla ──
  const wrap = document.getElementById('tabla-wrap');
  if (tramites.length === 0) {
    wrap.innerHTML = `<div class="empty-state" style="padding:50px 20px;"><i class="fas fa-inbox" style="font-size:3rem;color:#ddd;"></i><p style="margin-top:10px;color:#bbb;font-size:.88rem;">No hay trámites registrados aún.<br>Haz clic en <strong>+ Nuevo</strong> para agregar el primero.</p></div>`;
  } else {
    let rows = '';
    [...tramites].reverse().forEach((t, i) => {
      const idx = tramites.length - 1 - i;
      let badgeE = '', badgeA = '';
      if (t.estado === 'Revisión')  badgeE = `<span class="estado-badge eb-verde"><i class="fas fa-sync-alt"></i> Revisión</span>`;
      if (t.estado === 'Pendiente') badgeE = `<span class="estado-badge eb-amarillo"><i class="fas fa-clock"></i> Pendiente</span>`;
      if (t.estado === 'Aprobado')  badgeE = `<span class="estado-badge eb-verde"><i class="fas fa-check-circle"></i> Aprobado</span>`;
      if (t.estado === 'Aprobado')  badgeA = `<span class="estado-badge eb-azul mr-1">APROBADO</span>`;
      if (t.estado === 'Pendiente') badgeA = `<span class="estado-badge eb-amarillo mr-1">PENDIENTE</span>`;
      if (t.estado === 'Revisión')  badgeA = `<span class="estado-badge eb-verde mr-1">REVISIÓN</span>`;
      rows += `<tr>
        <td><i class="fas fa-graduation-cap mr-1" style="color:var(--blue2);"></i>${t.carrera}</td>
        <td><i class="fas fa-arrow-up mr-1" style="color:var(--gold2);font-size:.7rem;"></i>${t.descripcion}</td>
        <td>${badgeE}</td><td>${t.fecha}</td>
        <td>${badgeA}<i class="fas fa-user-circle" style="color:var(--blue2);margin-right:3px;"></i>${t.solicitante}
          <button onclick="eliminarTramite(${idx})" class="btn btn-sm ml-1" style="color:#e55;border:none;background:transparent;padding:1px 4px;"><i class="fas fa-times"></i></button>
        </td></tr>`;
    });
    wrap.innerHTML = `<table class="table tramites-table mb-0"><thead><tr><th>Carrera</th><th>Trámite</th><th>Estado</th><th>Fecha</th><th>Solicitante</th></tr></thead><tbody>${rows}</tbody></table>`;
  }

  // ── Eventos ──
  const evWrap = document.getElementById('eventos-wrap');
  if (eventos.length === 0) {
    evWrap.innerHTML = `<div class="empty-state" style="padding:40px 20px;"><i class="fas fa-calendar-times" style="font-size:2.5rem;color:#ddd;"></i><p style="margin-top:10px;color:#bbb;font-size:.84rem;">No hay eventos registrados.<br>Haz clic en <strong>+ Agregar</strong>.</p></div>`;
  } else {
    let evHtml = '';
    eventos.forEach((ev, i) => {
      const colorText = ev.color === '#e8b800' ? '#000' : '#fff';
      evHtml += `<div class="ev-item"><div class="ev-time-box" style="background:${ev.color};color:${colorText};"><div class="ev-t">${ev.hora}</div><div class="ev-d">${ev.fecha}</div></div><div style="flex:1;"><div class="ev-ti">${ev.titulo}</div><div class="ev-lo"><i class="fas fa-map-marker-alt mr-1"></i>${ev.lugar || 'Sin ubicación'}</div></div><button onclick="eliminarEvento(${i})" style="border:none;background:transparent;color:#ddd;font-size:.8rem;cursor:pointer;"><i class="fas fa-times"></i></button></div>`;
    });
    evWrap.innerHTML = evHtml;
  }
}

// ════════════════════════════════════════════
//  TRÁMITES
// ════════════════════════════════════════════
window.abrirModal = function() {
  ['f-tipo','f-carrera','f-descripcion','f-estado','f-solicitante','f-rol','f-cuenta'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('f-fecha').value = hoy();
  document.getElementById('modal-error').style.display = 'none';
  showModal('modalTramite');
};

window.guardarTramite = function() {
  const tipo        = document.getElementById('f-tipo').value.trim();
  const carrera     = document.getElementById('f-carrera').value.trim();
  const descripcion = document.getElementById('f-descripcion').value.trim();
  const estado      = document.getElementById('f-estado').value;
  const solicitante = document.getElementById('f-solicitante').value.trim();
  const rol         = document.getElementById('f-rol').value;
  const fecha       = document.getElementById('f-fecha').value;
  const cuenta      = document.getElementById('f-cuenta').value.trim();
  const err = document.getElementById('modal-error');
  if (!tipo || !carrera || !descripcion || !solicitante) {
    err.style.display = 'block';
    err.textContent   = 'Por favor completa todos los campos obligatorios (*)';
    return;
  }
  err.style.display = 'none';
  const tramites = getTramites();
  tramites.push({ tipo, carrera, descripcion, estado, solicitante, rol, fecha, cuenta, creadoEn: new Date().toISOString() });
  saveTramites(tramites);
  actualizarDashboard();
  hideModal('modalTramite');
};

window.eliminarTramite = function(idx) {
  if (!confirm('¿Eliminar este trámite?')) return;
  const tramites = getTramites();
  tramites.splice(idx, 1);
  saveTramites(tramites);
  actualizarDashboard();
};

window.limpiarTodo = function() {
  if (!confirm('⚠️ ¿Borrar TODOS los trámites? Esta acción no se puede deshacer.')) return;
  saveTramites([]);
  actualizarDashboard();
};

// ════════════════════════════════════════════
//  EVENTOS
// ════════════════════════════════════════════
window.abrirModalEvento = function() {
  document.getElementById('ev-titulo').value = '';
  document.getElementById('ev-lugar').value  = '';
  document.getElementById('ev-hora').value   = '10:00';
  document.getElementById('ev-fecha').value  = hoy();
  document.getElementById('ev-color').value  = '#1a3a6b';
  showModal('modalEvento');
};

window.guardarEvento = function() {
  const titulo = document.getElementById('ev-titulo').value.trim();
  const hora   = document.getElementById('ev-hora').value;
  const fecha  = document.getElementById('ev-fecha').value;
  const lugar  = document.getElementById('ev-lugar').value.trim();
  const color  = document.getElementById('ev-color').value;
  if (!titulo) { alert('El nombre del evento es obligatorio'); return; }
  const [h, m] = hora.split(':');
  const hNum = parseInt(h);
  const ampm = hNum >= 12 ? 'PM' : 'AM';
  const h12  = hNum > 12 ? hNum - 12 : (hNum === 0 ? 12 : hNum);
  const horaFmt = `${h12}:${m} ${ampm}`;
  const d = new Date(fecha + 'T00:00:00');
  const fechaFmt = isNaN(d) ? fecha : `${d.getDate().toString().padStart(2,'0')}.${(d.getMonth()+1).toString().padStart(2,'0')} – ${d.getFullYear().toString().slice(-2)}`;
  const eventos = getEventos();
  eventos.push({ titulo, hora: horaFmt, fecha: fechaFmt, lugar, color });
  saveEventos(eventos);
  actualizarDashboard();
  hideModal('modalEvento');
};

window.eliminarEvento = function(idx) {
  if (!confirm('¿Eliminar este evento?')) return;
  const eventos = getEventos();
  eventos.splice(idx, 1);
  saveEventos(eventos);
  actualizarDashboard();
};

// ════════════════════════════════════════════
//  EXPORTAR
// ════════════════════════════════════════════
window.exportarDatos = function() {
  const tramites = getTramites();
  if (tramites.length === 0) { alert('No hay datos para exportar'); return; }
  const header = 'Tipo,Carrera,Descripción,Estado,Solicitante,Rol,Fecha,Cuenta\n';
  const rows   = tramites.map(t => `"${t.tipo}","${t.carrera}","${t.descripcion}","${t.estado}","${t.solicitante}","${t.rol}","${t.fecha}","${t.cuenta}"`).join('\n');
  const blob   = new Blob([header + rows], { type: 'text/csv;charset=utf-8;' });
  const url    = URL.createObjectURL(blob);
  const a      = document.createElement('a'); a.href = url; a.download = 'tramites_pumagestion.csv'; a.click();
  URL.revokeObjectURL(url);
};

// ════════════════════════════════════════════
//  INIT
// ════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
  initCharts();
  actualizarDashboard();
  document.getElementById('f-fecha').value = hoy();
});
