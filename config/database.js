// Configuración de la base de datos y API
const API_CONFIG = {
    // URL base de la API
    BASE_URL: 'http://localhost/api',
    
    // Endpoints de la API
    ENDPOINTS: {
        // Autenticación
        LOGIN: '/auth/login',
        REGISTER: '/auth/register',
        
        // Usuario
        USER_UPDATE: '/user',
        USER_PROFILE: '/user/profile',
        
        // Pagos
        DEPOSIT: '/payments/deposit',
        WITHDRAW: '/payments/withdraw',
        
        // Transacciones
        TRANSACTIONS: '/transactions',
        
        // Admin
        ADMIN_DASHBOARD: '/admin/dashboard',
        ADMIN_USERS: '/admin/users',
        ADMIN_TRANSACTIONS: '/admin/transactions',
        
        // Juegos
        GAMES: '/games',
        GAME_PLAY: '/games/play',
        GAME_RESULT: '/games/result',
        
        // Entregas
        DELIVERIES: '/deliveries'
    },
    
    // Headers por defecto
    DEFAULT_HEADERS: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    
    // Configuración de Nitro Pagamentos
    NITRO: {
        API_TOKEN: 'AJTQzn8xWuYXrjNu5XWajspWi8i6sd9XzkgEViaDpkIrwyKRKCkC1fHCFY1P',
        OFFER_HASH: 'ydpamubeay',
        PRODUCT_HASH: '8cru5klgqv'
    }
};

// Configuración de la base de datos MySQL
const DB_CONFIG = {
    host: 'localhost',
    database: 'raspadinha_db',
    username: 'root',
    password: '',
    charset: 'utf8mb4',
    port: 3306
};

// Clase para manejar las llamadas a la API
class ApiClient {
    constructor() {
        this.baseUrl = API_CONFIG.BASE_URL;
        this.token = localStorage.getItem('auth_token');
    }
    
    // Configurar token de autenticación
    setToken(token) {
        this.token = token;
        if (token) {
            localStorage.setItem('auth_token', token);
        } else {
            localStorage.removeItem('auth_token');
        }
    }
    
    // Obtener headers con autenticación
    getHeaders() {
        const headers = { ...API_CONFIG.DEFAULT_HEADERS };
        if (this.token) {
            headers['Authorization'] = `Bearer ${this.token}`;
        }
        return headers;
    }
    
    // Método genérico para hacer peticiones
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const config = {
            headers: this.getHeaders(),
            ...options
        };
        
        try {
            const response = await fetch(url, config);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Error en la petición');
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
    
    // Métodos de autenticación
    async login(email, password) {
        const data = await this.request(API_CONFIG.ENDPOINTS.LOGIN, {
            method: 'POST',
            body: JSON.stringify({ email, password })
        });
        
        if (data.token) {
            this.setToken(data.token);
        }
        
        return data;
    }
    
    async register(userData) {
        return await this.request(API_CONFIG.ENDPOINTS.REGISTER, {
            method: 'POST',
            body: JSON.stringify(userData)
        });
    }
    
    // Métodos de usuario
    async updateUser(userData) {
        return await this.request(API_CONFIG.ENDPOINTS.USER_UPDATE, {
            method: 'PATCH',
            body: JSON.stringify(userData)
        });
    }
    
    // Métodos de pagos
    async createDeposit(amount) {
        return await this.request(API_CONFIG.ENDPOINTS.DEPOSIT, {
            method: 'POST',
            body: JSON.stringify({ amount })
        });
    }
    
    async createWithdraw(amount, pixKey) {
        return await this.request(API_CONFIG.ENDPOINTS.WITHDRAW, {
            method: 'POST',
            body: JSON.stringify({ amount, pix_key: pixKey })
        });
    }
    
    // Métodos de transacciones
    async getTransactions() {
        return await this.request(API_CONFIG.ENDPOINTS.TRANSACTIONS);
    }
    
    // Métodos de juegos
    async playGame(gameType, betAmount) {
        return await this.request(API_CONFIG.ENDPOINTS.GAME_PLAY, {
            method: 'POST',
            body: JSON.stringify({ game_type: gameType, bet_amount: betAmount })
        });
    }
    
    async getGameResult(gameId) {
        return await this.request(`${API_CONFIG.ENDPOINTS.GAME_RESULT}/${gameId}`);
    }
    
    // Métodos de admin
    async getAdminDashboard() {
        return await this.request(API_CONFIG.ENDPOINTS.ADMIN_DASHBOARD);
    }
    
    async getAdminUsers() {
        return await this.request(API_CONFIG.ENDPOINTS.ADMIN_USERS);
    }
    
    async getAdminTransactions() {
        return await this.request(API_CONFIG.ENDPOINTS.ADMIN_TRANSACTIONS);
    }
    
    // Método para logout
    logout() {
        this.setToken(null);
        window.location.href = '/';
    }
}

// Instancia global del cliente API
window.apiClient = new ApiClient();

// Exportar configuraciones
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { API_CONFIG, DB_CONFIG, ApiClient };
}