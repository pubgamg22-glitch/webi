class ApiService {
    constructor() {
        this.baseUrl = '/api';
        this.authToken = 'your_secure_token'; // In production, get from login
    }

    async request(endpoint, method = 'GET', data = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${this.authToken}`
            }
        };

        if (data) {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(`${this.baseUrl}/${endpoint}`, options);
        
        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Request failed');
        }

        return response.json();
    }

    // Places
    getPlaces() {
        return this.request('places');
    }

    createPlace(data) {
        return this.request('places', 'POST', data);
    }

    // Picnics
    getPicnics() {
        return this.request('picnics');
    }

    createPicnic(data) {
        return this.request('picnics', 'POST', data);
    }

    // Stats
    getStats() {
        return this.request('stats');
    }
}
