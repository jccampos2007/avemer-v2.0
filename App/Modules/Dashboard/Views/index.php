<!-- // php_mvc_app/app/Modules/Dashboard/Views/index.php -->
<?php
$activeDiplomados = array_filter($lastMonthStats ?? [], fn($i) => $i['category_index'] == 0);
$activeEventos = array_filter($lastMonthStats ?? [], fn($i) => $i['category_index'] == 1);
$activeMaestrias = array_filter($lastMonthStats ?? [], fn($i) => $i['category_index'] == 2);
$activeCursos = array_filter($lastMonthStats ?? [], fn($i) => $i['category_index'] == 3);
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Contenedor de Tarjetas KPI - Completamente Adaptativo (1 Col móvil, 2 col tablet, 4 col desktop) -->
<div x-data="{ activeTooltip: null }" @click.outside="activeTooltip = null" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
    
    <!-- Tarjeta Diplomados -->
    <div class="bg-blue-600 p-5 md:p-6 rounded-lg shadow-md text-white hover:shadow-lg transition duration-300 relative">
        <div class="flex items-center justify-between">
            <h3 class="text-base md:text-lg font-bold mb-1 md:mb-2">Diplomados</h3>
            <i class="fa-solid fa-graduation-cap text-2xl md:text-3xl opacity-80"></i>
        </div>
        <p class="text-xl md:text-2xl font-semibold"><?php echo ($stats['diplomados']['activos'] ?? 0) . ' <span class="text-2xl md:text-4xl opacity-75">/ ' . ($stats['diplomados']['total'] ?? 0) . '</span>'; ?></p>
        <div class="flex items-center justify-between mt-2 pt-2 border-t border-white/20">
            <p class="text-xs opacity-80">Inscritos: Activos / Total</p>
            <i @click.stop="activeTooltip = (activeTooltip === 'diplomados' ? null : 'diplomados')" class="fa-solid fa-circle-info cursor-pointer opacity-75 hover:opacity-100 transition-opacity text-lg md:text-xl"></i>
        </div>

        <!-- Tooltip de Diplomados (Adaptable a pantallas pequeñas) -->
        <div x-show="activeTooltip === 'diplomados'" x-transition style="display: none;" 
             class="absolute top-full left-0 right-0 sm:right-auto sm:left-4 mt-2 bg-gray-950 bg-opacity-95 text-white text-sm rounded shadow-xl p-4 z-50 sm:w-72 border border-gray-700 backdrop-blur-md">
            <div class="font-bold border-b border-gray-600 pb-2 mb-2">Diplomados Activos</div>
            <ul class="list-disc pl-5 max-h-40 overflow-y-auto space-y-1">
                <?php if (empty($activeDiplomados)): ?>
                    <li class="text-gray-500 italic text-xs">Ningún diplomado activo</li>
                <?php else: ?>
                    <?php foreach ($activeDiplomados as $item): ?>
                        <li class="text-xs"><?php echo htmlspecialchars($item['label']); ?></li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    
    <!-- Tarjeta Eventos -->
    <div class="bg-green-600 p-5 md:p-6 rounded-lg shadow-md text-white hover:shadow-lg transition duration-300 relative">
        <div class="flex items-center justify-between">
            <h3 class="text-base md:text-lg font-bold mb-1 md:mb-2">Eventos</h3>
            <i class="fa-solid fa-calendar-check text-2xl md:text-3xl opacity-80"></i>
        </div>
        <p class="text-xl md:text-2xl font-semibold"><?php echo ($stats['eventos']['activos'] ?? 0) . ' <span class="text-2xl md:text-4xl opacity-75">/ ' . ($stats['eventos']['total'] ?? 0) . '</span>'; ?></p>
        <div class="flex items-center justify-between mt-2 pt-2 border-t border-white/20">
            <p class="text-xs opacity-80">Inscritos: Activos / Total</p>
            <i @click.stop="activeTooltip = (activeTooltip === 'eventos' ? null : 'eventos')" class="fa-solid fa-circle-info cursor-pointer opacity-75 hover:opacity-100 transition-opacity text-lg md:text-xl"></i>
        </div>

        <!-- Tooltip de Eventos -->
        <div x-show="activeTooltip === 'eventos'" x-transition style="display: none;" 
             class="absolute top-full left-0 right-0 sm:right-auto sm:left-4 mt-2 bg-gray-950 bg-opacity-95 text-white text-sm rounded shadow-xl p-4 z-50 sm:w-72 border border-gray-700 backdrop-blur-md">
            <div class="font-bold border-b border-gray-600 pb-2 mb-2">Eventos Activos</div>
            <ul class="list-disc pl-5 max-h-40 overflow-y-auto space-y-1">
                <?php if (empty($activeEventos)): ?>
                    <li class="text-gray-500 italic text-xs">Ningún evento activo</li>
                <?php else: ?>
                    <?php foreach ($activeEventos as $item): ?>
                        <li class="text-xs"><?php echo htmlspecialchars($item['label']); ?></li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    
    <!-- Tarjeta Maestrías -->
    <div class="bg-purple-600 p-5 md:p-6 rounded-lg shadow-md text-white hover:shadow-lg transition duration-300 relative">
        <div class="flex items-center justify-between">
            <h3 class="text-base md:text-lg font-bold mb-1 md:mb-2">Maestrías</h3>
            <i class="fa-solid fa-user-graduate text-2xl md:text-3xl opacity-80"></i>
        </div>
        <p class="text-xl md:text-2xl font-semibold"><?php echo ($stats['maestrias']['activos'] ?? 0) . ' <span class="text-2xl md:text-4xl opacity-75">/ ' . ($stats['maestrias']['total'] ?? 0) . '</span>'; ?></p>
        <div class="flex items-center justify-between mt-2 pt-2 border-t border-white/20">
            <p class="text-xs opacity-80">Inscritos: Activos / Total</p>
            <i @click.stop="activeTooltip = (activeTooltip === 'maestrias' ? null : 'maestrias')" class="fa-solid fa-circle-info cursor-pointer opacity-75 hover:opacity-100 transition-opacity text-lg md:text-xl"></i>
        </div>

        <!-- Tooltip de Maestrías -->
        <div x-show="activeTooltip === 'maestrias'" x-transition style="display: none;" 
             class="absolute top-full left-0 right-0 sm:right-auto sm:left-4 mt-2 bg-gray-950 bg-opacity-95 text-white text-sm rounded shadow-xl p-4 z-50 sm:w-72 border border-gray-700 backdrop-blur-md">
            <div class="font-bold border-b border-gray-600 pb-2 mb-2">Maestrías Activas</div>
            <ul class="list-disc pl-5 max-h-40 overflow-y-auto space-y-1">
                <?php if (empty($activeMaestrias)): ?>
                    <li class="text-gray-500 italic text-xs">Ninguna maestría activa</li>
                <?php else: ?>
                    <?php foreach ($activeMaestrias as $item): ?>
                        <li class="text-xs"><?php echo htmlspecialchars($item['label']); ?></li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    
    <!-- Tarjeta Cursos / Talleres -->
    <div class="bg-indigo-700 p-5 md:p-6 rounded-lg shadow-md text-white hover:shadow-lg transition duration-300 relative">
        <div class="flex items-center justify-between">
            <h3 class="text-base md:text-lg font-bold mb-1 md:mb-2">Cursos / Talleres</h3>
            <i class="fa-solid fa-book-open text-2xl md:text-3xl opacity-80"></i>
        </div>
        <p class="text-xl md:text-2xl font-semibold"><?php echo ($stats['cursos']['activos'] ?? 0) . ' <span class="text-2xl md:text-4xl opacity-75">/ ' . ($stats['cursos']['total'] ?? 0) . '</span>'; ?></p>
        <div class="flex items-center justify-between mt-2 pt-2 border-t border-white/20">
            <p class="text-xs opacity-80">Inscritos: Activos / Total</p>
            <i @click.stop="activeTooltip = (activeTooltip === 'cursos' ? null : 'cursos')" class="fa-solid fa-circle-info cursor-pointer opacity-75 hover:opacity-100 transition-opacity text-lg md:text-xl"></i>
        </div>

        <!-- Tooltip de Cursos -->
        <div x-show="activeTooltip === 'cursos'" x-transition style="display: none;" 
             class="absolute top-full left-0 right-0 sm:right-auto sm:right-4 mt-2 bg-gray-900 bg-opacity-95 text-white text-sm rounded shadow-xl p-4 z-50 sm:w-72 border border-gray-700 backdrop-blur-md">
            <div class="font-bold border-b border-gray-600 pb-2 mb-2">Cursos Activos</div>
            <ul class="list-disc pl-5 max-h-40 overflow-y-auto space-y-1">
                <?php if (empty($activeCursos)): ?>
                    <li class="text-gray-500 italic text-xs">Ningún curso activo</li>
                <?php else: ?>
                    <?php foreach ($activeCursos as $item): ?>
                        <li class="text-xs"><?php echo htmlspecialchars($item['label']); ?></li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Gráfica de Inscripciones (Diseño adaptable con alturas controladas) -->
<div class="bg-white p-4 md:p-6 rounded-lg shadow-md mb-8">
    <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-4">Resumen Gráfico (Programas Activos)</h3>
    <div class="relative h-64 sm:h-80 md:h-96 w-full">
        <canvas id="inscripcionesChart"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('inscripcionesChart').getContext('2d');
    
    // Obtener los datos mapeados desde PHP
    const items = <?php echo json_encode($lastMonthStats ?? []); ?>;
    
    // Crear los datasets
    const datasets = items.map(item => {
        let dataArr = [null, null, null, null];
        dataArr[item.category_index] = item.count;
        
        return {
            label: item.label,
            data: dataArr,
            backgroundColor: item.bg_color,
            borderColor: item.border_color,
            borderWidth: 1,
            borderRadius: 4
        };
    });

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Diplomados', 'Eventos', 'Maestrías', 'Cursos / Talleres'],
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    ticks: {
                        display: true
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false,
                    position: 'top',
                    align: 'start',
                    labels: {
                        boxWidth: 12,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            return context[0].dataset.label;
                        },
                        label: function(context) {
                            return ` ${context.raw} inscritos`;
                        }
                    }
                }
            }
        }
    });
});
</script>

<!-- Grid de Accesos Directos Inferiores -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <?php if (\App\Core\Auth::hasPermission('users')): ?>
    <div class="bg-white p-5 md:p-6 rounded-lg shadow-md hover:shadow-lg transition duration-300 flex flex-col h-full">
        <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-2">Gestión de Usuarios</h3>
        <p class="text-sm md:text-base text-gray-600 flex-grow">Crea, edita y elimina cuentas de usuario.</p>
        <div class="mt-4 text-center">
            <a href="<?php echo BASE_URL; ?>users" class="w-full sm:w-auto inline-block bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-md transition duration-300">Ir a Usuarios</a>
        </div>
    </div>
    <?php endif; ?>

    <?php if (\App\Core\Auth::hasPermission('alumnos')): ?>
    <div class="bg-white p-5 md:p-6 rounded-lg shadow-md hover:shadow-lg transition duration-300 flex flex-col h-full">
        <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-2">Gestión de Alumnos</h3>
        <p class="text-sm md:text-base text-gray-600 flex-grow">Administra la información de los alumnos.</p>
        <div class="mt-4 text-center">
            <a href="<?php echo BASE_URL; ?>alumnos" class="w-full sm:w-auto inline-block bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-md transition duration-300">Ir a Alumnos</a>
        </div>
    </div>
    <?php endif; ?>

    <?php if (\App\Core\Auth::hasPermission('diplomado')): ?>
    <div class="bg-white p-5 md:p-6 rounded-lg shadow-md hover:shadow-lg transition duration-300 flex flex-col h-full">
        <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-2">Gestión de Diplomados</h3>
        <p class="text-sm md:text-base text-gray-600 flex-grow">Define y organiza los diferentes diplomados.</p>
        <div class="mt-4 text-center">
            <a href="<?php echo BASE_URL; ?>diplomado" class="w-full sm:w-auto inline-block bg-purple-500 hover:bg-purple-600 text-white font-semibold py-2 px-4 rounded-md transition duration-300">Ir a Diplomados</a>
        </div>
    </div>
    <?php endif; ?>
</div>