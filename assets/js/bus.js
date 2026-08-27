const seatPrice = 450;
let selectedSeats = [];
let seats = [];
const bookingParams = new URLSearchParams(window.location.search);
const passengerCounts = {
    adults: Math.max(0, Number(bookingParams.get('adults') || 0)),
    children: Math.max(0, Number(bookingParams.get('children') || 0)),
    seniors: Math.max(0, Number(bookingParams.get('seniors') || 0))
};
const requiredPassengers = Object.values(passengerCounts).reduce((total, count) => total + count, 0);

function renderSeatsGrid(layout, seatData) {
    const seatsGrid = document.getElementById('seatsGrid');
    seatsGrid.innerHTML = '';
    seats = [];
    const seatsByNumber = Object.fromEntries(seatData.map(seat => [Number(seat.seat_number), seat]));
    const rows = layout.reduce((groups, item) => {
        (groups[item.row_number] ||= []).push(item);
        return groups;
    }, {});

    Object.keys(rows).sort((a, b) => Number(a) - Number(b)).forEach(rowNumber => {
        const rowItems = rows[rowNumber]
            .sort((a, b) => Number(a.display_order) - Number(b.display_order))
            .filter(item => (item.position_type === 'seat' && seatsByNumber[Number(item.seat_number)]) || item.position_type === 'aisle');
        const firstAisleIndex = rowItems.findIndex(item => item.position_type === 'aisle');
        const hasSeatsBeforeAisle = rowItems.slice(0, firstAisleIndex).some(item => item.position_type === 'seat');
        const hasSeatsAfterAisle = rowItems.slice(firstAisleIndex + 1).some(item => item.position_type === 'seat');
        const visibleItems = rowItems.filter(item => item.position_type === 'seat' || (item.position_type === 'aisle' && hasSeatsBeforeAisle && hasSeatsAfterAisle));
        if (!visibleItems.length) return;

        const row = document.createElement('div');
        row.className = 'seat-row';
        row.style.gridTemplateColumns = `repeat(${visibleItems.length}, 1fr)`;

        visibleItems.forEach(item => {
            if (item.position_type === 'aisle') {
                const aisle = document.createElement('div');
                aisle.className = 'aisle-divider';
                row.appendChild(aisle);
                return;
            }

            const seatDataItem = seatsByNumber[Number(item.seat_number)];
            if (!seatDataItem) return;
            const seat = createSeat(seatDataItem);
            row.appendChild(seat.element);
            seats.push(seat.state);
        });
        seatsGrid.appendChild(row);
    });
}

function createSeat(seatData) {
    const seatNumber = Number(seatData.seat_number);
    const seat = document.createElement('div');
    const status = seatData.status === 'occupied' ? 'occupied' : 'available';
    seat.className = `seat ${status}`;
    seat.dataset.seatNumber = seatNumber;
    seat.dataset.seatId = seatData.id;
    seat.textContent = seatNumber;
    seat.addEventListener('click', () => toggleSeat(Number(seatData.id)));
    return {
        element: seat,
        state: { id: Number(seatData.id), number: seatNumber, status }
    };
}

function toggleSeat(seatId) {
    const seat = seats.find(item => item.id === seatId);
    if (!seat || seat.status === 'occupied') return;
    if (!selectedSeats.includes(seatId) && requiredPassengers > 0 && selectedSeats.length >= requiredPassengers) {
        alert(`Solo puedes seleccionar ${requiredPassengers} asientos.`);
        return;
    }

    const seatElement = document.querySelector(`[data-seat-id="${seatId}"]`);
    if (selectedSeats.includes(seatId)) {
        selectedSeats = selectedSeats.filter(id => id !== seatId);
        seatElement.classList.remove('selected');
        seatElement.classList.add('available');
    } else {
        selectedSeats.push(seatId);
        seatElement.classList.remove('available');
        seatElement.classList.add('selected');
    }
    updateSelectionInfo();
}

function updateSelectionInfo() {
    const selectedSeatsElement = document.getElementById('selectedSeats');
    const totalPriceElement = document.getElementById('totalPrice');
    const confirmBtn = document.getElementById('confirmBtn');

    if (selectedSeats.length === 0) {
        selectedSeatsElement.textContent = 'Ningún asiento seleccionado';
    } else {
        const seatText = selectedSeats.length === 1 ? 'asiento' : 'asientos';
        const selectedNumbers = selectedSeats
            .map(seatId => seats.find(seat => seat.id === seatId)?.number || seatId)
            .sort((a, b) => a - b);
        selectedSeatsElement.textContent = `${selectedSeats.length} ${seatText} seleccionados: ${selectedNumbers.join(', ')}`;
    }

    totalPriceElement.textContent = (selectedSeats.length * seatPrice).toLocaleString();
    confirmBtn.disabled = selectedSeats.length === 0
        || (requiredPassengers > 0 && selectedSeats.length !== requiredPassengers);
    confirmBtn.title = requiredPassengers > 0 && selectedSeats.length !== requiredPassengers
        ? `Selecciona exactamente ${requiredPassengers} asientos`
        : '';
}

async function confirmSelection() {
    const departureId = bookingParams.get('departure_id');
    if (!departureId) {
        alert('Falta la salida seleccionada. Regresa al detalle del tour.');
        return;
    }
    if (requiredPassengers > 0 && selectedSeats.length !== requiredPassengers) {
        alert(`Debes seleccionar exactamente ${requiredPassengers} asientos.`);
        return;
    }

    const passengers = [];
    Object.entries(passengerCounts).forEach(([type, count]) => {
        const apiType = type === 'seniors' ? 'senior' : type === 'children' ? 'child' : 'adult';
        for (let number = 1; number <= count; number++) {
            passengers.push({ type: apiType, number });
        }
    });

    try {
        await ToursApi.createBooking(departureId, selectedSeats, {
            agency_id: Number(bookingParams.get('agency_id') || 0) || null,
            adults: passengerCounts.adults,
            children: passengerCounts.children,
            seniors: passengerCounts.seniors,
            total_people: requiredPassengers,
            total: Number(bookingParams.get('total') || selectedSeats.length * seatPrice),
            passengers
        });
    } catch (error) {
        alert(error.message);
        return;
    }

    selectedSeats.forEach(seatId => {
        const seat = seats.find(item => item.id === seatId);
        const seatElement = document.querySelector(`[data-seat-id="${seatId}"]`);
        seat.status = 'occupied';
        seatElement.classList.remove('selected');
        seatElement.classList.add('occupied');
    });
    selectedSeats = [];
    updateSelectionInfo();
    document.getElementById('successModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('successModal').style.display = 'none';
}

function createRipple(event) {
    const button = event.currentTarget;
    const ripple = button.querySelector('.ripple');
    const diameter = Math.max(button.clientWidth, button.clientHeight);
    const radius = diameter / 2;
    ripple.style.width = `${diameter}px`;
    ripple.style.height = `${diameter}px`;
    ripple.style.left = `${event.clientX - button.offsetLeft - radius}px`;
    ripple.style.top = `${event.clientY - button.offsetTop - radius}px`;
    ripple.classList.add('ripple');
    setTimeout(() => ripple.classList.remove('ripple'), 600);
}

document.addEventListener('DOMContentLoaded', () => {
    const agencyName = document.getElementById('agencyName');
    const bookingRoute = document.getElementById('bookingRoute');
    const bookingSummary = document.getElementById('bookingSummary');
    if (agencyName && bookingParams.get('agency_name')) agencyName.textContent = bookingParams.get('agency_name');
    if (bookingRoute && bookingParams.get('tour_name')) bookingRoute.textContent = bookingParams.get('tour_name');
    if (bookingSummary && bookingParams.get('departure_date')) {
        bookingSummary.textContent = `Salida: ${bookingParams.get('departure_date')} · Pasajeros: ${requiredPassengers}`;
    }

    const departureId = bookingParams.get('departure_id');
    if (!departureId) {
        alert('Falta la salida seleccionada. Regresa al detalle del tour.');
    } else {
        ToursApi.getSeats(departureId).then(({ data, layout }) => {
            renderSeatsGrid(layout || [], data || []);
            updateSelectionInfo();
        }).catch(error => alert(error.message));
    }

    const confirmBtn = document.getElementById('confirmBtn');
    confirmBtn.addEventListener('click', confirmSelection);
    confirmBtn.addEventListener('click', createRipple);
    document.getElementById('closeModal').addEventListener('click', closeModal);
    window.addEventListener('click', event => {
        if (event.target === document.getElementById('successModal')) closeModal();
    });
});
