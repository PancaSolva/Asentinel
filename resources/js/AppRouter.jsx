import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import Layout from './layouts/Layout';
import Dashboard from './pages/Dashboard';
import AplikasiIndex from './pages/Aplikasi/Index';
import AplikasiDetail from './pages/Aplikasi/Detail';
import ServiceIndex from './pages/Services/Index';
import Login from './pages/Login';
import ProtectedRoute from './components/ProtectedRoute';

const AppRouter = () => {
    return (
        <Router>
            <Routes>
                {/* Public Route */}
                <Route path="/login" element={<Login />} />
                
                {/* Protected Routes */}
                <Route element={<ProtectedRoute />}>
                    <Route path="/" element={<Layout />}>
                        <Route index element={<Dashboard />} />
                        <Route path="aplikasi" element={<AplikasiIndex />} />
                        <Route path="aplikasi/:id" element={<AplikasiDetail />} />
                        <Route path="services" element={<ServiceIndex />} />
                    </Route>
                </Route>
                
                {/* Final Fallback */}
                <Route path="*" element={<Navigate to="/login" replace />} />
            </Routes>
        </Router>
    );
};

export default AppRouter;
