import React, { useEffect, useMemo, useState } from 'react';
import { useParams } from 'react-router-dom';
import api from '../../api';
import { Eye, AlertTriangle } from 'lucide-react';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Filler,
    Tooltip,
    Legend,
} from 'chart.js';
import { Line } from 'react-chartjs-2';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Filler, Tooltip, Legend);

const WARNING_THRESHOLD_MS = 1000;

const box = (extra = {}) => ({
    background: '#fff',
    border: '1px solid #e2e8f0',
    borderRadius: 20,
    boxShadow: '0 2px 10px rgba(0,0,0,0.05)',
    ...extra,
});

const UserAplikasiDetail = () => {
    const { id } = useParams();
    const [app, setApp] = useState(null);
    const [loading, setLoading] = useState(true);
    const [anomali, setAnomali] = useState([]);

    useEffect(() => {
        let mounted = true;
        const fetchAll = async () => {
            try {
                setLoading(true);
                const [appRes, anomRes] = await Promise.all([
                    api.get(`/aplikasi/${id}`),
                    api.get('/anomali-logs').catch(() => ({ data: { data: [] } })),
                ]);
                if (!mounted) return;
                setApp(appRes.data?.data || null);
                const payload = anomRes?.data?.data?.data || anomRes?.data?.data || [];
                setAnomali(Array.isArray(payload) ? payload : []);
            } catch (e) {}
            finally { if (mounted) setLoading(false); }
        };
        fetchAll();
        return () => { mounted = false; };
    }, [id]);

    if (loading) return (
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', minHeight: 300 }}>
            <div style={{ width: 40, height: 40, border: '4px solid #2563eb', borderTopColor: 'transparent', borderRadius: '50%', animation: 'spin 0.7s linear infinite' }} />
        </div>
    );

    if (!app) return <div style={{ textAlign: 'center', padding: 60, color: '#94a3b8' }}>Application not found.</div>;

    const services = Array.isArray(app.services) ? app.services : [];
    const tipe = (app.tipe || '').toLowerCase();
    const isMicroservice = tipe === 'microservice';
    const isMonolith = tipe === 'monolith';

    const servicesUpStable = services.filter(s => (s.status || '').toUpperCase() === 'UP' && Number(s.last_response_time || 0) < WARNING_THRESHOLD_MS).length;
    const servicesWarning = services.filter(s => (s.status || '').toUpperCase() === 'UP' && Number(s.last_response_time || 0) >= WARNING_THRESHOLD_MS).length;
    const servicesDown = services.filter(s => (s.status || '').toUpperCase() !== 'UP').length;

    const anomaliForApp = useMemo(() => {
        const appId = String(app.id_aplikasi);
        return anomali.filter(a => String(a.id_aplikasi) === appId).slice(0, 4);
    }, [anomali, app.id_aplikasi]);

    const logMonitors = app.log_monitors || app.logMonitors || [];
    const monolithLogs = Array.isArray(logMonitors)
        ? logMonitors
            .filter(l => !l.id_service)
            .filter(l => l.response_time_ms !== null && l.response_time_ms !== undefined)
            .slice()
            .reverse()
            .slice(0, 7)
            .reverse()
        : [];
    const latestRt = Number(app.last_response_time || (monolithLogs[monolithLogs.length - 1]?.response_time_ms || 0));

    const typePill = () => {
        if (isMicroservice) return { bg: '#cffafe', color: '#0891b2', label: 'Microservice' };
        return { bg: '#ede9fe', color: '#7c3aed', label: 'Monolith' };
    };

    const serviceTypeBadge = (t) => {
        const v = String(t || '').toLowerCase();
        if (v.includes('front')) return { bg: '#dbeafe', color: '#2563eb', label: 'Frontend' };
        if (v.includes('back')) return { bg: '#e0e7ff', color: '#4f46e5', label: 'Backend' };
        return { bg: '#f1f5f9', color: '#64748b', label: t || '-' };
    };

    const chartData = {
        labels: monolithLogs.map(l => new Date(l.checked_at).toLocaleTimeString()),
        datasets: [{
            data: monolithLogs.map(l => Number(l.response_time_ms || 0)),
            borderColor: '#2563eb',
            borderWidth: 2.5,
            pointRadius: 0,
            tension: 0.4,
            fill: true,
            backgroundColor: (ctx) => {
                const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 160);
                g.addColorStop(0, 'rgba(37,99,235,0.25)');
                g.addColorStop(1, 'rgba(37,99,235,0)');
                return g;
            },
        }],
    };
    const chartOpts = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10 } } },
            y: { grid: { display: false }, ticks: { display: false }, beginAtZero: true },
        },
    };

    return (
        <div style={{ fontFamily: "'Inter', sans-serif", maxWidth: 980 }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 14, marginBottom: 8 }}>
                <div style={{ fontSize: 44, fontWeight: 900, color: '#0f172a', letterSpacing: -0.8 }}>
                    {app.nama || 'Name Application'}
                </div>
                <div style={{ background: typePill().bg, color: typePill().color, padding: '4px 12px', borderRadius: 8, fontSize: 12, fontWeight: 700, height: 26, display: 'flex', alignItems: 'center' }}>
                    {typePill().label}
                </div>
            </div>

            <div style={{ fontSize: 18, fontWeight: 800, color: '#0f172a', marginBottom: 14 }}>General Information</div>

            <div style={{ display: 'flex', gap: 18, marginBottom: 28 }}>
                <div style={{ ...box(), flex: 2, padding: '26px 30px', display: 'grid', gridTemplateColumns: '1fr 1fr', rowGap: 22, columnGap: 22 }}>
                    <div>
                        <div style={{ fontSize: 11, color: '#94a3b8', fontWeight: 700, marginBottom: 6 }}>Application URL</div>
                        <div style={{ fontSize: 14, color: '#0f172a', fontWeight: 600 }}>{app.url_service || '-'}</div>
                    </div>
                    <div>
                        <div style={{ fontSize: 11, color: '#94a3b8', fontWeight: 700, marginBottom: 6 }}>Repository</div>
                        <div style={{ fontSize: 14, color: '#0f172a', fontWeight: 600 }}>{app.url_repository || '-'}</div>
                    </div>
                    <div>
                        <div style={{ fontSize: 11, color: '#94a3b8', fontWeight: 700, marginBottom: 6 }}>Application IP</div>
                        <div style={{ fontSize: 14, color: '#0f172a', fontWeight: 600 }}>{app.ip_local || '-'}</div>
                    </div>
                    <div>
                        <div style={{ fontSize: 11, color: '#94a3b8', fontWeight: 700, marginBottom: 6 }}>API Docs</div>
                        <div style={{ fontSize: 14, color: '#0f172a', fontWeight: 600 }}>{app.url_api_docs || '-'}</div>
                    </div>
                </div>

                <div style={{ ...box(), flex: 1, padding: 16 }}>
                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 10 }}>
                        <div style={{ fontSize: 13, fontWeight: 800, color: '#0f172a' }}>Anomaly</div>
                        <div style={{ width: 10, height: 10, borderRadius: 99, background: '#2563eb' }} />
                    </div>
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
                        {(anomaliForApp.length > 0 ? anomaliForApp : Array(4).fill(null)).map((row, idx) => {
                            const isReal = !!row?.id_log_anomali;
                            const url = isReal ? (row.service?.url_service || row.aplikasi?.url_service || '-') : 'https://Indonesia';
                            const time = isReal ? (row.detected_at ? new Date(row.detected_at).toLocaleTimeString() : '-') : '10.20.30';
                            const sevRaw = String(row?.severity || '').toLowerCase();
                            const isCrit = sevRaw === 'critical' || sevRaw === 'high';
                            return (
                                <div key={idx} style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', borderBottom: idx < 3 ? '1px solid #f1f5f9' : 'none', paddingBottom: 8 }}>
                                    <div>
                                        <div style={{ fontSize: 10, fontWeight: 800, color: '#0f172a' }}>{url}</div>
                                        <div style={{ fontSize: 9, color: '#94a3b8', marginTop: 2 }}>{time}</div>
                                    </div>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: 4, background: isCrit ? '#fee2e2' : '#fef3c7', color: isCrit ? '#dc2626' : '#d97706', padding: '3px 8px', borderRadius: 8, fontSize: 9, fontWeight: 800 }}>
                                        <AlertTriangle size={10} /> {isCrit ? 'Critical' : 'Medium'}
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </div>

            {isMicroservice && (
                <>
                    <div style={{ fontSize: 18, fontWeight: 800, color: '#0f172a', marginBottom: 12, textAlign: 'center' }}>Monitoring Summary</div>

                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4, 1fr)', gap: 16, marginBottom: 18 }}>
                        <div style={{
                            borderRadius: 20,
                            padding: '18px 16px',
                            minHeight: 128,
                            background: 'linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%)',
                            boxShadow: '0 10px 24px rgba(37,99,235,0.18)',
                            display: 'flex',
                            flexDirection: 'column',
                            alignItems: 'center',
                            justifyContent: 'center',
                            color: '#fff',
                        }}>
                            <div style={{ fontSize: 12, fontWeight: 800, marginBottom: 10 }}>Total Service</div>
                            <div style={{ fontSize: 56, fontWeight: 900, lineHeight: 1 }}>{services.length}</div>
                        </div>
                        {[
                            { label: 'Service Up', value: servicesUpStable },
                            { label: 'Service Down', value: servicesDown },
                            { label: 'Warning', value: servicesWarning },
                        ].map(({ label, value }) => (
                            <div key={label} style={{ ...box(), padding: '18px 16px', minHeight: 128, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center' }}>
                                <div style={{ fontSize: 12, fontWeight: 800, color: '#0f172a', marginBottom: 10 }}>{label}</div>
                                <div style={{ fontSize: 56, fontWeight: 900, color: '#0f172a', lineHeight: 1 }}>{value}</div>
                            </div>
                        ))}
                    </div>

                    <div style={{ ...box(), overflow: 'hidden' }}>
                        <table style={{ width: '100%', borderCollapse: 'collapse', fontSize: 13 }}>
                            <thead>
                                <tr style={{ background: '#f1f5f9', borderBottom: '1px solid #e2e8f0' }}>
                                    {['Service Name', 'Type', 'URL', 'View'].map((h, idx) => (
                                        <th key={h} style={{ padding: '14px 18px', textAlign: idx === 0 ? 'left' : 'center', fontWeight: 700, color: '#94a3b8', fontSize: 12 }}>
                                            {h}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {services.length === 0 ? (
                                    <tr><td colSpan={4} style={{ padding: 44, textAlign: 'center', color: '#94a3b8' }}>No services configured.</td></tr>
                                ) : services.map((svc) => {
                                    const tb = serviceTypeBadge(svc.type_service);
                                    return (
                                        <tr key={svc.id_service} style={{ borderBottom: '1px solid #f1f5f9' }}>
                                            <td style={{ padding: '14px 18px', fontWeight: 700, color: '#0f172a' }}>{svc.nama}</td>
                                            <td style={{ padding: '14px 18px', textAlign: 'center' }}>
                                                <span style={{ background: tb.bg, color: tb.color, padding: '3px 10px', borderRadius: 6, fontSize: 11, fontWeight: 700 }}>
                                                    {tb.label}
                                                </span>
                                            </td>
                                            <td style={{ padding: '14px 18px', textAlign: 'center', color: '#64748b' }}>{svc.url_service || '-'}</td>
                                            <td style={{ padding: '14px 18px', textAlign: 'center' }}>
                                                <button
                                                    type="button"
                                                    onClick={() => svc.url_service && window.open(svc.url_service, '_blank', 'noreferrer')}
                                                    style={{ background: 'transparent', border: 'none', cursor: svc.url_service ? 'pointer' : 'default', color: '#cbd5e1', padding: 6 }}
                                                >
                                                    <Eye size={18} />
                                                </button>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                </>
            )}

            {isMonolith && (
                <div style={{ display: 'flex', gap: 18, marginTop: 18 }}>
                    <div style={{ ...box(), width: 240, padding: 22, display: 'flex', flexDirection: 'column', justifyContent: 'center' }}>
                        <div style={{ fontSize: 12, fontWeight: 800, color: '#94a3b8', marginBottom: 12 }}>Response Time</div>
                        <div style={{ fontSize: 72, fontWeight: 900, color: '#0f172a', lineHeight: 1 }}>{latestRt || 0}</div>
                    </div>
                    <div style={{ ...box(), flex: 1, padding: 18, minHeight: 220 }}>
                        <div style={{ height: 180 }}>
                            <Line data={chartData} options={chartOpts} />
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default UserAplikasiDetail;
