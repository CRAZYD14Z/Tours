// Estado inicial de los asientos
const seatPrice = 450;
let selectedSeats = [];
let seats = [];

// Asientos que estarán ocupados por defecto (hardcodeados)
const initialOccupiedSeats = [5, 8, 17, 33];

// Generar estado inicial de asientos
function generateSeats() {
    const seatsGrid = document.getElementById('seatsGrid');
    seatsGrid.innerHTML = ''; // Limpiar asientos existentes antes de regenerar
    seats = [];
    let currentSeatNumber = 1; // Contador para la numeración continua de asientos

    // Generar asientos con el nuevo layout, ahora incluyendo una fila extra al final (0-8 para 9 filas)
    for (let row = 0; row < 9; row++) { // Cambiado de < 8 a < 9 para la fila extra
        // Fila 3 (índice 2): solo asientos laterales
        if (row === 2) {
            // Lado izquierdo - asiento exterior
            const seat = createSeat(currentSeatNumber);
            seatsGrid.appendChild(seat);
            seats.push({ id: currentSeatNumber, status: getInitialSeatStatus(currentSeatNumber) });
            currentSeatNumber++;

            // Espacio vacío para asiento interior
            const emptyLeft = document.createElement('div');
            emptyLeft.className = 'seat empty-seat';
            seatsGrid.appendChild(emptyLeft);

            // Divisor de pasillo
            const aisle = document.createElement('div');
            aisle.className = 'aisle-divider';
            seatsGrid.appendChild(aisle);

            // Lado derecho - espacio vacío para asiento interior
            const emptyRight = document.createElement('div');
            emptyRight.className = 'seat empty-seat';
            seatsGrid.appendChild(emptyRight);

            // Lado derecho - asiento exterior
            const seatRight = createSeat(currentSeatNumber);
            seatsGrid.appendChild(seatRight);
            seats.push({ id: currentSeatNumber, status: getInitialSeatStatus(currentSeatNumber) });
            currentSeatNumber++;
            continue;
        }

        // Fila 4 (índice 3): solo asientos al lado del pasillo
        else if (row === 3) { // Usar else if para que solo una condición se cumpla por fila
            // Lado izquierdo - espacio vacío para asiento exterior
            const emptyLeftOuter = document.createElement('div');
            emptyLeftOuter.className = 'seat empty-seat';
            seatsGrid.appendChild(emptyLeftOuter);

            // Lado izquierdo - asiento interior
            const seatLeft = createSeat(currentSeatNumber);
            seatsGrid.appendChild(seatLeft);
            seats.push({ id: currentSeatNumber, status: getInitialSeatStatus(currentSeatNumber) });
            currentSeatNumber++;

            // Divisor de pasillo
            const aisle = document.createElement('div');
            aisle.className = 'aisle-divider';
            seatsGrid.appendChild(aisle);

            // Lado derecho - asiento interior
            const seatRight = createSeat(currentSeatNumber);
            seatsGrid.appendChild(seatRight);
            seats.push({ id: currentSeatNumber, status: getInitialSeatStatus(currentSeatNumber) });
            currentSeatNumber++;

            // Lado derecho - espacio vacío para asiento exterior
            const emptyRightOuter = document.createElement('div');
            emptyRightOuter.className = 'seat empty-seat';
            seatsGrid.appendChild(emptyRightOuter);
            continue;
        }
        // Nueva última fila (índice 8): 5 asientos
        else if (row === 8) {
            for (let col = 1; col <= 5; col++) { // 5 asientos para la última fila
                const seat = createSeat(currentSeatNumber);
                seatsGrid.appendChild(seat);
                seats.push({ id: currentSeatNumber, status: getInitialSeatStatus(currentSeatNumber) });
                currentSeatNumber++;
            }
        }
        // Filas regulares: 2 asientos por cada lado del pasillo
        else { // Esto ahora cubre las filas 0, 1, 4, 5, 6, 7
            // Columnas 1-2 (lado izquierdo)
            for (let col = 1; col <= 2; col++) {
                const seat = createSeat(currentSeatNumber);
                seatsGrid.appendChild(seat);
                seats.push({ id: currentSeatNumber, status: getInitialSeatStatus(currentSeatNumber) });
                currentSeatNumber++;
            }

            // Divisor de pasillo
            const aisle = document.createElement('div');
            aisle.className = 'aisle-divider';
            seatsGrid.appendChild(aisle);

            // Columnas 3-4 (lado derecho)
            for (let col = 3; col <= 4; col++) {
                const seat = createSeat(currentSeatNumber);
                seatsGrid.appendChild(seat);
                seats.push({ id: currentSeatNumber, status: getInitialSeatStatus(currentSeatNumber) });
                currentSeatNumber++;
            }
        }
    }
}

function createSeat(seatNumber) {
    const seat = document.createElement('div');
    seat.className = 'seat';
    seat.dataset.seatNumber = seatNumber; // Almacena el número de asiento
    
    // Obtiene el estado inicial basado en si está en la lista de ocupados
    const status = getInitialSeatStatus(seatNumber);
    seat.classList.add(status);
    seat.textContent = seatNumber; // Muestra el número de asiento
    
    seat.addEventListener('click', () => toggleSeat(seatNumber));
    
    return seat;
}

// Determina el estado inicial de un asiento
function getInitialSeatStatus(seatNumber) {
    return initialOccupiedSeats.includes(seatNumber) ? 'occupied' : 'available';
}

// Alternar selección de asiento
function toggleSeat(seatId) {
    const seat = seats.find(s => s.id === seatId);
    
    if (seat.status === 'occupied') return;
    
    const seatElement = document.querySelector(`[data-seat-number="${seatId}"]`);
    
    if (selectedSeats.includes(seatId)) {
        // Deseleccionar
        selectedSeats = selectedSeats.filter(id => id !== seatId);
        seatElement.classList.remove('selected');
        seatElement.classList.add('available');
    } else {
        // Seleccionar
        selectedSeats.push(seatId);
        seatElement.classList.remove('available');
        seatElement.classList.add('selected');
    }
    
    updateSelectionInfo();
}

// Actualizar información de selección
function updateSelectionInfo() {
    const selectedSeatsElement = document.getElementById('selectedSeats');
    const totalPriceElement = document.getElementById('totalPrice');
    const confirmBtn = document.getElementById('confirmBtn');
    
    if (selectedSeats.length === 0) {
        selectedSeatsElement.textContent = 'Ningún asiento seleccionado';
    } else {
        const seatText = selectedSeats.length === 1 ? 'asiento' : 'asientos';
        selectedSeats.sort((a, b) => a - b); // Ordenar asientos numéricamente
        selectedSeatsElement.textContent = `${selectedSeats.length} ${seatText} seleccionados: ${selectedSeats.join(', ')}`;
    }
    
    const totalPrice = selectedSeats.length * seatPrice;
    totalPriceElement.textContent = totalPrice.toLocaleString();
    
    confirmBtn.disabled = selectedSeats.length === 0;
}

// Confirmar selección
async function confirmSelection() {
    const departureId = new URLSearchParams(window.location.search).get('departure_id') || '1';
    try {
        await ToursApi.createBooking(departureId, selectedSeats);
    } catch (error) {
        alert(error.message);
        return;
    }

    const modal = document.getElementById('successModal');
    modal.style.display = 'block';
    
    // Marcar asientos como ocupados
    selectedSeats.forEach(seatId => {
        const seat = seats.find(s => s.id === seatId);
        seat.status = 'occupied';
        
        const seatElement = document.querySelector(`[data-seat-number="${seatId}"]`);
        seatElement.classList.remove('selected');
        seatElement.classList.add('occupied');
    });
    
    selectedSeats = [];
    updateSelectionInfo();
}

// Cerrar modal
function closeModal() {
    const modal = document.getElementById('successModal');
    modal.style.display = 'none';
}

// Efecto ripple en botones
function createRipple(event) {
    const button = event.currentTarget;
    const ripple = button.querySelector('.ripple');
    
    const diameter = Math.max(button.clientWidth, button.clientHeight);
    const radius = diameter / 2;
    
    ripple.style.width = ripple.style.height = `${diameter}px`;
    ripple.style.left = `${event.clientX - button.offsetLeft - radius}px`;
    ripple.style.top = `${event.clientY - button.offsetTop - radius}px`;
    
    ripple.classList.add('ripple');
    
    setTimeout(() => {
        ripple.classList.remove('ripple');
    }, 600);
}

// Inicializar la aplicación
document.addEventListener('DOMContentLoaded', () => {
    generateSeats();

    const departureId = new URLSearchParams(window.location.search).get('departure_id');
    if (departureId) {
        ToursApi.getSeats(departureId).then(({ data }) => {
            data.forEach(({ seat_number: seatNumber, status }) => {
                const seat = seats.find(item => item.id === Number(seatNumber));
                const seatElement = document.querySelector(`[data-seat-number="${seatNumber}"]`);
                if (seat && seatElement && status === 'occupied') {
                    seat.status = 'occupied';
                    seatElement.classList.remove('available');
                    seatElement.classList.add('occupied');
                }
            });
        }).catch(error => alert(error.message));
    }
    
    const confirmBtn = document.getElementById('confirmBtn');
    const closeBtn = document.getElementById('closeModal');
    
    confirmBtn.addEventListener('click', confirmSelection);
    confirmBtn.addEventListener('click', createRipple);
    closeBtn.addEventListener('click', closeModal);
    
    // Cerrar modal al hacer clic fuera
    window.addEventListener('click', (e) => {
        const modal = document.getElementById('successModal');
        if (e.target === modal) {
            closeModal();
        }
    });
});