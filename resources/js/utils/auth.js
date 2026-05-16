/**
 * Safely parse the user object from localStorage.
 * Handles corrupted data (e.g., "[object Object]") gracefully.
 * Returns a plain object with at least { role: 'user' } if data is invalid.
 */
export function getStoredUser() {
    try {
        const raw = localStorage.getItem('user');
        if (!raw) return {};
        const parsed = JSON.parse(raw);
        if (typeof parsed === 'object' && parsed !== null) {
            return parsed;
        }
        return {};
    } catch (e) {
        // Corrupted localStorage data - clear it
        console.warn('Corrupted user data in localStorage, clearing...');
        localStorage.removeItem('user');
        localStorage.removeItem('token');
        return {};
    }
}

/**
 * Get the user's role from localStorage safely.
 * Defaults to 'user' if not found.
 */
export function getStoredUserRole() {
    const user = getStoredUser();
    return user.role || 'user';
}
