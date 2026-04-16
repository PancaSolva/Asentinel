import React from 'react';
import { Navigate, Outlet, useLocation } from 'react-router-dom';
import axios from 'axios';

const ProtectedRoute = () => {
    const token = localStorage.getItem('token');
    const location = useLocation();

    console.log("ProtectedRoute check. Token exists:", !!token);

    if (!token) {
        // Redirect them to the /login page, but save the current location they were
        // trying to go to when they were redirected. This allows us to send them
        // along to that page after they login, which is a nicer user experience
        // than dropping them off on the home page.
        return <Navigate to="/login" state={{ from: location }} replace />;
    }

    // Set axios default header for all requests if token exists
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

    return <Outlet />;
};

export default ProtectedRoute;
