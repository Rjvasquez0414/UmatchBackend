// ============================================
// ESTADO DE LA APLICACIÓN
// ============================================

let currentUser = null;
let currentSport = null;
let currentEventId = null;
let currentTournamentId = null;
let currentTournamentTab = 'official';

// ============================================
// CONFIGURACIÓN DE LA API DEL CLIMA
// ============================================

// IMPORTANTE: La API key debe estar en un archivo config.js separado
// que NO se suba a GitHub. Ver config.example.js para instrucciones.
const WEATHER_API_CONFIG = {
    url: 'https://atlas.microsoft.com/weather/currentConditions/json',
    // La apiKey se obtiene del archivo config.js (no incluido en git)
    apiKey: typeof WEATHER_CONFIG !== 'undefined' ? WEATHER_CONFIG.apiKey : 'CONFIGURAR_API_KEY',
    coordinates: {
        lat: 7.116345247418024,
        lon: -73.10550121931915
    }
};

// Estado del clima
let currentWeatherData = null;
let weatherUpdateInterval = null;

const SPORTS_NAMES = {
    futbol: 'Fútbol',
    basketball: 'Basketball',
    tenis: 'Tenis',
    padel: 'Pádel',
    volleyball: 'Volleyball',
    billar: 'Billar',
    pingpong: 'Ping Pong'
};

// ============================================
// SISTEMA DE CANCHAS Y DISPONIBILIDAD
// ============================================

const FACILITIES = {
    futbol: {
        type: 'cancha',
        courts: [
            { id: 'multiuso-1', name: 'Cancha Multiuso 1', shared: ['futbol', 'basketball'] },
            { id: 'multiuso-2', name: 'Cancha Multiuso 2', shared: ['futbol', 'basketball'] },
            { id: 'multiuso-3', name: 'Cancha Multiuso 3', shared: ['futbol', 'basketball'] }
        ]
    },
    basketball: {
        type: 'cancha',
        courts: [
            { id: 'multiuso-1', name: 'Cancha Multiuso 1', shared: ['futbol', 'basketball'] },
            { id: 'multiuso-2', name: 'Cancha Multiuso 2', shared: ['futbol', 'basketball'] },
            { id: 'multiuso-3', name: 'Cancha Multiuso 3', shared: ['futbol', 'basketball'] }
        ]
    },
    volleyball: {
        type: 'cancha',
        courts: [
            { id: 'volleyball-1', name: 'Cancha de Volleyball', shared: ['volleyball'] }
        ]
    },
    tenis: {
        type: 'cancha',
        courts: [
            { id: 'tenis-1', name: 'Cancha de Tenis', shared: ['tenis'] }
        ]
    },
    padel: {
        type: 'cancha',
        courts: [
            { id: 'padel-1', name: 'Cancha de Pádel', shared: ['padel'] }
        ]
    },
    billar: {
        type: 'mesa',
        courts: [
            { id: 'billar-1', name: 'Mesa de Billar 1', shared: ['billar'] },
            { id: 'billar-2', name: 'Mesa de Billar 2', shared: ['billar'] },
            { id: 'billar-3', name: 'Mesa de Billar 3', shared: ['billar'] }
        ]
    },
    pingpong: {
        type: 'mesa',
        courts: [
            { id: 'pingpong-1', name: 'Mesa de Ping Pong 1', shared: ['pingpong'] },
            { id: 'pingpong-2', name: 'Mesa de Ping Pong 2', shared: ['pingpong'] },
            { id: 'pingpong-3', name: 'Mesa de Ping Pong 3', shared: ['pingpong'] }
        ]
    }
};

// Coliseo - solo para administradores
const COLISEO = {
    id: 'coliseo-1',
    name: 'Coliseo CSU',
    shared: ['futbol', 'basketball', 'volleyball'],
    adminOnly: true
};

const DURATION_OPTIONS = [
    { value: '1', label: '1 hora' },
    { value: '1.5', label: '1.5 horas' },
    { value: '2', label: '2 horas' },
    { value: '2.5', label: '2.5 horas' },
    { value: '3', label: '3 horas' }
];

// ============================================
// INICIALIZACIÓN
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
    setupEventListeners();
    loadSampleData();
    initializeGalleryImages();
    initializeCarousel();
});

function initializeApp() {
    // Verificar si hay una sesión activa
    const savedUser = localStorage.getItem('currentUser');
    if (savedUser) {
        currentUser = JSON.parse(savedUser);
        showView('dashboard-view');
        updateUserInfo();
        // Iniciar actualización del clima cuando el usuario está logueado
        initializeWeatherSystem();
    } else {
        showView('login-view');
    }
}

function initializeGalleryImages() {
    // Obtener todas las imágenes de la galería
    const galleryImages = document.querySelectorAll('.gallery-item-image');

    galleryImages.forEach(img => {
        // Si la imagen ya está cargada (en cache)
        if (img.complete && img.naturalHeight !== 0) {
            handleImageLoad(img);
        } else {
            // Esperar a que la imagen cargue
            img.addEventListener('load', () => handleImageLoad(img));

            // Manejar errores de carga
            img.addEventListener('error', () => {
                const placeholder = img.previousElementSibling;
                if (placeholder && placeholder.classList.contains('gallery-placeholder')) {
                    // Mantener el placeholder visible si hay error
                    const icon = placeholder.querySelector('.gallery-placeholder-icon');
                    if (icon) {
                        icon.setAttribute('data-feather', 'alert-circle');
                        feather.replace();
                    }
                }
            });
        }
    });
}

function handleImageLoad(img) {
    // Agregar clase 'loaded' a la imagen
    img.classList.add('loaded');

    // Ocultar el placeholder
    const placeholder = img.previousElementSibling;
    if (placeholder && placeholder.classList.contains('gallery-placeholder')) {
        placeholder.classList.add('hidden');
    }
}

// ============================================
// CARRUSEL DE INSTALACIONES
// ============================================

let carouselState = {
    currentSlide: 0,
    totalSlides: 0,
    autoPlayInterval: null,
    isPlaying: true
};

function initializeCarousel() {
    const slides = document.querySelectorAll('.carousel-slide');
    const prevBtn = document.querySelector('.carousel-control.prev');
    const nextBtn = document.querySelector('.carousel-control.next');
    const indicators = document.querySelectorAll('.carousel-indicator');
    const carouselContainer = document.querySelector('.carousel-container');

    if (!slides.length) return;

    carouselState.totalSlides = slides.length;

    // Event listeners para controles
    prevBtn?.addEventListener('click', () => {
        navigateCarousel('prev');
    });

    nextBtn?.addEventListener('click', () => {
        navigateCarousel('next');
    });

    // Event listeners para indicadores
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            goToSlide(index);
        });
    });

    // Pausar auto-play en hover
    carouselContainer?.addEventListener('mouseenter', () => {
        pauseCarousel();
    });

    carouselContainer?.addEventListener('mouseleave', () => {
        resumeCarousel();
    });

    // Soporte para touch/swipe en móviles
    let touchStartX = 0;
    let touchEndX = 0;

    carouselContainer?.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
    });

    carouselContainer?.addEventListener('touchend', (e) => {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });

    function handleSwipe() {
        if (touchEndX < touchStartX - 50) {
            // Swipe left
            navigateCarousel('next');
        }
        if (touchEndX > touchStartX + 50) {
            // Swipe right
            navigateCarousel('prev');
        }
    }

    // Iniciar auto-play
    startCarousel();

    // Actualizar iconos de Feather
    feather.replace();
}

function navigateCarousel(direction) {
    if (direction === 'next') {
        carouselState.currentSlide = (carouselState.currentSlide + 1) % carouselState.totalSlides;
    } else {
        carouselState.currentSlide = (carouselState.currentSlide - 1 + carouselState.totalSlides) % carouselState.totalSlides;
    }
    updateCarousel();
}

function goToSlide(index) {
    carouselState.currentSlide = index;
    updateCarousel();
}

function updateCarousel() {
    const slides = document.querySelectorAll('.carousel-slide');
    const indicators = document.querySelectorAll('.carousel-indicator');

    // Actualizar slides
    slides.forEach((slide, index) => {
        if (index === carouselState.currentSlide) {
            slide.classList.add('active');
        } else {
            slide.classList.remove('active');
        }
    });

    // Actualizar indicadores
    indicators.forEach((indicator, index) => {
        if (index === carouselState.currentSlide) {
            indicator.classList.add('active');
        } else {
            indicator.classList.remove('active');
        }
    });
}

function startCarousel() {
    if (carouselState.autoPlayInterval) {
        clearInterval(carouselState.autoPlayInterval);
    }
    carouselState.autoPlayInterval = setInterval(() => {
        if (carouselState.isPlaying) {
            navigateCarousel('next');
        }
    }, 5000); // Avanzar cada 5 segundos
}

function pauseCarousel() {
    carouselState.isPlaying = false;
}

function resumeCarousel() {
    carouselState.isPlaying = true;
}

// ============================================
// SISTEMA DE CLIMA
// ============================================

function initializeWeatherSystem() {
    // Cargar el clima inmediatamente
    fetchWeatherData();
    
    // Actualizar cada 10 minutos
    weatherUpdateInterval = setInterval(fetchWeatherData, 600000);
    
    // Agregar listener para el botón de actualizar
    const refreshBtn = document.getElementById('refresh-weather');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
            refreshBtn.classList.add('rotating');
            fetchWeatherData().finally(() => {
                setTimeout(() => refreshBtn.classList.remove('rotating'), 500);
            });
        });
    }
}

async function fetchWeatherData() {
    const widget = document.getElementById('weather-widget');
    const loading = widget?.querySelector('.weather-loading');
    const content = widget?.querySelector('.weather-content');
    
    try {
        const response = await fetch(
            `${WEATHER_API_CONFIG.url}?api-version=1.0&query=${WEATHER_API_CONFIG.coordinates.lat},${WEATHER_API_CONFIG.coordinates.lon}&subscription-key=${WEATHER_API_CONFIG.apiKey}`,
            {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            }
        );
        
        if (!response.ok) {
            throw new Error('Error al obtener datos del clima');
        }
        
        const data = await response.json();
        currentWeatherData = data.results[0];
        
        // Actualizar UI del widget
        updateWeatherWidget(currentWeatherData);
        updateSportsWeatherIndicators(currentWeatherData);
        
        // Mostrar contenido y ocultar loading
        if (loading && content) {
            loading.style.display = 'none';
            content.style.display = 'block';
        }
        
    } catch (error) {
        console.error('Error fetching weather:', error);
        showWeatherError();
    }
}

function updateWeatherWidget(weatherData) {
    if (!weatherData) return;
    
    // Temperatura
    const tempElement = document.querySelector('.temp-value');
    if (tempElement) {
        tempElement.textContent = Math.round(weatherData.temperature.value);
    }
    
    // Condición
    const conditionIcon = document.querySelector('.condition-icon');
    const conditionText = document.querySelector('.condition-text');
    if (conditionIcon && conditionText) {
        const condition = getWeatherCondition(weatherData);
        conditionIcon.textContent = condition.icon;
        conditionText.textContent = condition.text;
    }
    
    // Detalles
    const humidity = document.getElementById('humidity');
    const wind = document.getElementById('wind');
    const precipitation = document.getElementById('precipitation');
    
    if (humidity) {
        humidity.textContent = `${weatherData.relativeHumidity}%`;
    }
    if (wind) {
        const windSpeed = weatherData.wind.speed.value;
        wind.textContent = `${Math.round(windSpeed)} km/h`;
    }
    if (precipitation) {
        // Si hay probabilidad de precipitación, usarla
        const precipProb = weatherData.precipitationSummary?.pastHour?.value || 0;
        precipitation.textContent = precipProb > 0 ? `${precipProb}%` : '0%';
    }
    
    // Recomendación
    updateWeatherRecommendation(weatherData);
    
    // Última actualización
    const lastUpdate = document.getElementById('last-update');
    if (lastUpdate) {
        const now = new Date();
        lastUpdate.textContent = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
    }
}

function getWeatherCondition(weatherData) {
    const iconPhrase = weatherData.iconPhrase?.toLowerCase() || '';
    const isDaytime = weatherData.isDaytime;
    
    // Mapeo de condiciones a iconos y textos
    const conditions = {
        'clear': { icon: isDaytime ? '☀️' : '🌙', text: 'Despejado' },
        'sunny': { icon: '☀️', text: 'Soleado' },
        'partly sunny': { icon: '⛅', text: 'Parcialmente soleado' },
        'mostly sunny': { icon: '🌤️', text: 'Mayormente soleado' },
        'partly cloudy': { icon: '⛅', text: 'Parcialmente nublado' },
        'mostly cloudy': { icon: '☁️', text: 'Mayormente nublado' },
        'cloudy': { icon: '☁️', text: 'Nublado' },
        'overcast': { icon: '☁️', text: 'Cubierto' },
        'fog': { icon: '🌫️', text: 'Niebla' },
        'showers': { icon: '🌦️', text: 'Chubascos' },
        'rain': { icon: '🌧️', text: 'Lluvia' },
        'thunderstorm': { icon: '⛈️', text: 'Tormenta' },
        'snow': { icon: '🌨️', text: 'Nieve' },
        'sleet': { icon: '🌨️', text: 'Aguanieve' },
        'hazy': { icon: '🌫️', text: 'Brumoso' }
    };
    
    // Buscar coincidencia
    for (const [key, value] of Object.entries(conditions)) {
        if (iconPhrase.includes(key)) {
            return value;
        }
    }
    
    // Default
    return { icon: isDaytime ? '🌤️' : '🌙', text: weatherData.iconPhrase || 'Variable' };
}

function updateWeatherRecommendation(weatherData) {
    const recommendation = document.getElementById('weather-recommendation');
    if (!recommendation) return;
    
    const icon = recommendation.querySelector('.recommendation-icon');
    const text = recommendation.querySelector('.recommendation-text');
    
    const temp = weatherData.temperature.value;
    const humidity = weatherData.relativeHumidity;
    const windSpeed = weatherData.wind.speed.value;
    const iconPhrase = weatherData.iconPhrase?.toLowerCase() || '';
    
    // Determinar recomendación basada en condiciones
    if (iconPhrase.includes('rain') || iconPhrase.includes('shower') || iconPhrase.includes('storm')) {
        icon.textContent = '⚠️';
        text.textContent = 'Condiciones no favorables - Se recomienda usar el coliseo o espacios techados';
        recommendation.className = 'weather-recommendation warning';
    } else if (windSpeed > 30) {
        icon.textContent = '⚠️';
        text.textContent = 'Viento fuerte - Precaución en deportes con pelota liviana';
        recommendation.className = 'weather-recommendation warning';
    } else if (temp > 35) {
        icon.textContent = '🔥';
        text.textContent = 'Temperatura muy alta - Mantenerse hidratado y tomar descansos frecuentes';
        recommendation.className = 'weather-recommendation caution';
    } else if (temp < 10) {
        icon.textContent = '❄️';
        text.textContent = 'Temperatura baja - Realizar calentamiento adecuado';
        recommendation.className = 'weather-recommendation caution';
    } else if (humidity > 85) {
        icon.textContent = '💧';
        text.textContent = 'Humedad alta - Mayor sensación térmica, hidratarse frecuentemente';
        recommendation.className = 'weather-recommendation caution';
    } else {
        icon.textContent = '✅';
        text.textContent = 'Condiciones ideales para deportes al aire libre';
        recommendation.className = 'weather-recommendation good';
    }
}

function updateSportsWeatherIndicators(weatherData) {
    if (!weatherData) return;
    
    const iconPhrase = weatherData.iconPhrase?.toLowerCase() || '';
    const isRaining = iconPhrase.includes('rain') || iconPhrase.includes('shower') || iconPhrase.includes('storm');
    const windSpeed = weatherData.wind.speed.value;
    
    // Actualizar indicadores de cada deporte destapado
    const outdoorSports = ['futbol', 'basketball', 'tenis', 'padel', 'volleyball'];
    
    outdoorSports.forEach(sport => {
        const indicator = document.getElementById(`weather-${sport}`);
        if (indicator) {
            const status = indicator.querySelector('.weather-status');
            if (status) {
                if (isRaining) {
                    status.textContent = '🌧️';
                    status.title = 'Lluvia - No recomendado';
                    indicator.className = 'weather-indicator bad';
                } else if (windSpeed > 30 && (sport === 'tenis' || sport === 'padel' || sport === 'volleyball')) {
                    status.textContent = '🌬️';
                    status.title = 'Viento fuerte - Precaución';
                    indicator.className = 'weather-indicator caution';
                } else {
                    status.textContent = '✅';
                    status.title = 'Condiciones favorables';
                    indicator.className = 'weather-indicator good';
                }
            }
        }
    });
}

function showWeatherError() {
    const widget = document.getElementById('weather-widget');
    const loading = widget?.querySelector('.weather-loading');
    const content = widget?.querySelector('.weather-content');
    
    if (loading) {
        loading.innerHTML = `
            <div class="weather-error">
                <span>⚠️</span>
                <p>No se pudo cargar el clima</p>
                <button class="btn-retry" onclick="fetchWeatherData()">Reintentar</button>
            </div>
        `;
    }
}

// Función para mostrar alerta del clima en la vista de eventos
function showWeatherAlertForSport(sport) {
    if (!currentWeatherData) return;
    
    const alert = document.getElementById('events-weather-alert');
    if (!alert) return;
    
    const isOutdoor = ['futbol', 'basketball', 'tenis', 'padel', 'volleyball'].includes(sport);
    
    if (isOutdoor) {
        const description = alert.querySelector('.alert-description');
        const iconPhrase = currentWeatherData.iconPhrase?.toLowerCase() || '';
        const isRaining = iconPhrase.includes('rain') || iconPhrase.includes('shower') || iconPhrase.includes('storm');
        
        if (isRaining) {
            alert.className = 'events-weather-alert warning';
            alert.querySelector('.alert-icon').textContent = '⚠️';
            description.textContent = 'Se detectan condiciones de lluvia. Considera usar el coliseo o reprogramar el evento.';
            alert.style.display = 'flex';
        } else if (currentWeatherData.wind.speed.value > 30) {
            alert.className = 'events-weather-alert caution';
            alert.querySelector('.alert-icon').textContent = '🌬️';
            description.textContent = 'Viento fuerte detectado. Ten precaución durante el juego.';
            alert.style.display = 'flex';
        } else {
            alert.style.display = 'none';
        }
    } else {
        alert.style.display = 'none';
    }
}

function loadSampleData() {
    // Cargar datos de ejemplo si no existen
    if (!localStorage.getItem('events')) {
        const event1Id = generateId();
        const event2Id = generateId();
        const event3Id = generateId();
        const event4Id = generateId();

        const sampleEvents = [
            {
                id: event1Id,
                sport: 'futbol',
                name: 'Partido de Fútbol 5 vs 5',
                date: '2025-10-25',
                time: '16:00',
                duration: 2,
                court: 'Cancha Multiuso 1',
                courtId: 'multiuso-1',
                maxPlayers: 10,
                currentPlayers: 6,
                level: 'intermedio',
                description: 'Partido amistoso de fútbol 5. Buscamos 4 jugadores más para completar el equipo. Nivel intermedio, todos bienvenidos!',
                organizer: 'Juan Pérez',
                participants: ['Juan Pérez', 'Carlos Gómez', 'María López', 'Ana Martínez', 'Pedro Sánchez', 'Luis García']
            },
            {
                id: event2Id,
                sport: 'basketball',
                name: 'Basketball 3x3',
                date: '2025-10-23',
                time: '18:30',
                duration: 1.5,
                court: 'Cancha Multiuso 2',
                courtId: 'multiuso-2',
                maxPlayers: 6,
                currentPlayers: 4,
                level: 'todos',
                description: 'Partido casual de basketball 3x3. Necesitamos 2 jugadores más. Todos los niveles son bienvenidos.',
                organizer: 'Diego Torres',
                participants: ['Diego Torres', 'Roberto Díaz', 'Sandra Ruiz', 'Miguel Ángel']
            },
            {
                id: event3Id,
                sport: 'tenis',
                name: 'Torneo de Tenis Dobles',
                date: '2025-10-26',
                time: '14:00',
                duration: 2,
                court: 'Cancha de Tenis',
                courtId: 'tenis-1',
                maxPlayers: 4,
                currentPlayers: 2,
                level: 'avanzado',
                description: 'Torneo de dobles de tenis. Buscamos una pareja más para completar. Nivel avanzado requerido.',
                organizer: 'Carolina Vega',
                participants: ['Carolina Vega', 'Andrés Morales']
            },
            {
                id: event4Id,
                sport: 'volleyball',
                name: 'Volleyball Recreativo',
                date: '2025-10-24',
                time: '17:00',
                duration: 2,
                court: 'Cancha de Volleyball',
                courtId: 'volleyball-1',
                maxPlayers: 12,
                currentPlayers: 8,
                level: 'principiante',
                description: 'Volleyball recreativo para principiantes. Ambiente relajado y divertido. Necesitamos 4 jugadores más.',
                organizer: 'Laura Ramírez',
                participants: ['Laura Ramírez', 'José Luis', 'Camila Herrera', 'David Castro', 'Patricia Núñez', 'Ricardo Fernández', 'Sofía Mendoza', 'Javier Ortiz']
            }
        ];
        localStorage.setItem('events', JSON.stringify(sampleEvents));

        // Crear reservas de canchas para los eventos de ejemplo
        const sampleReservations = [
            {
                id: generateId(),
                eventId: event1Id,
                sport: 'futbol',
                courtId: 'multiuso-1',
                date: '2025-10-25',
                time: '16:00',
                duration: 2,
                createdAt: new Date().toISOString()
            },
            {
                id: generateId(),
                eventId: event2Id,
                sport: 'basketball',
                courtId: 'multiuso-2',
                date: '2025-10-23',
                time: '18:30',
                duration: 1.5,
                createdAt: new Date().toISOString()
            },
            {
                id: generateId(),
                eventId: event3Id,
                sport: 'tenis',
                courtId: 'tenis-1',
                date: '2025-10-26',
                time: '14:00',
                duration: 2,
                createdAt: new Date().toISOString()
            },
            {
                id: generateId(),
                eventId: event4Id,
                sport: 'volleyball',
                courtId: 'volleyball-1',
                date: '2025-10-24',
                time: '17:00',
                duration: 2,
                createdAt: new Date().toISOString()
            }
        ];

        if (!localStorage.getItem('courtReservations')) {
            localStorage.setItem('courtReservations', JSON.stringify(sampleReservations));
        }
    }

    // Cargar torneos de ejemplo si no existen
    if (!localStorage.getItem('tournaments')) {
        const sampleTournaments = [
            {
                id: generateId(),
                tipo: 'oficial',
                nombre: 'Copa UNAB de Fútbol 2025',
                deporte: 'futbol',
                fechaInicio: '2025-11-01',
                fechaFin: '2025-11-15',
                maxParticipantes: 16,
                participantesActuales: 12,
                formato: 'eliminacion-directa',
                descripcion: 'Torneo oficial de fútbol organizado por la UNAB. Equipos de 5 jugadores. El torneo se llevará a cabo en las canchas del CSU durante dos semanas.',
                reglas: 'Equipos de 5 jugadores (4 de campo + 1 portero). Partidos de 2 tiempos de 15 minutos. Deben ser estudiantes activos de la UNAB. Se requiere presentar carnet universitario.',
                premios: '1er lugar: Trofeo + $500.000, 2do lugar: Medallas + $200.000, 3er lugar: Medallas + $100.000',
                organizador: 'CSU UNAB',
                participantes: ['Equipo A', 'Equipo B', 'Equipo C', 'Equipo D', 'Equipo E', 'Equipo F', 'Equipo G', 'Equipo H', 'Equipo I', 'Equipo J', 'Equipo K', 'Equipo L'],
                estado: 'abierto'
            },
            {
                id: generateId(),
                tipo: 'oficial',
                nombre: 'Torneo Interclases Basketball',
                deporte: 'basketball',
                fechaInicio: '2025-10-28',
                fechaFin: '2025-11-10',
                maxParticipantes: 12,
                participantesActuales: 10,
                formato: 'grupos',
                descripcion: 'Torneo oficial de basketball entre las diferentes carreras de la UNAB. Fase de grupos seguida de eliminación directa.',
                reglas: 'Equipos de 5 jugadores titulares + 3 suplentes. Partidos de 4 cuartos de 10 minutos. Solo estudiantes de pregrado. Cada equipo debe representar una carrera específica.',
                premios: '1er lugar: Trofeo + Balones Nike oficiales, 2do lugar: Medallas + Camisetas, 3er lugar: Medallas',
                organizador: 'CSU UNAB',
                participantes: ['Ingeniería', 'Medicina', 'Derecho', 'Administración', 'Arquitectura', 'Psicología', 'Comunicación', 'Economía', 'Diseño', 'Enfermería'],
                estado: 'abierto'
            },
            {
                id: generateId(),
                tipo: 'oficial',
                nombre: 'Campeonato de Tenis Individual',
                deporte: 'tenis',
                fechaInicio: '2025-11-05',
                fechaFin: '2025-11-12',
                maxParticipantes: 8,
                participantesActuales: 6,
                formato: 'eliminacion-directa',
                descripcion: 'Campeonato individual de tenis. Los mejores jugadores de la UNAB compitiendo por el título universitario.',
                reglas: 'Modalidad individual. Partidos al mejor de 3 sets. Se requiere experiencia previa en competencia. Los jugadores deben traer su propia raqueta.',
                premios: '1er lugar: Raqueta profesional + $300.000, 2do lugar: Bolso deportivo + $150.000',
                organizador: 'CSU UNAB',
                participantes: ['Carlos Méndez', 'Ana Torres', 'Diego Ramírez', 'Laura Castro', 'Sebastián Vega', 'María Fernanda Ruiz'],
                estado: 'abierto'
            },
            {
                id: generateId(),
                tipo: 'amistoso',
                nombre: 'Torneo Amistoso de Pádel',
                deporte: 'padel',
                fechaInicio: '2025-10-30',
                fechaFin: '2025-10-31',
                maxParticipantes: 8,
                participantesActuales: 5,
                formato: 'round-robin',
                descripcion: 'Torneo amistoso de pádel formato parejas. Ideal para principiantes y niveles intermedios. Ambiente relajado y divertido.',
                reglas: 'Formato parejas (2 vs 2). Todos los equipos juegan entre sí. Partidos de 1 set a 6 games. Nivel principiante/intermedio.',
                premios: '',
                organizador: 'Roberto Sánchez',
                participantes: ['Roberto Sánchez', 'Camila López', 'Andrés Gómez', 'Paula Martínez', 'Felipe Rojas'],
                estado: 'abierto'
            },
            {
                id: generateId(),
                tipo: 'amistoso',
                nombre: 'Liga de Volleyball Playero',
                deporte: 'volleyball',
                fechaInicio: '2025-11-08',
                fechaFin: '2025-11-22',
                maxParticipantes: 12,
                participantesActuales: 8,
                formato: 'round-robin',
                descripcion: 'Liga amistosa de volleyball. Partidos todos los fines de semana. Buscamos equipos para completar la liga.',
                reglas: 'Equipos de 6 jugadores (4 titulares + 2 suplentes). Partidos de 3 sets. Formato round robin. Todos los niveles bienvenidos.',
                premios: '',
                organizer: 'Sofía Hernández',
                participantes: ['Los Águilas', 'Remadores', 'Spikers', 'Net Force', 'Ace Team', 'Block Masters', 'Jump Squad', 'Volley Stars'],
                estado: 'abierto'
            },
            {
                id: generateId(),
                tipo: 'amistoso',
                nombre: 'Torneo Relámpago de Ping Pong',
                deporte: 'pingpong',
                fechaInicio: '2025-10-26',
                fechaFin: '2025-10-26',
                maxParticipantes: 16,
                participantesActuales: 16,
                formato: 'eliminacion-directa',
                descripcion: 'Torneo express de ping pong en un solo día. Inscripciones cerradas - TORNEO LLENO.',
                reglas: 'Modalidad individual. Eliminación directa. Partidos al mejor de 3 sets (11 puntos cada uno).',
                premios: '',
                organizador: 'David Moreno',
                participantes: ['David Moreno', 'Lucas Pérez', 'Emma García', 'Mateo Silva', 'Isabella Díaz', 'Nicolás Cruz', 'Valentina Rojas', 'Santiago López', 'Martina González', 'Alejandro Torres', 'Lucía Vargas', 'Gabriel Muñoz', 'Catalina Reyes', 'Daniel Herrera', 'Gabriela Castro', 'Tomás Jiménez'],
                estado: 'en-curso'
            },
            {
                id: generateId(),
                tipo: 'oficial',
                nombre: 'Torneo de Billar Profesional',
                deporte: 'billar',
                fechaInicio: '2025-12-01',
                fechaFin: '2025-12-03',
                maxParticipantes: 16,
                participantesActuales: 4,
                formato: 'eliminacion-directa',
                descripcion: 'Torneo oficial de billar para jugadores avanzados. Modalidad bola 8.',
                reglas: 'Modalidad Bola 8 profesional. Eliminación directa. Juegos al mejor de 3 partidas. Se requiere nivel avanzado.',
                premios: '1er lugar: Taco profesional + $250.000, 2do lugar: Estuche + $100.000',
                organizador: 'CSU UNAB',
                participantes: ['Jorge Suárez', 'Ricardo Ortega', 'Fernando Pinto', 'Alejandra Mora'],
                estado: 'abierto'
            }
        ];
        localStorage.setItem('tournaments', JSON.stringify(sampleTournaments));
    }
}

// ============================================
// NAVEGACIÓN
// ============================================

function showView(viewId) {
    const views = document.querySelectorAll('.view');
    views.forEach(view => view.classList.remove('active'));

    const targetView = document.getElementById(viewId);
    if (targetView) {
        targetView.classList.add('active');
    }
}

function updateUserInfo() {
    if (currentUser) {
        const userNameElements = document.querySelectorAll('#user-name, #user-name-events');
        userNameElements.forEach(el => {
            el.textContent = currentUser.name;
        });
    }
}

// ============================================
// EVENT LISTENERS
// ============================================

function setupEventListeners() {
    // Login/Registro
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }

    const toggleRegister = document.getElementById('toggle-register');
    if (toggleRegister) {
        toggleRegister.addEventListener('click', (e) => {
            e.preventDefault();
            const title = document.getElementById('login-title');
            const toggleText = document.getElementById('toggle-register');

            if (title.textContent === 'Iniciar Sesión') {
                title.textContent = 'Registrarse';
                toggleText.textContent = 'Ya tengo cuenta';
            } else {
                title.textContent = 'Iniciar Sesión';
                toggleText.textContent = 'Regístrate aquí';
            }
        });
    }

    // Logout
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', handleLogout);
    }

    // Deportes
    const sportCards = document.querySelectorAll('.sport-card');
    sportCards.forEach(card => {
        card.addEventListener('click', () => {
            const sport = card.getAttribute('data-sport');
            handleSportSelect(sport);
        });
    });

    // Navegación
    const backToDashboard = document.getElementById('back-to-dashboard');
    if (backToDashboard) {
        backToDashboard.addEventListener('click', () => {
            showView('dashboard-view');
        });
    }

    const backToEvents = document.getElementById('back-to-events');
    if (backToEvents) {
        backToEvents.addEventListener('click', () => {
            showView('events-view');
            loadEvents(currentSport);
        });
    }

    const backToEventsList = document.getElementById('back-to-events-list');
    if (backToEventsList) {
        backToEventsList.addEventListener('click', () => {
            showView('events-view');
            loadEvents(currentSport);
        });
    }

    // Crear Evento
    const createEventBtn = document.getElementById('create-event-btn');
    if (createEventBtn) {
        createEventBtn.addEventListener('click', () => {
            showView('create-event-view');
        });
    }

    const createEventForm = document.getElementById('create-event-form');
    if (createEventForm) {
        createEventForm.addEventListener('submit', handleCreateEvent);
    }

    // Event listeners para actualizar disponibilidad de canchas
    const eventDateInput = document.getElementById('event-date');
    const eventTimeInput = document.getElementById('event-time');
    const eventDurationInput = document.getElementById('event-duration');
    const eventCourtSelect = document.getElementById('event-court');

    const updateCourtAvailability = () => {
        const date = eventDateInput.value;
        const time = eventTimeInput.value;
        const duration = eventDurationInput.value;

        if (date && time && duration && currentSport) {
            const availableCourts = getAvailableCourts(currentSport, date, time, duration);

            // Actualizar selector de canchas
            eventCourtSelect.innerHTML = '';
            eventCourtSelect.disabled = false;

            if (availableCourts.length === 0) {
                eventCourtSelect.innerHTML = '<option value="">No hay canchas disponibles</option>';
                eventCourtSelect.disabled = true;

                // Mostrar mensaje de no disponibilidad
                const availabilityInfo = document.getElementById('availability-info');
                const availabilityText = document.getElementById('availability-text');
                availabilityInfo.style.display = 'block';
                availabilityInfo.className = 'availability-info unavailable';
                availabilityText.textContent = '❌ No hay canchas disponibles en este horario';
            } else {
                // Agregar opciones de canchas disponibles
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = 'Selecciona una cancha';
                eventCourtSelect.appendChild(placeholder);

                availableCourts.forEach(court => {
                    const option = document.createElement('option');
                    option.value = court.id;
                    option.textContent = court.name;
                    eventCourtSelect.appendChild(option);
                });

                // Mostrar mensaje de disponibilidad
                const availabilityInfo = document.getElementById('availability-info');
                const availabilityText = document.getElementById('availability-text');
                availabilityInfo.style.display = 'block';
                availabilityInfo.className = 'availability-info available';
                const facilityType = FACILITIES[currentSport]?.type || 'cancha';
                availabilityText.textContent = `✅ ${availableCourts.length} ${facilityType}${availableCourts.length > 1 ? 's' : ''} disponible${availableCourts.length > 1 ? 's' : ''}`;
            }
        } else {
            eventCourtSelect.innerHTML = '<option value="">Primero selecciona fecha, hora y duración</option>';
            eventCourtSelect.disabled = true;
            document.getElementById('availability-info').style.display = 'none';
        }
    };

    if (eventDateInput) eventDateInput.addEventListener('change', updateCourtAvailability);
    if (eventTimeInput) eventTimeInput.addEventListener('change', updateCourtAvailability);
    if (eventDurationInput) eventDurationInput.addEventListener('change', updateCourtAvailability);

    // Unirse a Evento
    const joinEventBtn = document.getElementById('join-event-btn');
    if (joinEventBtn) {
        joinEventBtn.addEventListener('click', handleJoinEvent);
    }

    // ============================================
    // TORNEOS - Event Listeners
    // ============================================

    // Tarjeta de torneos en dashboard
    const tournamentsCard = document.getElementById('tournaments-card');
    if (tournamentsCard) {
        tournamentsCard.addEventListener('click', () => {
            showView('tournaments-view');
            updateUserInfo();
            loadTournaments();
        });
    }

    // Botón volver desde torneos
    const backToDashboardTournaments = document.getElementById('back-to-dashboard-tournaments');
    if (backToDashboardTournaments) {
        backToDashboardTournaments.addEventListener('click', () => {
            showView('dashboard-view');
        });
    }

    // Pestañas de torneos
    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.getAttribute('data-tab');
            switchTournamentTab(tab);
        });
    });

    // Filtros de torneos
    const sportFilter = document.getElementById('tournament-sport-filter');
    const statusFilter = document.getElementById('tournament-status-filter');
    if (sportFilter) {
        sportFilter.addEventListener('change', loadTournaments);
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', loadTournaments);
    }

    // Crear torneo
    const createTournamentBtn = document.getElementById('create-tournament-btn');
    if (createTournamentBtn) {
        createTournamentBtn.addEventListener('click', () => {
            showView('create-tournament-view');
            setupCreateTournamentForm();
        });
    }

    const backToTournaments = document.getElementById('back-to-tournaments');
    if (backToTournaments) {
        backToTournaments.addEventListener('click', () => {
            showView('tournaments-view');
            loadTournaments();
        });
    }

    const createTournamentForm = document.getElementById('create-tournament-form');
    if (createTournamentForm) {
        createTournamentForm.addEventListener('submit', handleCreateTournament);
    }

    // Tipo de torneo - mostrar/ocultar premios
    const tournamentType = document.getElementById('tournament-type');
    if (tournamentType) {
        tournamentType.addEventListener('change', (e) => {
            const prizesField = document.getElementById('prizes-field');
            if (e.target.value === 'oficial') {
                prizesField.style.display = 'block';
            } else {
                prizesField.style.display = 'none';
            }
        });
    }

    // Event listeners para validar disponibilidad de torneos
    const tournamentSportInput = document.getElementById('tournament-sport');
    const tournamentStartDateInput = document.getElementById('tournament-start-date');
    const tournamentEndDateInput = document.getElementById('tournament-end-date');

    const checkTournamentAvailabilityUI = () => {
        const sport = tournamentSportInput?.value;
        const startDate = tournamentStartDateInput?.value;
        const endDate = tournamentEndDateInput?.value;

        if (sport && startDate && endDate) {
            const result = checkTournamentAvailability(sport, startDate, endDate);

            const availabilityInfo = document.getElementById('tournament-availability-info');
            const availabilityText = document.getElementById('tournament-availability-text');

            if (availabilityInfo && availabilityText) {
                availabilityInfo.style.display = 'block';

                if (result.available) {
                    if (result.warning) {
                        availabilityInfo.className = 'availability-info warning';
                        availabilityText.textContent = `⚠️ ${result.message}`;
                    } else {
                        availabilityInfo.className = 'availability-info available';
                        availabilityText.textContent = `✅ ${result.message}`;
                    }
                } else {
                    availabilityInfo.className = 'availability-info unavailable';
                    availabilityText.textContent = `❌ ${result.message}`;
                }
            }
        }
    };

    if (tournamentSportInput) tournamentSportInput.addEventListener('change', checkTournamentAvailabilityUI);
    if (tournamentStartDateInput) tournamentStartDateInput.addEventListener('change', checkTournamentAvailabilityUI);
    if (tournamentEndDateInput) tournamentEndDateInput.addEventListener('change', checkTournamentAvailabilityUI);

    // Detalle de torneo
    const backToTournamentsList = document.getElementById('back-to-tournaments-list');
    if (backToTournamentsList) {
        backToTournamentsList.addEventListener('click', () => {
            showView('tournaments-view');
            loadTournaments();
        });
    }

    // Unirse a torneo
    const joinTournamentBtn = document.getElementById('join-tournament-btn');
    if (joinTournamentBtn) {
        joinTournamentBtn.addEventListener('click', handleJoinTournament);
    }
}

// ============================================
// AUTENTICACIÓN
// ============================================

function handleLogin(e) {
    e.preventDefault();

    const email = document.getElementById('email').value;
    const title = document.getElementById('login-title').textContent;

    // Extraer nombre del email
    const name = email.split('@')[0];
    const userName = name.charAt(0).toUpperCase() + name.slice(1);

    // Determinar rol (admin si es correo admin.unab.edu.co o emails específicos)
    const isAdmin = email.includes('admin.unab.edu.co') ||
                    email === 'deporte@unab.edu.co' ||
                    email === 'csu@unab.edu.co';

    // Buscar usuario existente o crear nuevo usuario
    const savedUser = localStorage.getItem(`user_${email}`);
    if (savedUser) {
        currentUser = JSON.parse(savedUser);
    } else {
        currentUser = {
            name: userName,
            email: email,
            role: isAdmin ? 'admin' : 'student',
            fullName: userName,
            bio: 'Estudiante apasionado por los deportes',
            program: '',
            semester: '',
            code: '',
            favoriteSports: [],
            avatarColor: generateAvatarColor(userName)
        };
        localStorage.setItem(`user_${email}`, JSON.stringify(currentUser));
    }

    localStorage.setItem('currentUser', JSON.stringify(currentUser));

    showView('dashboard-view');
    updateUserInfo();
    initializeFeatherIcons();
}

function handleLogout() {
    currentUser = null;
    localStorage.removeItem('currentUser');
    showView('login-view');

    // Limpiar formulario
    document.getElementById('email').value = '';
    document.getElementById('password').value = '';
}

// ============================================
// DEPORTES Y EVENTOS
// ============================================

function handleSportSelect(sport) {
    currentSport = sport;
    const sportTitle = document.getElementById('sport-title');
    sportTitle.textContent = SPORTS_NAMES[sport];

    showView('events-view');
    loadEvents(sport);
    
    // Mostrar alerta del clima si es un deporte al aire libre
    showWeatherAlertForSport(sport);
}

function loadEvents(sport) {
    const eventsList = document.getElementById('events-list');
    const noEvents = document.getElementById('no-events');

    const allEvents = JSON.parse(localStorage.getItem('events') || '[]');
    const sportEvents = allEvents.filter(event => event.sport === sport);

    eventsList.innerHTML = '';

    if (sportEvents.length === 0) {
        noEvents.style.display = 'block';
        eventsList.style.display = 'none';
    } else {
        noEvents.style.display = 'none';
        eventsList.style.display = 'grid';

        sportEvents.forEach(event => {
            const eventCard = createEventCard(event);
            eventsList.appendChild(eventCard);
        });
    }
}

function createEventCard(event) {
    const card = document.createElement('div');
    card.className = 'event-card';
    card.onclick = () => showEventDetail(event.id);

    const spotsLeft = event.maxPlayers - event.currentPlayers;
    const isFull = spotsLeft === 0;

    card.innerHTML = `
        <h3>${event.name}</h3>
        <div class="event-meta">
            <div class="event-meta-item">
                <strong>Fecha:</strong> ${formatDate(event.date)}
            </div>
            <div class="event-meta-item">
                <strong>Hora:</strong> ${event.time}
            </div>
            <div class="event-meta-item">
                <strong>Organizador:</strong> ${event.organizer}
            </div>
        </div>
        <div class="event-players">
            <span class="players-badge">
                ${event.currentPlayers}/${event.maxPlayers} jugadores
                ${isFull ? '(LLENO)' : `(${spotsLeft} cupos)`}
            </span>
            <span class="level-badge">${event.level}</span>
        </div>
    `;

    return card;
}

function showEventDetail(eventId) {
    const allEvents = JSON.parse(localStorage.getItem('events') || '[]');
    const event = allEvents.find(e => e.id === eventId);

    if (!event) return;

    currentEventId = eventId;

    // Actualizar información del evento
    document.getElementById('detail-event-name').textContent = event.name;
    document.getElementById('detail-sport').textContent = SPORTS_NAMES[event.sport];
    document.getElementById('detail-date').textContent = formatDate(event.date);
    document.getElementById('detail-time').textContent = event.time;
    document.getElementById('detail-duration').textContent = event.duration ? `${event.duration} hora${event.duration > 1 ? 's' : ''}` : 'No especificada';
    document.getElementById('detail-court').textContent = event.court || 'No asignada';
    document.getElementById('detail-level').textContent = event.level;
    document.getElementById('detail-organizer').textContent = event.organizer;
    document.getElementById('detail-description').textContent = event.description;
    document.getElementById('detail-current-players').textContent = event.currentPlayers;
    document.getElementById('detail-max-players').textContent = event.maxPlayers;

    // Actualizar barra de progreso
    const progress = (event.currentPlayers / event.maxPlayers) * 100;
    document.getElementById('detail-progress').style.width = progress + '%';

    // Mostrar/ocultar botón de unirse
    const joinBtn = document.getElementById('join-event-btn');
    const fullMsg = document.getElementById('event-full-msg');

    const isFull = event.currentPlayers >= event.maxPlayers;
    const isParticipant = event.participants.includes(currentUser.name);

    if (isFull || isParticipant) {
        joinBtn.style.display = 'none';
        fullMsg.style.display = 'block';
        fullMsg.textContent = isFull ? 'Este evento está lleno' : 'Ya estás inscrito en este evento';
    } else {
        joinBtn.style.display = 'block';
        fullMsg.style.display = 'none';
    }

    showView('event-detail-view');
}

function handleJoinEvent() {
    const allEvents = JSON.parse(localStorage.getItem('events') || '[]');
    const eventIndex = allEvents.findIndex(e => e.id === currentEventId);

    if (eventIndex === -1) return;

    const event = allEvents[eventIndex];

    // Verificar si hay cupos disponibles
    if (event.currentPlayers >= event.maxPlayers) {
        alert('Este evento ya está lleno');
        return;
    }

    // Verificar si ya está inscrito
    if (event.participants.includes(currentUser.name)) {
        alert('Ya estás inscrito en este evento');
        return;
    }

    // Agregar participante
    event.participants.push(currentUser.name);
    event.currentPlayers++;

    // Guardar cambios
    allEvents[eventIndex] = event;
    localStorage.setItem('events', JSON.stringify(allEvents));

    // Actualizar vista
    showEventDetail(currentEventId);

    alert('¡Te has unido al evento exitosamente!');
}

// ============================================
// CREAR EVENTO
// ============================================

function handleCreateEvent(e) {
    e.preventDefault();

    const date = document.getElementById('event-date').value;
    const time = document.getElementById('event-time').value;
    const duration = document.getElementById('event-duration').value;
    const courtId = document.getElementById('event-court').value;

    // Validaciones
    if (!courtId) {
        alert('Por favor selecciona una cancha disponible');
        return;
    }

    // Verificar disponibilidad una vez más antes de crear
    if (!checkCourtAvailability(courtId, currentSport, date, time, duration)) {
        alert('Lo sentimos, esta cancha ya no está disponible. Por favor selecciona otra.');
        return;
    }

    const eventId = generateId();
    const court = findCourtById(courtId);

    const newEvent = {
        id: eventId,
        sport: currentSport,
        name: document.getElementById('event-name').value,
        date: date,
        time: time,
        duration: parseFloat(duration),
        court: court.name,
        courtId: courtId,
        maxPlayers: parseInt(document.getElementById('max-players').value),
        currentPlayers: 1,
        level: document.getElementById('event-level').value,
        description: document.getElementById('event-description').value,
        organizer: currentUser.name,
        participants: [currentUser.name]
    };

    // Crear reserva de cancha
    createCourtReservation(eventId, currentSport, courtId, date, time, duration);

    // Guardar evento
    const allEvents = JSON.parse(localStorage.getItem('events') || '[]');
    allEvents.push(newEvent);
    localStorage.setItem('events', JSON.stringify(allEvents));

    // Limpiar formulario
    document.getElementById('create-event-form').reset();
    document.getElementById('availability-info').style.display = 'none';

    // Volver a la vista de eventos
    showView('events-view');
    loadEvents(currentSport);

    const facilityType = FACILITIES[currentSport]?.type || 'cancha';
    alert(`¡Evento creado exitosamente! ${court.name} ha sido reservada para ${date} a las ${time}.`);
}

// ============================================
// FUNCIONES DE TORNEOS
// ============================================

function switchTournamentTab(tab) {
    currentTournamentTab = tab;

    // Actualizar botones de pestañas
    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(btn => {
        if (btn.getAttribute('data-tab') === tab) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    // Actualizar contenido
    const officialContent = document.getElementById('official-tournaments-content');
    const friendlyContent = document.getElementById('friendly-tournaments-content');

    if (tab === 'official') {
        officialContent.classList.add('active');
        friendlyContent.classList.remove('active');
    } else {
        officialContent.classList.remove('active');
        friendlyContent.classList.add('active');
    }

    loadTournaments();
}

function loadTournaments() {
    const sportFilter = document.getElementById('tournament-sport-filter').value;
    const statusFilter = document.getElementById('tournament-status-filter').value;

    const allTournaments = JSON.parse(localStorage.getItem('tournaments') || '[]');

    // Filtrar por tipo (oficial/amistoso)
    const officialTournaments = allTournaments.filter(t => t.tipo === 'oficial');
    const friendlyTournaments = allTournaments.filter(t => t.tipo === 'amistoso');

    // Aplicar filtros
    const filteredOfficial = applyTournamentFilters(officialTournaments, sportFilter, statusFilter);
    const filteredFriendly = applyTournamentFilters(friendlyTournaments, sportFilter, statusFilter);

    // Renderizar torneos oficiales
    renderTournaments(filteredOfficial, 'official-tournaments-list', 'no-official-tournaments');

    // Renderizar torneos amistosos
    renderTournaments(filteredFriendly, 'friendly-tournaments-list', 'no-friendly-tournaments');

    // Actualizar nombre de usuario en vista de torneos
    const userNameTournaments = document.getElementById('user-name-tournaments');
    if (userNameTournaments && currentUser) {
        userNameTournaments.textContent = currentUser.name;
    }
}

function applyTournamentFilters(tournaments, sportFilter, statusFilter) {
    let filtered = [...tournaments];

    if (sportFilter !== 'all') {
        filtered = filtered.filter(t => t.deporte === sportFilter);
    }

    if (statusFilter !== 'all') {
        filtered = filtered.filter(t => t.estado === statusFilter);
    }

    return filtered;
}

function renderTournaments(tournaments, listId, noTournamentsId) {
    const list = document.getElementById(listId);
    const noTournaments = document.getElementById(noTournamentsId);

    list.innerHTML = '';

    if (tournaments.length === 0) {
        noTournaments.style.display = 'block';
        list.style.display = 'none';
    } else {
        noTournaments.style.display = 'none';
        list.style.display = 'grid';

        tournaments.forEach(tournament => {
            const card = createTournamentCard(tournament);
            list.appendChild(card);
        });
    }
}

function createTournamentCard(tournament) {
    const card = document.createElement('div');
    card.className = 'tournament-card';
    card.onclick = () => showTournamentDetail(tournament.id);

    const spotsLeft = tournament.maxParticipantes - tournament.participantesActuales;
    const isFull = spotsLeft === 0;

    card.innerHTML = `
        <div class="tournament-card-header">
            <div class="tournament-card-title">
                <h3>${tournament.nombre}</h3>
            </div>
            <span class="tournament-type-badge ${tournament.tipo}">${tournament.tipo === 'oficial' ? 'OFICIAL UNAB' : 'AMISTOSO'}</span>
        </div>
        <div class="tournament-meta">
            <div class="tournament-meta-item">
                <strong>Deporte:</strong> ${SPORTS_NAMES[tournament.deporte]}
            </div>
            <div class="tournament-meta-item">
                <strong>Inicio:</strong> ${formatDate(tournament.fechaInicio)}
            </div>
            <div class="tournament-meta-item">
                <strong>Organizador:</strong> ${tournament.organizador}
            </div>
            <div class="tournament-meta-item">
                <span class="tournament-status-badge ${tournament.estado}">${tournament.estado.toUpperCase()}</span>
            </div>
        </div>
        <div class="tournament-participants">
            <span class="participants-badge">
                ${tournament.participantesActuales}/${tournament.maxParticipantes} participantes
                ${isFull ? '(LLENO)' : `(${spotsLeft} cupos)`}
            </span>
            <span class="format-badge">${formatTournamentFormat(tournament.formato)}</span>
        </div>
    `;

    return card;
}

function formatTournamentFormat(format) {
    const formats = {
        'eliminacion-directa': 'Eliminación Directa',
        'round-robin': 'Round Robin',
        'grupos': 'Fase de Grupos'
    };
    return formats[format] || format;
}

function showTournamentDetail(tournamentId) {
    const allTournaments = JSON.parse(localStorage.getItem('tournaments') || '[]');
    const tournament = allTournaments.find(t => t.id === tournamentId);

    if (!tournament) return;

    currentTournamentId = tournamentId;

    // Actualizar información del torneo
    document.getElementById('detail-tournament-name').textContent = tournament.nombre;

    const typeBadge = document.getElementById('detail-tournament-type-badge');
    typeBadge.textContent = tournament.tipo === 'oficial' ? 'OFICIAL UNAB' : 'AMISTOSO';
    typeBadge.className = `tournament-type-badge ${tournament.tipo}`;

    const statusBanner = document.getElementById('detail-tournament-status-banner');
    statusBanner.className = `tournament-status-banner ${tournament.estado}`;
    document.getElementById('detail-tournament-status').textContent = tournament.estado.toUpperCase();

    document.getElementById('detail-tournament-sport').textContent = SPORTS_NAMES[tournament.deporte];
    document.getElementById('detail-tournament-start-date').textContent = formatDate(tournament.fechaInicio);
    document.getElementById('detail-tournament-end-date').textContent = formatDate(tournament.fechaFin);
    document.getElementById('detail-tournament-format').textContent = formatTournamentFormat(tournament.formato);
    document.getElementById('detail-tournament-organizer').textContent = tournament.organizador;
    document.getElementById('detail-tournament-description').textContent = tournament.descripcion;
    document.getElementById('detail-tournament-rules').textContent = tournament.reglas;
    document.getElementById('detail-tournament-current-participants').textContent = tournament.participantesActuales;
    document.getElementById('detail-tournament-max-participants').textContent = tournament.maxParticipantes;

    // Actualizar barra de progreso
    const progress = (tournament.participantesActuales / tournament.maxParticipantes) * 100;
    document.getElementById('detail-tournament-progress').style.width = progress + '%';

    // Mostrar premios si es oficial
    const prizesSection = document.getElementById('prizes-section');
    if (tournament.tipo === 'oficial' && tournament.premios) {
        prizesSection.style.display = 'block';
        document.getElementById('detail-tournament-prizes').textContent = tournament.premios;
    } else {
        prizesSection.style.display = 'none';
    }

    // Mostrar/ocultar botón de inscripción
    const joinBtn = document.getElementById('join-tournament-btn');
    const fullMsg = document.getElementById('tournament-full-msg');

    const isFull = tournament.participantesActuales >= tournament.maxParticipantes;
    const isParticipant = tournament.participantes.includes(currentUser.name);
    const isClosed = tournament.estado !== 'abierto';

    if (isFull || isParticipant || isClosed) {
        joinBtn.style.display = 'none';
        fullMsg.style.display = 'block';
        if (isClosed) {
            fullMsg.textContent = 'Este torneo no está aceptando inscripciones';
        } else if (isFull) {
            fullMsg.textContent = 'Este torneo está lleno';
        } else {
            fullMsg.textContent = 'Ya estás inscrito en este torneo';
        }
    } else {
        joinBtn.style.display = 'block';
        fullMsg.style.display = 'none';
    }

    showView('tournament-detail-view');
}

function handleJoinTournament() {
    const allTournaments = JSON.parse(localStorage.getItem('tournaments') || '[]');
    const tournamentIndex = allTournaments.findIndex(t => t.id === currentTournamentId);

    if (tournamentIndex === -1) return;

    const tournament = allTournaments[tournamentIndex];

    // Verificar estado
    if (tournament.estado !== 'abierto') {
        alert('Este torneo no está aceptando inscripciones');
        return;
    }

    // Verificar si hay cupos disponibles
    if (tournament.participantesActuales >= tournament.maxParticipantes) {
        alert('Este torneo ya está lleno');
        return;
    }

    // Verificar si ya está inscrito
    if (tournament.participantes.includes(currentUser.name)) {
        alert('Ya estás inscrito en este torneo');
        return;
    }

    // Agregar participante
    tournament.participantes.push(currentUser.name);
    tournament.participantesActuales++;

    // Guardar cambios
    allTournaments[tournamentIndex] = tournament;
    localStorage.setItem('tournaments', JSON.stringify(allTournaments));

    // Actualizar vista
    showTournamentDetail(currentTournamentId);

    alert('¡Te has inscrito al torneo exitosamente!');
}

function setupCreateTournamentForm() {
    // Configurar opciones según rol del usuario
    const officialOption = document.getElementById('official-option');
    const tournamentType = document.getElementById('tournament-type');

    if (currentUser.role !== 'admin') {
        // Si no es admin, deshabilitar opción oficial
        officialOption.disabled = true;
        officialOption.textContent = 'Oficial UNAB (Solo Administradores)';
        tournamentType.value = 'amistoso';
    } else {
        officialOption.disabled = false;
        tournamentType.value = '';
    }

    // Establecer fechas mínimas
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('tournament-start-date').setAttribute('min', today);
    document.getElementById('tournament-end-date').setAttribute('min', today);
}

function handleCreateTournament(e) {
    e.preventDefault();

    const tipo = document.getElementById('tournament-type').value;
    const deporte = document.getElementById('tournament-sport').value;
    const fechaInicio = document.getElementById('tournament-start-date').value;
    const fechaFin = document.getElementById('tournament-end-date').value;
    const duration = document.getElementById('tournament-duration').value;

    // Validar que los admins puedan crear oficiales
    if (tipo === 'oficial' && currentUser.role !== 'admin') {
        alert('Solo los administradores pueden crear torneos oficiales');
        return;
    }

    // Validar fechas
    const startDate = new Date(fechaInicio);
    const endDate = new Date(fechaFin);

    if (endDate < startDate) {
        alert('La fecha de finalización debe ser posterior a la fecha de inicio');
        return;
    }

    // Validar duración
    if (!duration) {
        alert('Por favor selecciona la duración estimada por partido');
        return;
    }

    // Verificar disponibilidad de canchas para el torneo
    const availabilityResult = checkTournamentAvailability(deporte, fechaInicio, fechaFin);

    if (!availabilityResult.available) {
        alert(availabilityResult.message);
        return;
    }

    // Mostrar advertencia si hay alta ocupación
    if (availabilityResult.warning) {
        if (!confirm(`${availabilityResult.message}\n\n¿Deseas continuar con la creación del torneo?`)) {
            return;
        }
    }

    const newTournament = {
        id: generateId(),
        tipo: tipo,
        nombre: document.getElementById('tournament-name').value,
        deporte: deporte,
        fechaInicio: fechaInicio,
        fechaFin: fechaFin,
        duration: parseFloat(duration),
        maxParticipantes: parseInt(document.getElementById('tournament-max-participants').value),
        participantesActuales: 1,
        formato: document.getElementById('tournament-format').value,
        descripcion: document.getElementById('tournament-description').value,
        reglas: document.getElementById('tournament-rules').value,
        premios: tipo === 'oficial' ? document.getElementById('tournament-prizes').value : '',
        organizador: currentUser.name,
        participantes: [currentUser.name],
        estado: 'abierto'
    };

    // Guardar torneo
    const allTournaments = JSON.parse(localStorage.getItem('tournaments') || '[]');
    allTournaments.push(newTournament);
    localStorage.setItem('tournaments', JSON.stringify(allTournaments));

    // Limpiar formulario
    document.getElementById('create-tournament-form').reset();
    document.getElementById('tournament-availability-info').style.display = 'none';

    // Volver a la vista de torneos
    showView('tournaments-view');
    loadTournaments();

    alert('¡Torneo creado exitosamente! Se ha verificado la disponibilidad de canchas.');
}

// ============================================
// UTILIDADES
// ============================================

function generateId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
}

function formatDate(dateString) {
    const date = new Date(dateString + 'T00:00:00');
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('es-ES', options);
}

// ============================================
// FUNCIONES DE DISPONIBILIDAD DE CANCHAS
// ============================================

// Obtener todas las reservas
function getReservations() {
    return JSON.parse(localStorage.getItem('courtReservations') || '[]');
}

// Guardar reservas
function saveReservations(reservations) {
    localStorage.setItem('courtReservations', JSON.stringify(reservations));
}

// Crear una nueva reserva
function createCourtReservation(eventId, sport, courtId, date, time, duration) {
    const reservations = getReservations();
    const newReservation = {
        id: generateId(),
        eventId: eventId,
        sport: sport,
        courtId: courtId,
        date: date,
        time: time,
        duration: parseFloat(duration),
        createdAt: new Date().toISOString()
    };
    reservations.push(newReservation);
    saveReservations(reservations);
    return newReservation;
}

// Verificar si hay conflicto de horarios
function hasTimeConflict(time1, duration1, time2, duration2) {
    // Convertir tiempo HH:MM a minutos desde medianoche
    const timeToMinutes = (time) => {
        const [hours, minutes] = time.split(':').map(Number);
        return hours * 60 + minutes;
    };

    const start1 = timeToMinutes(time1);
    const end1 = start1 + (duration1 * 60);
    const start2 = timeToMinutes(time2);
    const end2 = start2 + (duration2 * 60);

    // Hay conflicto si los rangos se solapan
    return (start1 < end2 && end1 > start2);
}

// Verificar disponibilidad de una cancha específica
function checkCourtAvailability(courtId, sport, date, time, duration, excludeEventId = null) {
    const reservations = getReservations();
    const court = findCourtById(courtId);

    if (!court) return false;

    // Obtener deportes que comparten esta cancha
    const sharedSports = court.shared || [sport];

    // Buscar conflictos
    for (const reservation of reservations) {
        // Saltar si es el mismo evento (para ediciones)
        if (excludeEventId && reservation.eventId === excludeEventId) {
            continue;
        }

        // Verificar si es la misma cancha y fecha
        if (reservation.courtId === courtId && reservation.date === date) {
            // Verificar si hay conflicto de horario
            if (hasTimeConflict(time, duration, reservation.time, reservation.duration)) {
                return false;
            }
        }
    }

    return true;
}

// Encontrar cancha por ID
function findCourtById(courtId) {
    // Buscar en canchas regulares
    for (const sport in FACILITIES) {
        const facility = FACILITIES[sport];
        const court = facility.courts.find(c => c.id === courtId);
        if (court) return court;
    }

    // Verificar si es el coliseo
    if (courtId === COLISEO.id) {
        return COLISEO;
    }

    return null;
}

// Obtener canchas disponibles para un deporte en fecha/hora específica
function getAvailableCourts(sport, date, time, duration) {
    const available = [];

    if (!FACILITIES[sport]) return available;

    const courts = FACILITIES[sport].courts;

    // Verificar cada cancha
    for (const court of courts) {
        if (checkCourtAvailability(court.id, sport, date, time, duration)) {
            available.push(court);
        }
    }

    // Agregar coliseo si el usuario es admin y el deporte es compatible
    if (currentUser && currentUser.role === 'admin' && COLISEO.shared.includes(sport)) {
        if (checkCourtAvailability(COLISEO.id, sport, date, time, duration)) {
            available.push(COLISEO);
        }
    }

    return available;
}

// Contar canchas totales disponibles (sin fecha/hora)
function getTotalCourts(sport) {
    if (!FACILITIES[sport]) return 0;
    let total = FACILITIES[sport].courts.length;

    // Agregar coliseo si aplica
    if (currentUser && currentUser.role === 'admin' && COLISEO.shared.includes(sport)) {
        total += 1;
    }

    return total;
}

// Liberar reserva de cancha cuando se cancela un evento
function releaseCourtReservation(eventId) {
    const reservations = getReservations();
    const updatedReservations = reservations.filter(r => r.eventId !== eventId);
    saveReservations(updatedReservations);
}

// Verificar disponibilidad general para torneos (rango de fechas)
function checkTournamentAvailability(sport, startDate, endDate) {
    const totalCourts = getTotalCourts(sport);
    if (totalCourts === 0) return { available: false, message: 'No hay canchas disponibles para este deporte' };

    // Para torneos, verificamos que haya al menos una cancha disponible
    // Esta es una verificación simplificada - en producción sería más compleja
    const reservations = getReservations();
    const start = new Date(startDate);
    const end = new Date(endDate);

    // Contar reservas en el rango de fechas
    let conflictCount = 0;
    for (const reservation of reservations) {
        const resDate = new Date(reservation.date);
        if (resDate >= start && resDate <= end &&
            (reservation.sport === sport ||
             (sport === 'futbol' && reservation.sport === 'basketball') ||
             (sport === 'basketball' && reservation.sport === 'futbol'))) {
            conflictCount++;
        }
    }

    // Advertencia si hay muchas reservas
    const daysInRange = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
    const maxReservations = totalCourts * daysInRange * 8; // Asumiendo 8 slots por día
    const occupancyRate = (conflictCount / maxReservations) * 100;

    if (occupancyRate > 70) {
        return {
            available: true,
            warning: true,
            message: `Alta demanda de canchas en estas fechas (${occupancyRate.toFixed(0)}% ocupado). Se recomienda coordinar horarios con anticipación.`
        };
    }

    return {
        available: true,
        message: `Disponibilidad confirmada. ${totalCourts} cancha(s) disponible(s) para ${SPORTS_NAMES[sport]}.`
    };
}

// ============================================
// GESTIÓN DE FECHA MÍNIMA
// ============================================

// Establecer fecha mínima en el formulario (hoy)
const eventDateInput = document.getElementById('event-date');
if (eventDateInput) {
    const today = new Date().toISOString().split('T')[0];
    eventDateInput.setAttribute('min', today);
}

// ============================================
// FUNCIONES DE PERFIL
// ============================================

// Generar color de avatar basado en el nombre
function generateAvatarColor(name) {
    const colors = [
        '#FF6B35', '#FFB627', '#4CAF50', '#2196F3',
        '#9C27B0', '#E91E63', '#00BCD4', '#FF9800'
    ];
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return colors[Math.abs(hash) % colors.length];
}

// Inicializar iconos de Feather
function initializeFeatherIcons() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
}

// Actualizar avatar en header y perfil
function updateAvatars() {
    if (!currentUser) return;

    const initials = currentUser.fullName ? currentUser.fullName.charAt(0).toUpperCase() : currentUser.name.charAt(0).toUpperCase();
    const color = currentUser.avatarColor || generateAvatarColor(currentUser.name);

    // Avatar del header
    const headerAvatar = document.getElementById('header-avatar');
    const headerAvatarText = document.getElementById('header-avatar-text');
    if (headerAvatar && headerAvatarText) {
        headerAvatar.style.background = color;
        headerAvatarText.textContent = initials;
    }

    // Avatar del perfil
    const profileAvatar = document.getElementById('profile-avatar');
    const profileAvatarText = document.getElementById('profile-avatar-text');
    if (profileAvatar && profileAvatarText) {
        profileAvatar.style.background = color;
        profileAvatarText.textContent = initials;
    }
}

// Mostrar vista de perfil
function showProfile() {
    if (!currentUser) return;

    // Actualizar información básica
    document.getElementById('profile-full-name').textContent = currentUser.fullName || currentUser.name;
    document.getElementById('profile-email').textContent = currentUser.email;
    document.getElementById('profile-bio').textContent = currentUser.bio || 'Sin descripción';
    document.getElementById('user-name-profile').textContent = currentUser.name;

    // Actualizar información adicional
    document.getElementById('profile-program').textContent = currentUser.program || 'No especificado';
    document.getElementById('profile-semester').textContent = currentUser.semester || 'No especificado';
    document.getElementById('profile-code').textContent = currentUser.code || 'No especificado';

    // Actualizar deportes favoritos
    const favoriteSportsList = document.getElementById('profile-favorite-sports');
    favoriteSportsList.innerHTML = '';
    if (currentUser.favoriteSports && currentUser.favoriteSports.length > 0) {
        currentUser.favoriteSports.forEach(sport => {
            const sportTag = document.createElement('div');
            sportTag.className = 'sport-tag';
            sportTag.innerHTML = `${getSportEmoji(sport)} ${SPORTS_NAMES[sport]}`;
            favoriteSportsList.appendChild(sportTag);
        });
    } else {
        favoriteSportsList.innerHTML = '<p style="color: var(--texto-medio);">No has seleccionado deportes favoritos</p>';
    }

    // Calcular y mostrar estadísticas
    calculateAndDisplayStats();

    // Mostrar eventos y torneos
    loadProfileEvents();
    loadProfileTournaments();

    // Actualizar avatares
    updateAvatars();

    // Crear gráfico de deportes
    createSportsChart();

    // Mostrar vista
    showView('profile-view');
    initializeFeatherIcons();
}

// Obtener emoji del deporte
function getSportEmoji(sport) {
    const emojis = {
        futbol: '⚽',
        basketball: '🏀',
        tenis: '🎾',
        padel: '🏸',
        volleyball: '🏐',
        billar: '🎱',
        pingpong: '🏓'
    };
    return emojis[sport] || '🏃';
}

// Calcular estadísticas
function calculateAndDisplayStats() {
    const allEvents = JSON.parse(localStorage.getItem('events') || '[]');
    const allTournaments = JSON.parse(localStorage.getItem('tournaments') || '[]');

    // Eventos creados
    const eventsCreated = allEvents.filter(e => e.organizer === currentUser.name).length;
    document.getElementById('stat-events-created').textContent = eventsCreated;

    // Eventos unidos (participante pero no organizador)
    const eventsJoined = allEvents.filter(e =>
        e.participants.includes(currentUser.name) && e.organizer !== currentUser.name
    ).length;
    document.getElementById('stat-events-joined').textContent = eventsJoined;

    // Torneos participados
    const tournamentsJoined = allTournaments.filter(t =>
        t.participantes.includes(currentUser.name)
    ).length;
    document.getElementById('stat-tournaments-joined').textContent = tournamentsJoined;

    // Total partidos jugados
    const totalMatches = eventsCreated + eventsJoined + tournamentsJoined;
    document.getElementById('stat-total-matches').textContent = totalMatches;

    // Deporte más jugado
    const sportCounts = {};
    allEvents.filter(e => e.participants.includes(currentUser.name)).forEach(e => {
        sportCounts[e.sport] = (sportCounts[e.sport] || 0) + 1;
    });
    allTournaments.filter(t => t.participantes.includes(currentUser.name)).forEach(t => {
        sportCounts[t.deporte] = (sportCounts[t.deporte] || 0) + 1;
    });

    let favoriteSport = 'futbol';
    let maxCount = 0;
    for (const [sport, count] of Object.entries(sportCounts)) {
        if (count > maxCount) {
            maxCount = count;
            favoriteSport = sport;
        }
    }

    document.getElementById('favorite-sport-icon').textContent = getSportEmoji(favoriteSport);
    document.getElementById('favorite-sport-name').textContent = SPORTS_NAMES[favoriteSport] || 'Fútbol';
    document.getElementById('favorite-sport-count').textContent = `${maxCount} partidos`;

    const percentage = totalMatches > 0 ? Math.round((maxCount / totalMatches) * 100) : 0;
    document.getElementById('favorite-sport-percentage').style.width = percentage + '%';
    document.getElementById('favorite-sport-percent-text').textContent = percentage + '%';

    // Racha de actividad
    const now = new Date();
    const currentMonth = now.getMonth();
    const currentYear = now.getFullYear();

    const thisMonthEvents = allEvents.filter(e => {
        if (!e.participants.includes(currentUser.name)) return false;
        const eventDate = new Date(e.date);
        return eventDate.getMonth() === currentMonth && eventDate.getFullYear() === currentYear;
    }).length;
    document.getElementById('streak-this-month').textContent = thisMonthEvents;

    const upcomingEvents = allEvents.filter(e => {
        if (!e.participants.includes(currentUser.name)) return false;
        const eventDate = new Date(e.date);
        return eventDate >= now;
    }).length;
    document.getElementById('streak-upcoming').textContent = upcomingEvents;

    const completedEvents = allEvents.filter(e => {
        if (!e.participants.includes(currentUser.name)) return false;
        const eventDate = new Date(e.date);
        return eventDate < now;
    }).length;
    document.getElementById('streak-completed').textContent = completedEvents;
}

// Cargar eventos del perfil
function loadProfileEvents() {
    const allEvents = JSON.parse(localStorage.getItem('events') || '[]');
    const now = new Date();

    // Eventos creados
    const eventsCreated = allEvents.filter(e => e.organizer === currentUser.name);
    const eventsCreatedList = document.getElementById('events-created-list');
    const noEventsCreated = document.getElementById('no-events-created');

    eventsCreatedList.innerHTML = '';
    if (eventsCreated.length === 0) {
        noEventsCreated.style.display = 'block';
    } else {
        noEventsCreated.style.display = 'none';
        eventsCreated.forEach(event => {
            const card = createProfileEventCard(event, 'created', now);
            eventsCreatedList.appendChild(card);
        });
    }

    // Eventos unidos
    const eventsJoined = allEvents.filter(e =>
        e.participants.includes(currentUser.name) && e.organizer !== currentUser.name
    );
    const eventsJoinedList = document.getElementById('events-joined-list');
    const noEventsJoined = document.getElementById('no-events-joined');

    eventsJoinedList.innerHTML = '';
    if (eventsJoined.length === 0) {
        noEventsJoined.style.display = 'block';
    } else {
        noEventsJoined.style.display = 'none';
        eventsJoined.forEach(event => {
            const card = createProfileEventCard(event, 'joined', now);
            eventsJoinedList.appendChild(card);
        });
    }

    initializeFeatherIcons();
}

// Crear card de evento para perfil
function createProfileEventCard(event, type, now) {
    const card = document.createElement('div');
    card.className = 'profile-event-card';

    const eventDate = new Date(event.date);
    const isPast = eventDate < now;
    const statusClass = isPast ? 'past' : 'upcoming';
    const statusText = isPast ? 'Pasado' : 'Próximo';

    card.innerHTML = `
        <div class="profile-event-header">
            <h4>${event.name}</h4>
            <span class="event-status-badge ${statusClass}">${statusText}</span>
        </div>
        <div class="profile-event-meta">
            <span><i data-feather="calendar"></i> ${formatDate(event.date)}</span>
            <span><i data-feather="clock"></i> ${event.time}</span>
            <span><i data-feather="users"></i> ${event.currentPlayers}/${event.maxPlayers}</span>
        </div>
        <div class="profile-event-actions">
            <button class="btn-small btn-view" onclick="showEventDetail('${event.id}')">
                <i data-feather="eye"></i> Ver
            </button>
            ${type === 'created' ?
                `<button class="btn-small btn-cancel" onclick="cancelEvent('${event.id}')">
                    <i data-feather="x-circle"></i> Cancelar
                </button>` :
                `<button class="btn-small btn-leave" onclick="leaveEvent('${event.id}')">
                    <i data-feather="log-out"></i> Abandonar
                </button>`
            }
        </div>
    `;

    return card;
}

// Cargar torneos del perfil
function loadProfileTournaments() {
    const allTournaments = JSON.parse(localStorage.getItem('tournaments') || '[]');
    const tournamentList = document.getElementById('profile-tournaments-list');
    const noTournaments = document.getElementById('no-tournaments');

    const userTournaments = allTournaments.filter(t =>
        t.participantes.includes(currentUser.name)
    );

    tournamentList.innerHTML = '';
    if (userTournaments.length === 0) {
        noTournaments.style.display = 'block';
    } else {
        noTournaments.style.display = 'none';
        userTournaments.forEach(tournament => {
            const card = createProfileTournamentCard(tournament);
            tournamentList.appendChild(card);
        });
    }

    initializeFeatherIcons();
}

// Crear card de torneo para perfil
function createProfileTournamentCard(tournament) {
    const card = document.createElement('div');
    card.className = 'profile-tournament-card';

    card.innerHTML = `
        <div class="profile-event-header">
            <h4>${tournament.nombre}</h4>
            <span class="tournament-status-badge ${tournament.estado}">${tournament.estado.toUpperCase()}</span>
        </div>
        <div class="profile-event-meta">
            <span><i data-feather="calendar"></i> ${formatDate(tournament.fechaInicio)}</span>
            <span><i data-feather="award"></i> ${SPORTS_NAMES[tournament.deporte]}</span>
            <span><i data-feather="users"></i> ${tournament.participantesActuales}/${tournament.maxParticipantes}</span>
        </div>
        <div class="profile-event-actions">
            <button class="btn-small btn-view" onclick="showTournamentDetail('${tournament.id}')">
                <i data-feather="eye"></i> Ver
            </button>
            ${tournament.organizador === currentUser.name ?
                `<button class="btn-small btn-cancel" onclick="cancelTournament('${tournament.id}')">
                    <i data-feather="x-circle"></i> Cancelar
                </button>` :
                `<button class="btn-small btn-leave" onclick="leaveTournament('${tournament.id}')">
                    <i data-feather="log-out"></i> Abandonar
                </button>`
            }
        </div>
    `;

    return card;
}

// Cancelar evento
function cancelEvent(eventId) {
    if (!confirm('¿Estás seguro de que quieres cancelar este evento? Esta acción no se puede deshacer.')) {
        return;
    }

    const allEvents = JSON.parse(localStorage.getItem('events') || '[]');
    const updatedEvents = allEvents.filter(e => e.id !== eventId);
    localStorage.setItem('events', JSON.stringify(updatedEvents));

    // Liberar reserva de cancha
    releaseCourtReservation(eventId);

    alert('Evento cancelado exitosamente. La cancha ha sido liberada.');
    showProfile();
}

// Abandonar evento
function leaveEvent(eventId) {
    if (!confirm('¿Estás seguro de que quieres abandonar este evento?')) {
        return;
    }

    const allEvents = JSON.parse(localStorage.getItem('events') || '[]');
    const eventIndex = allEvents.findIndex(e => e.id === eventId);

    if (eventIndex !== -1) {
        const event = allEvents[eventIndex];
        event.participants = event.participants.filter(p => p !== currentUser.name);
        event.currentPlayers--;

        // Si no quedan participantes, cancelar el evento y liberar la cancha
        if (event.currentPlayers === 0) {
            allEvents.splice(eventIndex, 1);
            releaseCourtReservation(eventId);
            alert('Has abandonado el evento. Como eras el único participante, el evento ha sido cancelado y la cancha liberada.');
        } else {
            allEvents[eventIndex] = event;
            alert('Has abandonado el evento exitosamente');
        }

        localStorage.setItem('events', JSON.stringify(allEvents));
        showProfile();
    }
}

// Cancelar torneo
function cancelTournament(tournamentId) {
    if (!confirm('¿Estás seguro de que quieres cancelar este torneo? Esta acción no se puede deshacer.')) {
        return;
    }

    const allTournaments = JSON.parse(localStorage.getItem('tournaments') || '[]');
    const updatedTournaments = allTournaments.filter(t => t.id !== tournamentId);
    localStorage.setItem('tournaments', JSON.stringify(updatedTournaments));

    alert('Torneo cancelado exitosamente');
    showProfile();
}

// Abandonar torneo
function leaveTournament(tournamentId) {
    if (!confirm('¿Estás seguro de que quieres abandonar este torneo?')) {
        return;
    }

    const allTournaments = JSON.parse(localStorage.getItem('tournaments') || '[]');
    const tournamentIndex = allTournaments.findIndex(t => t.id === tournamentId);

    if (tournamentIndex !== -1) {
        const tournament = allTournaments[tournamentIndex];
        tournament.participantes = tournament.participantes.filter(p => p !== currentUser.name);
        tournament.participantesActuales--;
        allTournaments[tournamentIndex] = tournament;
        localStorage.setItem('tournaments', JSON.stringify(allTournaments));

        alert('Has abandonado el torneo exitosamente');
        showProfile();
    }
}

// Crear gráfico de deportes
let sportsChartInstance = null;

function createSportsChart() {
    const canvas = document.getElementById('sports-chart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    // Destruir gráfico anterior si existe
    if (sportsChartInstance) {
        sportsChartInstance.destroy();
    }

    // Obtener datos
    const allEvents = JSON.parse(localStorage.getItem('events') || '[]');
    const allTournaments = JSON.parse(localStorage.getItem('tournaments') || '[]');

    const sportCounts = {};
    allEvents.filter(e => e.participants.includes(currentUser.name)).forEach(e => {
        sportCounts[e.sport] = (sportCounts[e.sport] || 0) + 1;
    });
    allTournaments.filter(t => t.participantes.includes(currentUser.name)).forEach(t => {
        sportCounts[t.deporte] = (sportCounts[t.deporte] || 0) + 1;
    });

    const labels = Object.keys(sportCounts).map(sport => SPORTS_NAMES[sport]);
    const data = Object.values(sportCounts);

    // Si no hay datos, mostrar mensaje
    if (data.length === 0) {
        ctx.font = '16px Inter';
        ctx.fillStyle = '#999';
        ctx.textAlign = 'center';
        ctx.fillText('No hay datos para mostrar', canvas.width / 2, canvas.height / 2);
        return;
    }

    // Crear gráfico
    sportsChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    '#FF6B35',
                    '#FFB627',
                    '#4CAF50',
                    '#2196F3',
                    '#9C27B0',
                    '#E91E63',
                    '#00BCD4'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12,
                            family: 'Inter'
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

// Editar perfil - Abrir modal
function openEditProfileModal() {
    if (!currentUser) return;

    // Llenar formulario con datos actuales
    document.getElementById('edit-full-name').value = currentUser.fullName || currentUser.name;
    document.getElementById('edit-bio').value = currentUser.bio || '';
    document.getElementById('edit-program').value = currentUser.program || '';
    document.getElementById('edit-semester').value = currentUser.semester || '';
    document.getElementById('edit-code').value = currentUser.code || '';

    // Marcar deportes favoritos
    const allSports = ['futbol', 'basketball', 'tenis', 'padel', 'volleyball', 'billar', 'pingpong'];
    allSports.forEach(sport => {
        const checkbox = document.getElementById(`fav-${sport}`);
        if (checkbox) {
            checkbox.checked = currentUser.favoriteSports && currentUser.favoriteSports.includes(sport);
        }
    });

    // Seleccionar color de avatar actual
    const colorOptions = document.querySelectorAll('.color-option');
    colorOptions.forEach(option => {
        option.classList.remove('selected');
        if (option.dataset.color === currentUser.avatarColor) {
            option.classList.add('selected');
        }
    });

    // Mostrar modal
    document.getElementById('edit-profile-modal').classList.add('active');
    initializeFeatherIcons();
}

// Cerrar modal
function closeEditProfileModal() {
    document.getElementById('edit-profile-modal').classList.remove('active');
}

// Guardar cambios del perfil
function saveProfileChanges(e) {
    e.preventDefault();

    // Obtener datos del formulario
    currentUser.fullName = document.getElementById('edit-full-name').value;
    currentUser.bio = document.getElementById('edit-bio').value;
    currentUser.program = document.getElementById('edit-program').value;
    currentUser.semester = document.getElementById('edit-semester').value;
    currentUser.code = document.getElementById('edit-code').value;

    // Obtener deportes favoritos seleccionados
    const favoriteSports = [];
    const allSports = ['futbol', 'basketball', 'tenis', 'padel', 'volleyball', 'billar', 'pingpong'];
    allSports.forEach(sport => {
        const checkbox = document.getElementById(`fav-${sport}`);
        if (checkbox && checkbox.checked) {
            favoriteSports.push(sport);
        }
    });
    currentUser.favoriteSports = favoriteSports;

    // Obtener color de avatar seleccionado
    const selectedColor = document.querySelector('.color-option.selected');
    if (selectedColor) {
        currentUser.avatarColor = selectedColor.dataset.color;
    }

    // Guardar en localStorage
    localStorage.setItem('currentUser', JSON.stringify(currentUser));
    localStorage.setItem(`user_${currentUser.email}`, JSON.stringify(currentUser));

    // Cerrar modal y actualizar perfil
    closeEditProfileModal();
    showProfile();
    updateAvatars();

    alert('Perfil actualizado exitosamente');
}

// Setup de event listeners para perfil
function setupProfileEventListeners() {
    // Botón de perfil en header
    const profileBtn = document.getElementById('profile-btn');
    if (profileBtn) {
        profileBtn.addEventListener('click', showProfile);
    }

    // Botón de volver desde perfil
    const backToDashboardProfile = document.getElementById('back-to-dashboard-profile');
    if (backToDashboardProfile) {
        backToDashboardProfile.addEventListener('click', () => {
            showView('dashboard-view');
        });
    }

    // Botón de editar perfil
    const editProfileBtn = document.getElementById('edit-profile-btn');
    if (editProfileBtn) {
        editProfileBtn.addEventListener('click', openEditProfileModal);
    }

    const changeAvatarBtn = document.getElementById('change-avatar-btn');
    if (changeAvatarBtn) {
        changeAvatarBtn.addEventListener('click', openEditProfileModal);
    }

    // Botones del modal
    const closeEditModal = document.getElementById('close-edit-modal');
    if (closeEditModal) {
        closeEditModal.addEventListener('click', closeEditProfileModal);
    }

    const cancelEditBtn = document.getElementById('cancel-edit-btn');
    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', closeEditProfileModal);
    }

    // Formulario de edición
    const editProfileForm = document.getElementById('edit-profile-form');
    if (editProfileForm) {
        editProfileForm.addEventListener('submit', saveProfileChanges);
    }

    // Selector de color de avatar
    const colorOptions = document.querySelectorAll('.color-option');
    colorOptions.forEach(option => {
        option.addEventListener('click', function() {
            colorOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
        });
    });

    // Tabs de eventos en perfil
    const profileTabBtns = document.querySelectorAll('.profile-tab-btn');
    profileTabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;

            // Actualizar botones
            profileTabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // Actualizar contenido
            document.querySelectorAll('.profile-tab-content').forEach(content => {
                content.classList.remove('active');
            });

            if (tab === 'created') {
                document.getElementById('profile-events-created').classList.add('active');
            } else if (tab === 'joined') {
                document.getElementById('profile-events-joined').classList.add('active');
            }

            initializeFeatherIcons();
        });
    });

    // Cerrar modal al hacer click fuera
    const modal = document.getElementById('edit-profile-modal');
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeEditProfileModal();
            }
        });
    }
}

// Llamar setup de event listeners cuando se inicializa la app
document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
    setupEventListeners();
    setupProfileEventListeners();
    loadSampleData();
    initializeFeatherIcons();
    if (currentUser) {
        updateAvatars();
    }
});