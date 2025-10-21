/**
 * HTTP Request Utility
 * 
 * @package FP\PerfSuite
 */

/**
 * Make an HTTP request with WordPress REST API nonce
 * 
 * @param {string} url - The URL to fetch
 * @param {string} method - HTTP method (GET, POST, etc.)
 * @param {object|null} body - Request body
 * @param {string} nonce - WordPress nonce for authentication
 * @returns {Promise} Fetch promise
 */
export function request(url, method = 'GET', body = null, nonce = '') {
    const headers = { 'X-WP-Nonce': nonce };
    
    if (body) {
        headers['Content-Type'] = 'application/json';
    }
    
    return fetch(url, {
        method,
        headers,
        body: body ? JSON.stringify(body) : null,
        credentials: 'same-origin'
    }).then(async (response) => {
        let data = null;
        
        try {
            data = await response.json();
        } catch (e) {
            data = null;
        }
        
        if (!response.ok) {
            const error = data && (data.message || data.error);
            const err = new Error(error || 'Request failed');
            err.data = data;
            throw err;
        }
        
        return data;
    });
}