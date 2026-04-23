import React, { Suspense, lazy } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import Layout from './layouts/Layout';
import ProtectedRoute from './components/ProtectedRoute';

// Lazy-load pages for code splitting
const Dashboard = lazy(() => import('./pages/Dashboard'));
const AplikasiIndex = lazy(() => import('./pages/Aplikasi/Index'));
const AplikasiDetail = lazy(() => import('./pages/Aplikasi/Detail'));
const ServiceIndex = lazy(() => import('./pages/Services/Index'));
const UserIndex = lazy(() => import('./pages/Users/Index'));
const Login = lazy(() => import('./pages/Login2'));

const PageLoader = () => (
    <div className="flex items-center justify-center min-h-[400px]">
        <div className="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
    </div>
);

const AppRouter = () => {
    return (
        <Router>
            <Suspense fallback={<PageLoader />}>
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
                            <Route path="users" element={<UserIndex />} />
                        </Route>
                    </Route>
                    
                    {/* Final Fallback */}
                    <Route path="*" element={<Navigate to="/login" replace />} />
                </Routes>
            </Suspense>
        </Router>
    );
};

export default AppRouter;
