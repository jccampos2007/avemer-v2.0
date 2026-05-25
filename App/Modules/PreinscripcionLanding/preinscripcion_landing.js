// preinscripcion_landing.js — Modern UI, vanilla JS

const API = BASE_URL_JS + 'preinscripcionlanding/';

// --- Estado global ---
let state = {
    modo: '',
    typeId: 0,
    alumno: null,
    ofertaId: null,
    ofertas: []
};

const MODO_MAP = {
    taller:   { typeId: 1, label: 'Taller' },
    diplomado:{ typeId: 2, label: 'Diplomado' },
    evento:   { typeId: 3, label: 'Evento' },
    maestria: { typeId: 4, label: 'Maestría' }
};

// --- Utilidades ---

function $(id) { return document.getElementById(id); }

function show(el) { if (typeof el === 'string') el = $(el); el.classList.remove('hidden'); }
function hide(el) { if (typeof el === 'string') el = $(el); el.classList.add('hidden'); }

function openModal(id) {
    const m = $(id);
    m.classList.remove('opacity-0', 'pointer-events-none');
    m.querySelector('.modal-scale').classList.remove('scale-95');
    m.querySelector('.modal-scale').classList.add('scale-100');
}

function closeModal(id) {
    const m = $(id);
    m.classList.add('opacity-0', 'pointer-events-none');
    m.querySelector('.modal-scale').classList.add('scale-95');
    m.querySelector('.modal-scale').classList.remove('scale-100');
}

function showToast(msg, type) {
    const t = $('toast');
    const icon = $('toast-icon');
    const bg = type === 'success' ? 'bg-emerald-600' : type === 'error' ? 'bg-red-600' : 'bg-slate-900';

    t.querySelector('div').className = 'flex items-center gap-2.5 px-5 py-3 rounded-2xl shadow-xl text-white text-sm font-medium ' + bg;
    icon.setAttribute('data-lucide', type === 'success' ? 'check-circle-2' : type === 'error' ? 'alert-circle' : 'info');
    $('toast-message').textContent = msg;

    t.classList.remove('opacity-0', 'translate-y-2', 'pointer-events-none');
    t.classList.add('opacity-100', 'translate-y-0');
    lucide.createIcons();

    clearTimeout(t._timer);
    t._timer = setTimeout(() => {
        t.classList.add('opacity-0', 'translate-y-2', 'pointer-events-none');
        t.classList.remove('opacity-100', 'translate-y-0');
    }, 4000);
}

function postJSON(url, data, onSuccess, onError) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    xhr.onload = function () {
        if (xhr.status !== 200) {
            onError('Error del servidor (código ' + xhr.status + '): ' + (xhr.responseText || 'sin respuesta'));
            return;
        }
        if (!xhr.responseText || xhr.responseText.trim() === '') {
            onError('El servidor no devolvió datos (respuesta vacía).');
            return;
        }
        try {
            const json = JSON.parse(xhr.responseText);
            onSuccess(json);
        } catch (e) {
            console.error('Error parsing response:', xhr.responseText);
            onError('Error al procesar la respuesta del servidor: ' + xhr.responseText.substring(0, 200));
        }
    };
    xhr.onerror = function () {
        console.error('Network error');
        onError('Error de conexión al servidor.');
    };
    const params = new URLSearchParams();
    for (const k in data) params.append(k, data[k]);
    xhr.send(params.toString());
}

// --- Inicialización ---

document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(location.search);
    const modo = params.get('modo');

    if (!modo || !MODO_MAP[modo]) {
        showToast('Modo no válido. Usa ?modo=taller|diplomado|evento|maestria', 'error');
        return;
    }

    state.modo = modo;
    state.typeId = MODO_MAP[modo].typeId;

    $('oferta').textContent = 'Pre-inscripción: ' + modo;
    document.title = 'Pre-inscripción: ' + modo + ' - Sistema de Registro';
    $('tipo-label').textContent = MODO_MAP[modo].label;

    lucide.createIcons();

    // Cargar ofertas como paso inicial
    hide('empty-state');
    show('step1-section');
    loadOfertas();
});

// --- Búsqueda de alumno ---

function searchStudent(e) {
    e.preventDefault();
    const ci = $('search-ci-input').value.trim();
    const errEl = $('search-error');

    if (!ci) {
        errEl.textContent = 'Ingresa un CI/Pasaporte.';
        errEl.classList.remove('hidden');
        return false;
    }
    errEl.classList.add('hidden');
    $('form-search-student').querySelector('button[type=submit]').disabled = true;

    postJSON(API + 'search_alumno', { ci_pasapote: ci },
        function (res) {
            if (res.success && res.found) {
                closeModal('modal-search-student');
                state.alumno = res.alumno;
                showStudentCard(res.alumno);
            } else if (res.success && !res.found) {
                closeModal('modal-search-student');
                $('new-ci').value = ci;
                openModal('modal-create-student');
                $('new-primer-nombre').focus();
            } else {
                showToast(res.message || 'Error al buscar alumno.', 'error');
            }
            $('form-search-student').querySelector('button[type=submit]').disabled = false;
        },
        function (msg) {
            showToast(msg, 'error');
            $('form-search-student').querySelector('button[type=submit]').disabled = false;
        }
    );

    return false;
}

// --- Mostrar tarjeta del alumno ---

function showStudentCard(a) {
    const nombre = [a.primer_nombre, a.segundo_nombre || '', a.primer_apellido, a.segundo_apellido || '']
        .filter(Boolean).join(' ');

    $('student-name').textContent = nombre || '—';
    $('student-ci').textContent = a.ci_pasapote || '—';
    $('student-email').textContent = a.correo || '—';
    $('student-phone').textContent = a.tlf_celular || 'No registrado';

    hide('empty-state');
    show('step2-section');
    show('student-card');
    show('alumno-verified-badge');
    $('step-badge').textContent = 'Paso 2 de 2';

    // Habilitar botón de envío
    const btn = $('btn-submit');
    btn.disabled = false;
    btn.classList.remove('bg-slate-300', 'text-slate-500', 'cursor-not-allowed');
    btn.classList.add('bg-indigo-600', 'text-white', 'hover:bg-indigo-700', 'cursor-pointer', 'shadow-lg', 'shadow-indigo-100');

    lucide.createIcons();
}

// --- Crear alumno ---

function createStudent(e) {
    e.preventDefault();
    const btn = $('form-create-student').querySelector('button[type=submit]');
    btn.disabled = true;

    const data = {
        new_ci_pasapote:       $('new-ci').value.trim(),
        new_primer_nombre:     $('new-primer-nombre').value.trim(),
        new_segundo_nombre:    $('new-segundo-nombre').value.trim(),
        new_primer_apellido:   $('new-primer-apellido').value.trim(),
        new_segundo_apellido:  $('new-segundo-apellido').value.trim(),
        new_correo:            $('new-correo').value.trim(),
        new_tlf_celular:       $('new-tlf-celular').value.trim(),
        new_tlf_habitacion:    $('new-tlf-habitacion').value.trim()
    };

    if (!data.new_ci_pasapote || !data.new_primer_nombre || !data.new_primer_apellido) {
        showToast('Completa los campos obligatorios.', 'error');
        btn.disabled = false;
        return false;
    }

    postJSON(API + 'create_alumno', data,
        function (res) {
            if (res.success) {
                closeModal('modal-create-student');
                state.alumno = res.alumno;
                showStudentCard(res.alumno);
                showToast(res.message, 'success');
            } else {
                showToast(res.message || 'Error al crear alumno.', 'error');
            }
            btn.disabled = false;
        },
        function (msg) {
            showToast(msg, 'error');
            btn.disabled = false;
        }
    );

    return false;
}

// --- Cargar ofertas ---

function loadOfertas() {
    const list = $('ofertas-list');
    list.innerHTML = `
        <div class="text-center py-8 text-slate-400">
            <i data-lucide="loader-2" class="w-6 h-6 mx-auto mb-2 animate-spin"></i>
            <p class="text-sm">Cargando ofertas disponibles...</p>
        </div>
    `;
    lucide.createIcons();
    show('step1-section');
    hide('empty-state');

    postJSON(API + 'get_ofertas_abiertas', { typeId: state.typeId },
        function (res) {
            list.innerHTML = '';
            if (res.success && res.data.length > 0) {
                state.ofertas = res.data;
                res.data.forEach(o => {
                    const card = document.createElement('div');
                    card.className = 'workshop-card group relative bg-white border border-slate-200 hover:border-indigo-200 rounded-2xl p-5 cursor-pointer transition-all duration-300 hover:shadow-md hover:shadow-slate-100 flex items-start gap-4';
                    card.dataset.id = o.id;

                    const fecha = o.fecha_inicio || o.fecha || '';
                    const fechaFin = o.fecha_fin || '';

                    card.innerHTML = `
                        <div class="flex items-center justify-center mt-1">
                            <div class="radio-outer w-5 h-5 rounded-full border border-slate-300 group-hover:border-indigo-400 flex items-center justify-center transition-all bg-white">
                                <div class="radio-inner w-2.5 h-2.5 rounded-full bg-indigo-600 scale-0"></div>
                            </div>
                        </div>
                        <div class="flex-1 space-y-3">
                            <div>
                                <span class="inline-block bg-slate-100 text-slate-600 text-[10px] font-bold tracking-wider px-2 py-0.5 rounded uppercase mb-1.5">${o.numero || ''}</span>
                                <h3 class="font-bold text-slate-800 text-sm md:text-base leading-snug group-hover:text-indigo-950 transition-colors">${o.nombre || ''}</h3>
                            </div>
                            <div class="flex flex-wrap gap-y-2 gap-x-4 text-xs text-slate-500">
                                <span class="flex items-center gap-1.5">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-400"></i>
                                    ${o.sede_nombre || 'N/A'}
                                </span>
                                ${fecha ? `
                                <span class="flex items-center gap-1.5 font-medium text-slate-700 bg-slate-100/60 px-2 py-0.5 rounded">
                                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                                    ${fecha}${fechaFin ? ' al ' + fechaFin : ''}
                                </span>
                                ` : ''}
                            </div>
                        </div>
                    `;

                    card.addEventListener('click', function () {
                        selectOferta(this.dataset.id);
                    });

                    list.appendChild(card);
                });
                lucide.createIcons();
            } else {
                list.innerHTML = `
                    <div class="text-center py-8 text-slate-400 border border-dashed border-slate-200 rounded-2xl">
                        <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2"></i>
                        <p class="text-sm">No hay ${MODO_MAP[state.modo].label}s disponibles en este momento.</p>
                    </div>
                `;
                lucide.createIcons();
            }
        },
        function () {
            list.innerHTML = `
                <div class="text-center py-8 text-red-400 border border-dashed border-red-200 rounded-2xl">
                    <i data-lucide="alert-circle" class="w-8 h-8 mx-auto mb-2"></i>
                    <p class="text-sm">Error al cargar ofertas. Intenta recargar la página.</p>
                </div>
            `;
            lucide.createIcons();
        }
    );
}

// --- Seleccionar oferta ---

function selectOferta(id) {
    state.ofertaId = id;

    document.querySelectorAll('.workshop-card').forEach(card => {
        card.classList.remove('border-indigo-500', 'bg-indigo-50/20', 'shadow-md', 'shadow-indigo-50/40');
        card.classList.add('border-slate-200', 'bg-white');

        const inner = card.querySelector('.radio-inner');
        if (inner) { inner.classList.remove('scale-100'); inner.classList.add('scale-0'); }
        const outer = card.querySelector('.radio-outer');
        if (outer) { outer.classList.remove('border-indigo-600', 'bg-indigo-50'); outer.classList.add('border-slate-300', 'bg-white'); }
    });

    const active = document.querySelector(`.workshop-card[data-id="${id}"]`);
    if (active) {
        active.classList.remove('border-slate-200', 'bg-white');
        active.classList.add('border-indigo-500', 'bg-indigo-50/20', 'shadow-md', 'shadow-indigo-50/40');

        const inner = active.querySelector('.radio-inner');
        if (inner) { inner.classList.remove('scale-0'); inner.classList.add('scale-100'); }
        const outer = active.querySelector('.radio-outer');
        if (outer) { outer.classList.remove('border-slate-300', 'bg-white'); outer.classList.add('border-indigo-600', 'bg-indigo-50'); }
    }

    // Avanzar a paso 2: buscar alumno
    toggleChangeStudentModal(true);
}

// --- Pre-inscripción ---

function submitPreinscripcion() {
    if (!state.alumno || !state.ofertaId) return;

    const nombre = [state.alumno.primer_nombre, state.alumno.primer_apellido].filter(Boolean).join(' ');
    $('confirm-message').textContent = nombre + ', ¿está usted seguro de preinscribirse en este ' + MODO_MAP[state.modo].label + '?';
    openModal('modal-confirm');
}

function executePreinscripcion() {
    closeModal('modal-confirm');

    const btn = $('btn-submit');
    btn.disabled = true;
    btn.textContent = 'Procesando...';

    postJSON(API + 'process_preinscripcion', {
        alumno_id: state.alumno.id,
        oferta_abierta_id: state.ofertaId,
        typeId: state.typeId
    },
        function (res) {
            if (res.success) {
                const nombre = [state.alumno.primer_nombre, state.alumno.primer_apellido].filter(Boolean).join(' ');
                const oferta = state.ofertas.find(o => String(o.id) === String(state.ofertaId));
                $('ticket-program').textContent = (oferta ? oferta.numero + ' - ' + oferta.nombre : '');
                $('ticket-student').textContent = nombre;
                $('ticket-id').textContent = '#REG-' + new Date().getFullYear() + '-' + Date.now().toString().slice(-4);
                openModal('modal-success');
                showToast(res.message, 'success');
            } else {
                showToast(res.message || 'Error al procesar la pre-inscripción.', 'error');
            }
            btn.textContent = 'Finalizar Pre-inscripción';
        },
        function () {
            showToast('Error de conexión al procesar la pre-inscripción.', 'error');
            btn.textContent = 'Finalizar Pre-inscripción';
        }
    );
}

// --- Modales ---

function toggleChangeStudentModal(show) {
    if (show) {
        $('search-ci-input').value = '';
        $('search-error').classList.add('hidden');
        openModal('modal-search-student');
        setTimeout(() => $('search-ci-input').focus(), 100);
    } else {
        closeModal('modal-search-student');
    }
}

function toggleCreateStudentModal(show) {
    if (show) {
        openModal('modal-create-student');
    } else {
        closeModal('modal-create-student');
    }
}

// --- Reset ---

function resetFlow() {
    closeModal('modal-success');
    state.alumno = null;
    state.ofertaId = null;

    show('empty-state');
    hide('student-card');
    hide('alumno-verified-badge');
    hide('step1-section');
    hide('step2-section');
    $('step-badge').textContent = 'Paso 1 de 2';

    const btn = $('btn-submit');
    btn.disabled = true;
    btn.classList.add('bg-slate-300', 'text-slate-500', 'cursor-not-allowed');
    btn.classList.remove('bg-indigo-600', 'text-white', 'hover:bg-indigo-700', 'cursor-pointer', 'shadow-lg', 'shadow-indigo-100');

    // Recargar ofertas para empezar de nuevo
    setTimeout(function () {
        hide('empty-state');
        show('step1-section');
        loadOfertas();
    }, 300);

    showToast('Flujo reiniciado correctamente.', 'info');
}
