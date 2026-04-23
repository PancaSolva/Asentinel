import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import Layout from './layouts/Layout';
import Dashboard from './pages/Dashboard';
import AplikasiIndex from './pages/Aplikasi/Index';
import AplikasiDetail from './pages/Aplikasi/Detail';
import ServiceIndex from './pages/Services/Index';
import UserIndex from './pages/Users/Index';
import Login from './pages/Login2';
import ProtectedRoute from './components/ProtectedRoute';

const AppRouter = () => {
    return (
        <Router>
            <Routes>

                <Route path="/login" element={<Login />} />
                

                <Route element={<ProtectedRoute />}>
                    <Route path="/" element={<Layout />}>
                        <Route index element={<Dashboard />} />
                        <Route path="aplikasi" element={<AplikasiIndex />} />
                        <Route path="aplikasi/:id" element={<AplikasiDetail />} />
                        <Route path="services" element={<ServiceIndex />} />
                        <Route path="users" element={<UserIndex />} />
                    </Route>
                </Route>
                

                <Route path="*" element={<Navigate to="/login" replace />} />
            </Routes>
        </Router>
    );
};

export default AppRouter;
