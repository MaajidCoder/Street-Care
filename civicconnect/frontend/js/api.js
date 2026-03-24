// frontend/js/api.js
// Use the 'api' route which .htaccess handles
const API_BASE_URL = 'http://localhost/street%20care/civicconnect/api';

class CivicConnectAPI {
    constructor() {
        this.token = localStorage.getItem('auth_token');
        this.user = JSON.parse(localStorage.getItem('user_data') || '{}');
    }

    async request(endpoint, method = 'GET', data = null) {
        const url = `${API_BASE_URL}/${endpoint}`;
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
            }
        };

        if (this.token) {
             options.headers['Authorization'] = `Bearer ${this.token}`;
        }

        if (data && (method === 'POST' || method === 'PUT')) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, options);
            
            // Check for HTML response (error)
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.indexOf("application/json") === -1) {
                // Not JSON, probably 404 or 500 HTML page
                const text = await response.text();
                console.error('API Invalid Response:', text);
                throw new Error(`Server returned non-JSON response: ${response.status}`);
            }

            const result = await response.json();
            return result;
        } catch (error) {
            console.error('API Error:', error);
            // Re-throw to caller with better message
            throw error;
        }
    }

    async login(email, password) {
        const response = await this.request('auth/login.php', 'POST', { email, password });
        if (response.success) {
            this.token = response.token;
            this.user = response.user;
            localStorage.setItem('auth_token', response.token);
            localStorage.setItem('user_data', JSON.stringify(response.user));
        }
        return response;
    }

    async register(userData) {
        return await this.request('auth/register.php', 'POST', userData);
    }

    async createIssue(issueData) {
        if (this.user && this.user.id) {
            issueData.user_id = this.user.id;
        }
        return await this.request('issues.php?action=create', 'POST', issueData);
    }

    async getIssues(filterStr = '') {
        return await this.request(`issues.php?action=list${filterStr}`);
    }

    async updateIssueStatus(issueId, status) {
        return await this.request('issues.php?action=update', 'POST', { id: issueId, status: status });
    }

    async assignIssue(issueId, assigneeId) {
        return await this.request('issues.php?action=assign', 'POST', { issue_id: issueId, assignee_id: assigneeId });
    }

    logout() {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user_data');
        window.location.href = '../auth/login.html';
    }
}

window.CivicConnectAPI = new CivicConnectAPI();
