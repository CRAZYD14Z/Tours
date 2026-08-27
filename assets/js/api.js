const ToursApi = (() => {
    const baseUrl = `${window.location.origin}/tours/api/`;

    async function request(resource, query = '', options = {}) {
        const token = localStorage.getItem('tours_jwt');
        const headers = { 'Content-Type': 'application/json', ...(options.headers || {}) };
        if (token) headers.Authorization = `Bearer ${token}`;

        const response = await fetch(`${baseUrl}?resource=${encodeURIComponent(resource)}${query}`, {
            ...options,
            headers
        });
        const payload = await response.json();
        if (!response.ok) throw new Error(payload.error || 'No se pudo completar la solicitud');
        return payload;
    }

    return {
        login(email, password) {
            return request('auth', '', {
                method: 'POST',
                body: JSON.stringify({ email, password })
            }).then(response => {
                localStorage.setItem('tours_jwt', response.token);
                return response;
            });
        },
        getTours(destination = '') {
            return request('tours', `&destination=${encodeURIComponent(destination)}`);
        },
        getTour(tourId) {
            return request('tour', `&id=${encodeURIComponent(tourId)}`);
        },
        getDepartures(tourId) {
            return request('departures', `&tour_id=${encodeURIComponent(tourId)}`);
        },
        getVehicles(companyId = '') {
            const query = companyId ? `&company_id=${encodeURIComponent(companyId)}` : '';
            return request('vehicles', query);
        },
        getSeats(departureId) {
            return request('seats', `&departure_id=${encodeURIComponent(departureId)}`);
        },
        createBooking(departureId, seatIds) {
            return request('bookings', '', {
                method: 'POST',
                body: JSON.stringify({ departure_id: departureId, seat_ids: seatIds })
            });
        }
    };
})();
