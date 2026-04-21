import React from 'react';
import { Navigate, Outlet, useLocation } from 'react-router-dom';
import axios from 'axios';


const ProtectedRoute = () => {
    const token = localStorage.getItem('token');
    const location = useLocation();

    if (!token) {
        return <Navigate to="/login" state={{ from: location }} replace />;
    }

    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

    return <Outlet />;
};

export default ProtectedRoute;
