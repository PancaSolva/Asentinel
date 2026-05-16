import React from 'react';
import { Navigate, Outlet, useLocation } from 'react-router-dom';
import { getStoredUser } from '../utils/auth';


const ProtectedRoute = () => {
    const token = localStorage.getItem('token');
    const location = useLocation();

    if (!token) {
        // Redirect to login, preserving the intended destination
        return <Navigate to="/login" state={{ from: location }} replace />;
    }

    // If user is at root path, redirect based on role
    const user = getStoredUser();
    const userRole = user.role || 'user';

    // Prevent non-admin users from accessing admin paths (root level routes)
    if (userRole !== 'admin' && location.pathname === '/') {
        return <Navigate to="/user-dashboard" replace />;
    }

    // Prevent admin users from accessing user paths
    if (userRole === 'admin' && location.pathname.startsWith('/user-')) {
        return <Navigate to="/" replace />;
    }

    return <Outlet />;
};

export default ProtectedRoute;
