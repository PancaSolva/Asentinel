import React, { useState, useEffect, useMemo } from 'react';
import { Link } from 'react-router-dom';
import api from '../../api';
import { Search, ChevronDown, Eye, TrendingUp, Activity, AlertTriangle } from 'lucide-react';

const WARNING_THRESHOLD_MS = 1000;

const UserAplikasiIndex = () => {
    const [aplikasi, setAplikasi] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState('');
    const [categoryFilter, setCategoryFilter] = useState('');
    const [latestStatus, setLatestStatus] = useState({});

    useEffect(() => { fetchAplikasi(); fetchStatus(); }, []);

    const fetchAplikasi = async () => {
        try {
            setLoading(true);
            const res = await api.get('/aplikasi');
            setAplikasi(res.data.data || []);
        } catch (e) {}
        finally { setLoading(false); }
    };

    const fetchStatus = async () => {
        try {
            const res = await api.get('/monitoring-logs');
            const logs = Array.isArray(res.data?.data) ? res.data.data : (res.data?.data?.data || []);
            // Build a map of latest status per aplikasi
            const statusMap = {};
            logs.forEach(log => {
                const key = log.id_aplikasi;
                if (key && !statusMap[key]) {
                    const httpCode = Number(log.http_status_code || 0);
                    const httpOk = httpCode >= 200 && httpCode < 300;
                    const base = (log.status || '').toUpperCase();
                    if (!httpOk || base !== 'UP') {
                        statusMap[key] = 'DOWN';
                        return;
                    }
                    const rt = Number(log.response_time_ms || 0);
                    statusMap[key] = rt >= WARNING_THRESHOLD_MS ? 'MEDIUM' : 'UP';
                }
            });
            setLatestStatus(statusMap);
        } catch (e) {}
    };

    const getAppStatus = (app, idx) => {
        if (latestStatus[app.id_aplikasi]) return latestStatus[app.id_aplikasi];
        // Fallback: alternate for demo
        return idx % 3 === 0 ? 'DOWN' : 'UP';
    };

    const filtered = useMemo(() => aplikasi.filter(app => {
        const matchSearch = (app.nama || '').toLowerCase().includes(searchTerm.toLowerCase());
        const matchCat = categoryFilter ? app.tipe === categoryFilter : true;
        return matchSearch && matchCat;
    }), [aplikasi, searchTerm, categoryFilter]);

    const GridIcon = () => (
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <rect x="3" y="3" width="7" height="7" rx="1.5" fill="#2563eb"/>
            <rect x="14" y="3" width="7" height="7" rx="1.5" fill="#2563eb"/>
            <rect x="3" y="14" width="7" height="7" rx="1.5" fill="#2563eb"/>
            <rect x="14" y="14" width="7" height="7" rx="1.5" fill="#2563eb"/>
        </svg>
    );

    return (
        <div style={{ fontFamily: "'Inter', sans-serif" }}>

            {/* ── Filter row ── */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 20 }}>
                {/* Status dropdown */}
                <div style={{ position: 'relative' }}>
                    <select
                        value={statusFilter}
                        onChange={e => setStatusFilter(e.target.value)}
                        style={{
                            appearance: 'none', background: '#2563eb', color: '#fff',
                            border: 'none', borderRadius: 999, padding: '9px 36px 9px 18px',
                            fontSize: 13, fontWeight: 600, cursor: 'pointer', outline: 'none',
                        }}
                    >
                        <option value="">Status</option>
                        <option value="UP">Up</option>
                        <option value="DOWN">Down</option>
                        <option value="MEDIUM">Medium</option>
                    </select>
                    <ChevronDown size={14} color="#fff" style={{ position: 'absolute', right: 12, top: '50%', transform: 'translateY(-50%)', pointerEvents: 'none' }} />
                </div>

                {/* Category dropdown */}
                <div style={{ position: 'relative' }}>
                    <select
                        value={categoryFilter}
                        onChange={e => setCategoryFilter(e.target.value)}
                        style={{
                            appearance: 'none', background: '#2563eb', color: '#fff',
                            border: 'none', borderRadius: 999, padding: '9px 36px 9px 18px',
                            fontSize: 13, fontWeight: 600, cursor: 'pointer', outline: 'none',
                        }}
                    >
                        <option value="">Category</option>
                        <option value="monolith">Monolith</option>
                        <option value="microservice">Microservice</option>
                    </select>
                    <ChevronDown size={14} color="#fff" style={{ position: 'absolute', right: 12, top: '50%', transform: 'translateY(-50%)', pointerEvents: 'none' }} />
                </div>

                {/* Search input */}
                <div style={{ flex: 1, position: 'relative', maxWidth: 400 }}>
                    <input
                        type="text"
                        placeholder="Search..."
                        value={searchTerm}
                        onChange={e => setSearchTerm(e.target.value)}
                        style={{
                            width: '100%', border: 'none', borderRadius: 999,
                            padding: '9px 40px 9px 18px', fontSize: 13, outline: 'none',
                            background: '#fff', boxShadow: '0 1px 4px rgba(0,0,0,0.06)', boxSizing: 'border-box',
                        }}
                    />
                    <Search size={15} color="#2563eb" strokeWidth={3} style={{ position: 'absolute', right: 14, top: '50%', transform: 'translateY(-50%)', pointerEvents: 'none' }} />
                </div>
            </div>

            {/* ── Table Card ── */}
            <div style={{ background: '#f1f5f9', borderRadius: 24, padding: 18, boxShadow: '0 1px 4px rgba(0,0,0,0.04)' }}>
                <div style={{ background: '#fff', borderRadius: 18, overflow: 'hidden', border: '2px solid #2563eb' }}>
                    <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 13 }}>
                        <thead>
                            <tr style={{ background: '#f1f5f9', borderBottom: '1px solid #e2e8f0' }}>
                                {['Name App', 'URL', 'Category', 'Status', 'View'].map(h => (
                                    <th key={h} style={{
                                        padding: '14px 20px',
                                        textAlign: h === 'Name App' ? 'left' : 'center',
                                        fontWeight: 600, color: '#94a3b8', fontSize: 12,
                                    }}>{h}</th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {loading ? (
                                <tr><td colSpan={5} style={{ padding: 40, textAlign: 'center', color: '#94a3b8' }}>Loading...</td></tr>
                            ) : filtered.length === 0 ? (
                                <tr><td colSpan={5} style={{ padding: 40, textAlign: 'center', color: '#94a3b8' }}>No data</td></tr>
                            ) : (
                                filtered.map((app, i) => {
                                    const status = getAppStatus(app, i);
                                    const isUp = status === 'UP';
                                    const isMedium = status === 'MEDIUM';
                                    const isMonolith = app.tipe === 'monolith';
                                    if (statusFilter && statusFilter !== status) return null;
                                    return (
                                        <tr key={app.id_aplikasi} style={{ borderBottom: '1px solid #f8fafc' }}>
                                            {/* Name */}
                                            <td style={{ padding: '14px 20px' }}>
                                                <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                                                    <GridIcon />
                                                    <span style={{ fontWeight: 700, color: '#374151' }}>{app.nama || 'Kominfo.web'}</span>
                                                </div>
                                            </td>
                                            {/* URL */}
                                            <td style={{ padding: '14px 20px', textAlign: 'center', color: '#64748b' }}>
                                                {app.url_service || 'https://KominfoJatim.com'}
                                            </td>
                                            {/* Category */}
                                            <td style={{ padding: '14px 20px', textAlign: 'center' }}>
                                                <span style={{
                                                    background: isMonolith ? '#ede9fe' : '#cffafe',
                                                    color: isMonolith ? '#7c3aed' : '#0891b2',
                                                    padding: '3px 12px', borderRadius: 4, fontSize: 11, fontWeight: 600,
                                                }}>
                                                    {app.tipe ? app.tipe.charAt(0).toUpperCase() + app.tipe.slice(1) : 'Monolith'}
                                                </span>
                                            </td>
                                            {/* Status */}
                                            <td style={{ padding: '14px 20px', textAlign: 'center' }}>
                                                <span style={{
                                                    background: isUp ? '#dcfce7' : (isMedium ? '#fef3c7' : '#fee2e2'),
                                                    color: isUp ? '#16a34a' : (isMedium ? '#d97706' : '#dc2626'),
                                                    padding: '3px 12px', borderRadius: 6, fontSize: 11, fontWeight: 700,
                                                    display: 'inline-flex', alignItems: 'center', gap: 4,
                                                }}>
                                                    {isUp ? <TrendingUp size={10} /> : (isMedium ? <AlertTriangle size={10} /> : <Activity size={10} />)}
                                                    {isUp ? 'Up' : (isMedium ? 'Medium' : 'Down')}
                                                </span>
                                            </td>
                                            {/* View */}
                                            <td style={{ padding: '14px 20px', textAlign: 'center' }}>
                                                <Link to={`/user-aplikasi/${app.id_aplikasi}`} style={{ color: '#cbd5e1', textDecoration: 'none' }}>
                                                    <Eye size={17} />
                                                </Link>
                                            </td>
                                        </tr>
                                    );
                                })
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
};

export default UserAplikasiIndex;
