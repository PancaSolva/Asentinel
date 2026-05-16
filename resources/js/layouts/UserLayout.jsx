import React, { useState } from 'react';
import { Outlet, Link, useLocation, useNavigate } from 'react-router-dom';
import { LayoutDashboard, AppWindow, FileText, Activity, Users, Bell, LogOut } from 'lucide-react';
import api from '../api';
import { getStoredUser } from '../utils/auth';

const UserLayout = () => {
    const location = useLocation();
    const navigate = useNavigate();
    const [showLogout, setShowLogout] = useState(false);

    const user = getStoredUser();

    const handleLogout = async () => {
        try { await api.post('/logout'); } catch (e) {}
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        navigate('/login');
    };

    const navItems = [
        { name: 'Dashboard',         path: '/user-dashboard', icon: LayoutDashboard },
        { name: 'Aplication',        path: '/user-aplikasi',  icon: AppWindow },
        { name: 'Monitoring Logs',   path: '/user-monitoring-logs', icon: FileText },
        { name: 'Anomaly Detection', path: '#',               icon: Activity },
        { name: 'User Management',   path: '#',               icon: Users },
    ];

    const getPageTitle = () => {
        if (location.pathname === '/user-dashboard') return 'Dashboard';
        if (location.pathname.startsWith('/user-aplikasi')) return 'Application';
        if (location.pathname.startsWith('/user-monitoring-logs')) return 'Monitoring Logs';
        return 'Dashboard';
    };

    return (
        <div style={{ display: 'flex', minHeight: '100vh', background: '#f1f5f9', fontFamily: "'Inter', sans-serif" }}>

            {/* ── SIDEBAR ── */}
            <aside style={{
                width: 140,
                minWidth: 140,
                background: '#fff',
                display: 'flex',
                flexDirection: 'column',
                position: 'relative',
                overflow: 'hidden',
                zIndex: 10,
            }}>
                {/* Logo */}
                <div style={{ padding: '20px 16px 12px', display: 'flex', flexDirection: 'column', gap: 2 }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                        <div style={{ width: 10, height: 10, background: '#1d4ed8', borderRadius: 2 }} />
                        <div style={{ width: 10, height: 10, background: '#60a5fa', borderRadius: 2 }} />
                    </div>
                    <div style={{ fontSize: 9, fontWeight: 700, color: '#1e293b', lineHeight: 1.3, marginTop: 4 }}>
                        System Dashboard API
                    </div>
                    <div style={{ fontSize: 8, color: '#94a3b8', lineHeight: 1.3 }}>
                        Dinas Kominfo Provinsi Jawa Timur
                    </div>
                </div>

                {/* Nav */}
                <nav style={{ flex: 1, paddingTop: 16 }}>
                    {navItems.map(({ name, path, icon: Icon }) => {
                        const active = location.pathname === path || (path !== '#' && location.pathname.startsWith(path));
                        return (
                            <Link key={name} to={path} style={{ textDecoration: 'none' }}>
                                <div style={{
                                    display: 'flex',
                                    alignItems: 'center',
                                    gap: 8,
                                    padding: '10px 16px',
                                    position: 'relative',
                                    color: active ? '#1d4ed8' : '#cbd5e1',
                                    fontWeight: active ? 700 : 500,
                                    fontSize: 11,
                                }}>
                                    {active && (
                                        <div style={{
                                            position: 'absolute', left: 0, top: '50%',
                                            transform: 'translateY(-50%)',
                                            width: 4, height: 28,
                                            background: '#1d4ed8',
                                            borderRadius: '0 4px 4px 0',
                                        }} />
                                    )}
                                    <Icon size={14} />
                                    <span>{name}</span>
                                </div>
                            </Link>
                        );
                    })}
                </nav>

                {/* Blue wave shapes at bottom */}
                <div style={{
                    position: 'absolute', bottom: 0, left: 0, right: 0,
                    height: 200,
                    background: 'linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #3b82f6 100%)',
                    borderTopRightRadius: 120,
                    zIndex: -1,
                }} />
                <div style={{
                    position: 'absolute', bottom: 0, left: 0, right: 0,
                    height: 150,
                    background: 'linear-gradient(135deg, #1e40af 0%, #1d4ed8 60%, #2563eb 100%)',
                    borderTopRightRadius: 90,
                    zIndex: -2,
                    opacity: 0.8,
                }} />
                <div style={{ height: 200 }} />
            </aside>

            {/* ── MAIN ── */}
            <main style={{ flex: 1, display: 'flex', flexDirection: 'column', minWidth: 0, position: 'relative' }}>

                {/* Blue curved header background */}
                <div style={{
                    position: 'absolute',
                    top: 0, left: 0, right: 0,
                    height: 110,
                    background: 'linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 50%, #3b82f6 100%)',
                    borderBottomRightRadius: 80,
                    zIndex: 0,
                }} />

                {/* Header */}
                <header style={{
                    height: 72,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between',
                    padding: '0 32px',
                    position: 'relative',
                    zIndex: 10,
                }}>
                    <h1 style={{
                        fontSize: 32,
                        fontWeight: 900,
                        color: '#fff',
                        margin: 0,
                        letterSpacing: -0.5,
                    }}>
                        {getPageTitle()}
                    </h1>

                    <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
                        {/* Bell */}
                        <div style={{
                            width: 36, height: 36,
                            background: '#fff',
                            borderRadius: '50%',
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                            cursor: 'pointer',
                            boxShadow: '0 2px 8px rgba(0,0,0,0.15)',
                            position: 'relative',
                        }}>
                            <Bell size={16} color="#1d4ed8" />
                        </div>
                    </div>
                </header>

                {/* Page Content */}
                <div style={{ flex: 1, padding: '64px 32px 32px', position: 'relative', zIndex: 5, overflowY: 'auto' }}>
                    <Outlet />
                </div>

                {/* Logout button fixed bottom */}
                <button
                    onClick={handleLogout}
                    style={{
                        position: 'fixed', bottom: 24, right: 24,
                        background: '#ef4444',
                        color: '#fff',
                        border: 'none',
                        borderRadius: 12,
                        padding: '8px 16px',
                        fontWeight: 700,
                        fontSize: 12,
                        cursor: 'pointer',
                        display: 'flex', alignItems: 'center', gap: 6,
                        zIndex: 100,
                        boxShadow: '0 4px 12px rgba(239,68,68,0.4)',
                    }}
                >
                    <LogOut size={14} />
                    Logout
                </button>
            </main>
        </div>
    );
};

export default UserLayout;
