class AdminApp {
    constructor() {
        this.apiService = new ApiService();
        this.currentEditId = null;
        this.init();
    }

    async init() {
        this.setupEventListeners();
        await this.loadData();
        this.renderUI();
    }

    setupEventListeners() {
        // Tab switching
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.addEventListener('click', () => this.showTab(btn.dataset.tab));
        });

        // Form submissions
        document.getElementById('picnicForm').addEventListener('submit', (e) => this.handlePicnicForm(e));
        document.getElementById('placeForm').addEventListener('submit', (e) => this.handlePlaceForm(e));
    }

    async loadData() {
        try {
            this.showLoading();
            const [places, picnics, stats] = await Promise.all([
                this.apiService.getPlaces(),
                this.apiService.getPicnics(),
                this.apiService.getStats()
            ]);
            
            this.places = places;
            this.picnics = picnics;
            this.stats = stats;
        } catch (error) {
            this.showError('Failed to load data: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }

    async handlePicnicForm(e) {
        e.preventDefault();
        const formData = this.getFormData('picnicForm');
        
        try {
            let result;
            if (this.currentEditId) {
                result = await this.apiService.updatePicnic(this.currentEditId, formData);
                this.showSuccess('Picnic updated successfully!');
            } else {
                result = await this.apiService.createPicnic(formData);
                this.showSuccess('Picnic created successfully!');
            }
            
            await this.loadData();
            this.clearForm('picnicForm');
            this.currentEditId = null;
        } catch (error) {
            if (error.errors) {
                this.showFormErrors('picnicForm', error.errors);
            } else {
                this.showError('Failed to save picnic: ' + error.message);
            }
        }
    }

    // Similar methods for place handling...
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => new AdminApp());
