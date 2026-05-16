import React, { useEffect, useMemo, useState } from 'react';
import api from '../../api';
import { Search, ChevronDown, Eye, TrendingUp, Activity, AlertTriangle, AppWindow, Server } from 'lucide-react';

const WARNING_THRESHOLD_MS = 1000;

const UserMonitoringLogs = () => {
    const [logs, setLogs] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    const [statusFilter, setStatusFilter] = useState('');
    const [categoryFilter, setCategoryFilter] = useState('');

    useEffect(() => {
        const fetchLogs = async () => {
            try {
                setLoading(true);
                const res = await api.get('/monitoring-logs');
                setLogs(res.data?.data || []);
            } catch (e) {}
            finally { setLoading(false); }
        };
        fetchLogs();
    }, []);

    const toStatusLevel = (row) => {
        const httpCode = Number(row.http_status_code || 0);
        const httpOk = httpCode >= 200 && httpCode < 300;
        const base = (row.status || '').toUpperCase();
        if (!httpOk || base !== 'UP') return 'DOWN';
        const rt = Number(row.response_time_ms || 0);
        if (rt >= WARNING_THRESHOLD_MS) return 'MEDIUM';
        return 'UP';
    };

    const statusBadge = (level) => {
        if (level === 'UP') return { bg: '#dcfce7', color: '#16a34a', label: 'Up', Icon: TrendingUp };
        if (level === 'MEDIUM') return { bg: '#fef3c7', color: '#d97706', label: 'Medium', Icon: AlertTriangle };
        return { bg: '#fee2e2', color: '#dc2626', label: 'Down', Icon: Activity };
    };

    const formatTimestamp = (d) => {
        const date = new Date(d);
        if (Number.isNaN(date.getTime())) return '-';
        return date.toLocaleString('sv-SE');
    };

    const filtered = useMemo(() => {
        const q = searchTerm.trim().toLowerCase();
        return logs.filter((row) => {
            const targetName = (row.service?.nama || row.aplikasi?.nama || '').toLowerCase();
            const url = (row.url || '').toLowerCase();
            const category = (row.aplikasi?.tipe || '').toLowerCase();
            const level = toStatusLevel(row);
            const matchSearch = q ? (targetName.includes(q) || url.includes(q)) : true;
            const matchCat = categoryFilter ? category === categoryFilter : true;
            const matchStatus = statusFilter ? level === statusFilter : true;
            return matchSearch && matchCat && matchStatus;
        });
    }, [logs, searchTerm, categoryFilter, statusFilter]);

    return (
        <div style={{ fontFamily: "'Inter', sans-serif" }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 12, marginBottom: 20 }}>
                <div style={{ position: 'relative' }}>
                    <select
                        value={statusFilter}
                        onChange={(e) => setStatusFilter(e.target.value)}
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

                <div style={{ position: 'relative' }}>
                    <select
                        value={categoryFilter}
                        onChange={(e) => setCategoryFilter(e.target.value)}
                        style={{
                            appearance: 'none', background: '#2563eb', color: '#fff',
                            border: 'none', borderRadius: 999, padding: '9px 36px 9px 18px',
                            fontSize: 13, fontWeight: 600, cursor: 'pointer', outline: 'none',
                        }}
                    >
                        <option value="">Category</option>
                        <option value="microservice">Microservice</option>
                        <option value="monolith">Monolith</option>
                    </select>
                    <ChevronDown size={14} color="#fff" style={{ position: 'absolute', right: 12, top: '50%', transform: 'translateY(-50%)', pointerEvents: 'none' }} />
                </div>

                <div style={{ flex: 1, position: 'relative', maxWidth: 420 }}>
                    <input
                        type="text"
                        placeholder="Search..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        style={{
                            width: '100%', border: 'none', borderRadius: 999,
                            padding: '9px 40px 9px 18px', fontSize: 13, outline: 'none',
                            background: '#fff', boxShadow: '0 1px 4px rgba(0,0,0,0.06)', boxSizing: 'border-box',
                        }}
                    />
                    <Search size={15} color="#2563eb" strokeWidth={3} style={{ position: 'absolute', right: 14, top: '50%', transform: 'translateY(-50%)', pointerEvents: 'none' }} />
                </div>
            </div>

            <div style={{ background: '#f1f5f9', borderRadius: 24, padding: 18, boxShadow: '0 1px 4px rgba(0,0,0,0.04)' }}>
                <div style={{ background: '#fff', borderRadius: 18, overflow: 'hidden', border: '2px solid #2563eb' }}>
                    <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 13 }}>
                        <thead>
                            <tr style={{ background: '#f1f5f9', borderBottom: '1px solid #e2e8f0' }}>
                                {['Name App/Service', 'Status', 'Response Time', 'HTTP', 'Timestamp', 'View'].map((h, idx) => (
                                    <th
                                        key={h}
                                        style={{
                                            padding: '14px 18px',
                                            textAlign: idx === 0 ? 'left' : 'center',
                                            fontWeight: 600,
                                            color: '#94a3b8',
                                            fontSize: 12,
                                        }}
                                    >
                                        {h}
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {loading ? (
                                <tr><td colSpan={6} style={{ padding: 40, textAlign: 'center', color: '#94a3b8' }}>Loading...</td></tr>
                            ) : filtered.length === 0 ? (
                                <tr><td colSpan={6} style={{ padding: 40, textAlign: 'center', color: '#94a3b8' }}>No data</td></tr>
                            ) : (
                                filtered.map((row) => {
                                    const level = toStatusLevel(row);
                                    const b = statusBadge(level);
                                    const rt = Number(row.response_time_ms || 0);
                                    const rtColor = level === 'UP' ? '#16a34a' : (level === 'MEDIUM' ? '#d97706' : '#dc2626');
                                    const httpCode = Number(row.http_status_code || 0);
                                    const httpColor = httpCode >= 200 && httpCode < 300 ? '#16a34a' : '#dc2626';
                                    const targetName = row.service?.nama || row.aplikasi?.nama || '-';
                                    const isService = !!row.service;

                                    return (
                                        <tr key={row.id_log_monitor} style={{ borderBottom: '1px solid #f1f5f9' }}>
                                            <td style={{ padding: '14px 18px' }}>
                                                <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                                                    <div style={{
                                                        width: 26, height: 26, borderRadius: 8,
                                                        background: isService ? '#ede9fe' : '#dbeafe',
                                                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                                                    }}>
                                                        {isService ? <Server size={14} color="#7c3aed" /> : <AppWindow size={14} color="#2563eb" />}
                                                    </div>
                                                    <div style={{ fontWeight: 700, color: '#374151' }}>{targetName}</div>
                                                </div>
                                            </td>
                                            <td style={{ padding: '14px 18px', textAlign: 'center' }}>
                                                <span style={{
                                                    background: b.bg,
                                                    color: b.color,
                                                    padding: '3px 12px',
                                                    borderRadius: 8,
                                                    fontSize: 11,
                                                    fontWeight: 700,
                                                    display: 'inline-flex',
                                                    alignItems: 'center',
                                                    gap: 6,
                                                }}>
                                                    <b.Icon size={12} /> {b.label}
                                                </span>
                                            </td>
                                            <td style={{ padding: '14px 18px', textAlign: 'center', fontWeight: 800, color: rtColor }}>
                                                {rt ? `${rt}ms` : '-'}
                                            </td>
                                            <td style={{ padding: '14px 18px', textAlign: 'center', fontWeight: 800, color: httpColor }}>
                                                {row.http_status_code ?? '-'}
                                            </td>
                                            <td style={{ padding: '14px 18px', textAlign: 'center', color: '#94a3b8', fontSize: 12 }}>
                                                {row.checked_at ? formatTimestamp(row.checked_at) : '-'}
                                            </td>
                                            <td style={{ padding: '14px 18px', textAlign: 'center' }}>
                                                <button
                                                    type="button"
                                                    onClick={() => row.url && window.open(row.url, '_blank', 'noreferrer')}
                                                    style={{
                                                        background: 'transparent',
                                                        border: 'none',
                                                        cursor: row.url ? 'pointer' : 'default',
                                                        color: '#cbd5e1',
                                                        padding: 6,
                                                    }}
                                                >
                                                    <Eye size={18} />
                                                </button>
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

export default UserMonitoringLogs;

