import React, { useState, useEffect } from 'react';
import { TrendingUp, Activity, AlertTriangle } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import api from '../api';
import {
    Chart as ChartJS,
    CategoryScale, LinearScale, PointElement, LineElement,
    BarElement, Title, Tooltip, Legend, Filler, ArcElement,
} from 'chart.js';
import { Line, Bar, Doughnut } from 'react-chartjs-2';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend, Filler, ArcElement);

const WARNING_THRESHOLD_MS = 1000;

const card = (extra = {}) => ({
    background: '#fff', borderRadius: 20,
    boxShadow: '0 2px 8px rgba(0,0,0,0.06)', ...extra,
});

const UserDashboard = () => {
    const navigate = useNavigate();
    const [stats, setStats] = useState({ totalAplikasi: 0, totalUp: 0, totalDown: 0, totalWarning: 0 });
    const [monitoringData, setMonitoringData] = useState([]);
    const [aplikasiList, setAplikasiList] = useState([]);
    const [anomalyList, setAnomalyList] = useState([]);
    const [avgResponseTime, setAvgResponseTime] = useState(0);

    useEffect(() => {
        api.get('/dashboard-stats').then(r => {
            if (r.data?.data) {
                const d = r.data.data;
                setStats({ totalAplikasi: d.totalAplikasi || 0, totalUp: d.totalUp || 0, totalDown: d.totalDown || 0, totalWarning: d.totalWarning || 0 });
            }
        }).catch(() => {});

        api.get('/monitoring-logs').then(r => {
            if (r.data?.data) {
                const logs = Array.isArray(r.data.data) ? r.data.data : (r.data.data.data || []);
                setMonitoringData(logs.slice(0, 5));
                const times = logs.filter(l => l.response_time_ms).map(l => Number(l.response_time_ms));
                if (times.length) setAvgResponseTime(Math.round(times.reduce((a, b) => a + b, 0) / times.length));
                setAnomalyList(logs.filter(l => l.status && l.status.toUpperCase() === 'DOWN').slice(0, 8));
            }
        }).catch(() => {});

        api.get('/anomali-logs').then(r => {
            const payload = r.data?.data?.data || r.data?.data || [];
            if (Array.isArray(payload) && payload.length > 0) setAnomalyList(payload.slice(0, 8));
        }).catch(() => {});

        api.get('/aplikasi').then(r => {
            if (r.data?.data) setAplikasiList(Array.isArray(r.data.data) ? r.data.data.slice(0, 7) : []);
        }).catch(() => {});
    }, []);

    const now = new Date();
    const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    const dateStr = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;

    const total = stats.totalAplikasi || 0;
    const up = stats.totalUp || 0;
    const down = stats.totalDown || 0;
    const warn = stats.totalWarning || 0;
    const upStable = Math.max(up - warn, 0);
    const upPct = (upStable + down + warn) > 0 ? Math.round((upStable / (upStable + down + warn)) * 100) : 80;
    const pad2 = n => String(n).padStart(2, '0');
    const pad4 = n => String(n).padStart(4, '0');

    /* ── Charts ── */
    const lineData = {
        labels: ['Jan','Feb','Mar','Apr','May','Jun'],
        datasets: [{ data: [80,130,90,380,190,90], fill: true,
            backgroundColor: ctx => { const g = ctx.chart.ctx.createLinearGradient(0,0,0,160); g.addColorStop(0,'rgba(37,99,235,0.35)'); g.addColorStop(1,'rgba(37,99,235,0)'); return g; },
            borderColor: '#2563eb', borderWidth: 2.5, tension: 0.45, pointRadius: 0,
        }],
    };
    const lineOpts = { responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        scales: { x: { display: false }, y: { display: false, min: 0 } },
    };
    const donutData = {
        labels: ['Up','Down','Warning'],
        datasets: [{ data: [upStable || 80, down || 10, warn || 10], backgroundColor: ['#1d4ed8','#38bdf8','#93c5fd'], borderWidth: 0 }],
    };
    const donutOpts = { responsive: true, maintainAspectRatio: false, cutout: '68%',
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
    };
    const barData = {
        labels: ['21 April','22 April','23 April','24 April','25 April','26 April','27 April'],
        datasets: [
            { data: [55,65,62,38,70,52,60], backgroundColor: '#93c5fd', borderRadius: 3, barPercentage: 0.45, categoryPercentage: 0.85 },
            { data: [75,90,76,48,92,72,86], backgroundColor: '#1d4ed8', borderRadius: 3, barPercentage: 0.45, categoryPercentage: 0.85 },
            { data: [65,78,70,44,80,64,76], backgroundColor: '#2563eb', borderRadius: 3, barPercentage: 0.45, categoryPercentage: 0.85 },
        ],
    };
    const barOpts = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
        scales: { x: { grid: { display: false }, ticks: { font: { size: 9 }, color: '#94a3b8' } }, y: { display: false } },
    };

    const badge = (log) => {
        const httpCode = Number(log?.http_status_code || 0);
        const httpOk = httpCode >= 200 && httpCode < 300;
        const base = (log?.status || '').toUpperCase();
        if (!httpOk || base !== 'UP') return { bg:'#fee2e2', color:'#dc2626', label:'Down', Icon: Activity };
        const rt = Number(log?.response_time_ms || 0);
        if (rt >= WARNING_THRESHOLD_MS) return { bg:'#fef3c7', color:'#d97706', label:'Medium', Icon: AlertTriangle };
        return { bg:'#dcfce7', color:'#16a34a', label:'Up', Icon: TrendingUp };
    };
    const statusIcons = [
        { icon: <TrendingUp size={12} color="#16a34a" />, bg: '#bbf7d0' },
        { icon: <Activity size={12} color="#dc2626" />, bg: '#fecaca' },
        { icon: <AlertTriangle size={12} color="#ca8a04" />, bg: '#fef08a' },
    ];

    const dummyLogs = [
        { status:'up', url:'https://dummyadd' }, { status:'down', url:'https://dummyadd' },
        { status:'up', url:'https://dummyadd' }, { status:'up', url:'https://dummyadd' },
        { status:'down', url:'https://dummyadd' },
    ];
    const dummyAnomaly = Array(8).fill(null).map((_,i) => ({ url:'https://indonesia', time:'10.20.30', severity: i%2===0?'critical':'medium' }));

    const GridIcon = ({ color = 'rgba(255,255,255,0.8)', size = 22 }) => (
        <svg width={size} height={size} viewBox="0 0 24 24" fill="none">
            <rect x="3" y="3" width="7" height="7" rx="1.5" fill={color}/>
            <rect x="14" y="3" width="7" height="7" rx="1.5" fill={color}/>
            <rect x="3" y="14" width="7" height="7" rx="1.5" fill={color}/>
            <rect x="14" y="14" width="7" height="7" rx="1.5" fill={color}/>
        </svg>
    );

    return (
        <div style={{ display:'flex', gap:20, alignItems:'flex-start' }}>
            {/* ═══ LEFT MAIN ═══ */}
            <div style={{ flex:1, display:'flex', flexDirection:'column', gap:16, minWidth:0 }}>
                {/* Row 1: Date + Total Apps + Status Cards */}
                <div style={{ display:'flex', gap:12, alignItems:'stretch', height:210 }}>
                    <div style={{ display:'flex', flexDirection:'column', gap:10, flex:'0 0 220px' }}>
                        <div style={{ background:'#1d4ed8', color:'#fff', borderRadius:999, padding:'6px 20px', fontSize:12, fontWeight:700, width:'fit-content' }}>
                            {dateStr}
                        </div>
                        <div style={{ ...card(), flex:1, position:'relative', overflow:'hidden', display:'flex', alignItems:'flex-end', padding:'16px 20px' }}>
                            <div style={{ position:'absolute', top:-30, left:-10, right:-10, height:'65%', background:'#2563eb', borderRadius:'0 0 80% 80%' }} />
                            <div style={{ position:'absolute', top:12, left:16, zIndex:1 }}><GridIcon /></div>
                            <div style={{ position:'relative', zIndex:1, display:'flex', alignItems:'baseline', gap:6 }}>
                                <span style={{ fontSize:64, fontWeight:900, color:'#1d4ed8', lineHeight:1 }}>{total || '123'}</span>
                                <div style={{ fontSize:11, fontWeight:700, color:'#1d4ed8', lineHeight:1.3 }}>Total<br/>Applications</div>
                            </div>
                        </div>
                    </div>
                    <div style={{ display:'flex', flexDirection:'column', gap:8, flex:1 }}>
                        {[
                            { val: upStable||90, label:'Up', bg:'#2563eb', iconBg:'#bbf7d0', Icon:TrendingUp, iconColor:'#16a34a' },
                            { val: down||5, label:'Down', bg:'#1d4ed8', iconBg:'#fecaca', Icon:Activity, iconColor:'#dc2626' },
                            { val: warn||5, label:'Warning', bg:'#38bdf8', iconBg:'#fef08a', Icon:AlertTriangle, iconColor:'#ca8a04' },
                        ].map((item,i) => (
                            <div key={i} style={{ flex:1, background:item.bg, borderRadius:16, display:'flex', alignItems:'center', justifyContent:'space-between', padding:'0 20px' }}>
                                <div style={{ display:'flex', alignItems:'baseline', gap:8, color:'#fff' }}>
                                    <span style={{ fontSize:36, fontWeight:900 }}>{pad2(item.val)}</span>
                                    <span style={{ fontSize:13, fontWeight:700 }}>{item.label}</span>
                                </div>
                                <div style={{ width:38, height:38, background:item.iconBg, borderRadius:10, display:'flex', alignItems:'center', justifyContent:'center' }}>
                                    <item.Icon size={20} color={item.iconColor} />
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Row 2: Avg Response Time + Donut */}
                <div style={{ display:'flex', gap:16, height:220 }}>
                    <div style={{ ...card(), flex:2, padding:'18px 20px', display:'flex', flexDirection:'column', position:'relative', overflow:'hidden' }}>
                        <div style={{ fontSize:14, fontWeight:700, color:'#1e293b', marginBottom:4 }}>Avg. Response Time</div>
                        <div style={{ position:'absolute', top:'50%', left:'50%', transform:'translate(-50%,-50%)', fontSize:28, fontWeight:900, color:'#1d4ed8', zIndex:5, pointerEvents:'none' }}>{pad4(avgResponseTime||5)}</div>
                        <div style={{ flex:1, marginTop:8 }}><Line data={lineData} options={lineOpts} /></div>
                    </div>
                    <div style={{ ...card({ border:'2.5px solid #1d4ed8' }), flex:1, display:'flex', flexDirection:'column', alignItems:'center', position:'relative', overflow:'hidden', paddingTop:32, paddingBottom:16 }}>
                        <div style={{ position:'absolute', top:0, background:'#1d4ed8', color:'#fff', fontSize:11, fontWeight:700, padding:'5px 24px', borderRadius:'0 0 12px 12px' }}>This Month</div>
                        <div style={{ flex:1, width:'100%', position:'relative', display:'flex', alignItems:'center', justifyContent:'center' }}>
                            <Doughnut data={donutData} options={donutOpts} />
                            <div style={{ position:'absolute', fontSize:15, fontWeight:900, color:'#1d4ed8', textAlign:'center' }}>{upPct}% Up</div>
                        </div>
                    </div>
                </div>

                {/* Row 3: Performance Overview */}
                <div style={{ ...card(), padding:'18px 20px' }}>
                    <div style={{ fontSize:14, fontWeight:700, color:'#1e293b', marginBottom:16 }}>Perfomance Overview</div>
                    <div style={{ height:180 }}><Bar data={barData} options={barOpts} /></div>
                </div>

                {/* Row 4: Recent Monitoring Log */}
                <div style={{ ...card({ overflow:'hidden' }) }}>
                    <div style={{ background:'#2563eb', color:'#fff', display:'flex', alignItems:'center', justifyContent:'center', gap:12, padding:'12px 0', fontSize:14, fontWeight:700 }}>
                        <GridIcon size={20} /> Recent Monitoring Log
                    </div>
                    <table style={{ width:'100%', borderCollapse:'collapse', fontSize:12 }}>
                        <thead>
                            <tr style={{ borderBottom:'1px solid #f1f5f9' }}>
                                {['Url','Status','HTTP Code','Response Time','Timestamp'].map(h => (
                                    <th key={h} style={{ padding:'12px 16px', textAlign:'left', fontWeight:700, color:'#374151', fontSize:12 }}>{h}</th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {(monitoringData.length > 0 ? monitoringData : dummyLogs).map((log, i) => {
                                const b = badge(log);
                                const url = log.aplikasi?.url_service || log.service?.url_service || log.url || 'https://dummyadd';
                                return (
                                    <tr key={i} style={{ borderBottom:'1px solid #f8fafc' }}>
                                        <td style={{ padding:'10px 16px', color:'#64748b' }}>{url}</td>
                                        <td style={{ padding:'10px 16px' }}>
                                            <span style={{ background:b.bg, color:b.color, padding:'2px 8px', borderRadius:4, fontSize:11, fontWeight:700, display:'inline-flex', alignItems:'center', gap:4 }}>
                                                <b.Icon size={10} /> {b.label}
                                            </span>
                                        </td>
                                        <td style={{ padding:'10px 16px', color:'#64748b' }}>{log.http_status_code || 200}</td>
                                        <td style={{ padding:'10px 16px', color:'#64748b' }}>{log.response_time_ms ? `${log.response_time_ms}ms` : '15ms'}</td>
                                        <td style={{ padding:'10px 16px', color:'#64748b' }}>{log.checked_at ? new Date(log.checked_at).toLocaleTimeString() : '11.21.05'}</td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* ═══ RIGHT SIDEBAR ═══ */}
            <div style={{ width:260, display:'flex', flexDirection:'column', gap:14, flexShrink:0 }}>
                {/* Your Apps */}
                <div style={{ background:'#2563eb', borderRadius:20, overflow:'hidden' }}>
                    <div style={{ padding:'18px 16px 32px', color:'#fff', textAlign:'center' }}>
                        <div style={{ fontSize:20, fontWeight:900 }}>Your Apps</div>
                    </div>
                    <div style={{ background:'#fff', borderTopLeftRadius:24, borderTopRightRadius:24, marginTop:-16, padding:'14px 12px 12px' }}>
                        <div style={{ textAlign:'center', fontSize:14, fontWeight:800, color:'#1e293b', marginBottom:10 }}>App List</div>
                        <div style={{ display:'flex', flexDirection:'column', gap:6, maxHeight:340, overflowY:'auto' }}>
                            {(aplikasiList.length > 0 ? aplikasiList : Array(7).fill(null)).map((app, i) => {
                                const isReal = !!app?.id_aplikasi;
                                const rt = isReal ? Number(app.last_response_time || 0) : 200;
                                const ts = isReal ? (app.lastchecked ? new Date(app.lastchecked).toLocaleTimeString() : '-') : '11.23.03';
                                const status = (app?.status || '').toUpperCase();
                                const isDown = status === 'DOWN';
                                const isMedium = !isDown && rt >= WARNING_THRESHOLD_MS;
                                const si = isDown ? statusIcons[1] : (isMedium ? statusIcons[2] : statusIcons[0]);
                                return (
                                    <div key={i} onClick={() => app && navigate(`/user-aplikasi/${app.id_aplikasi}`)}
                                        style={{ display:'flex', alignItems:'center', justifyContent:'space-between', border:'1px solid #e2e8f0', borderRadius:10, padding:'6px 10px', cursor: app ? 'pointer' : 'default', transition:'background 0.15s' }}
                                        onMouseEnter={e => { if(app) e.currentTarget.style.background='#f8fafc'; }}
                                        onMouseLeave={e => { e.currentTarget.style.background=''; }}
                                    >
                                        <div style={{ display:'flex', alignItems:'center', gap:6 }}>
                                            <div style={{ width:3, height:28, background:'#60a5fa', borderRadius:99 }} />
                                            <div>
                                                <div style={{ fontSize:10, fontWeight:700, color:'#1e293b' }}>{app?.nama || 'Aplication Name'}</div>
                                                <div style={{ fontSize:9, background:'#dbeafe', color:'#2563eb', borderRadius:4, padding:'1px 5px', marginTop:2, display:'inline-block', fontWeight:600 }}>
                                                    {app?.tipe ? app.tipe.charAt(0).toUpperCase()+app.tipe.slice(1) : 'Microservice'}
                                                </div>
                                            </div>
                                        </div>
                                        <div style={{ display:'flex', alignItems:'center', gap:6 }}>
                                            <div style={{ textAlign:'right' }}>
                                                <div style={{ fontSize:10, fontWeight:700, color:'#1e293b' }}>{rt ? `${rt}ms` : '-'}</div>
                                                <div style={{ fontSize:9, color:'#94a3b8', marginTop:1 }}>{ts}</div>
                                            </div>
                                            <div style={{ width:26, height:26, background:si.bg, borderRadius:7, display:'flex', alignItems:'center', justifyContent:'center' }}>{si.icon}</div>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>

                {/* Anomaly */}
                <div style={{ background:'#fff', borderRadius:20, overflow:'hidden', border:'1px solid #e2e8f0' }}>
                    <div style={{ textAlign:'center', padding:'14px 0 8px', fontSize:18, fontWeight:900, color:'#1e293b', fontStyle:'italic' }}>Anomaly</div>
                    <div style={{ padding:'0 14px 14px', display:'flex', flexDirection:'column', maxHeight:320, overflowY:'auto' }}>
                        {(anomalyList.length > 0 ? anomalyList : dummyAnomaly).map((item, i, arr) => {
                            const isReal = !!item.aplikasi || !!item.detected_at || !!item.checked_at;
                            const url = isReal ? (item.service?.url_service || item.aplikasi?.url_service || item.url || 'https://indonesia') : (item.url || 'https://indonesia');
                            const ts = item.detected_at || item.checked_at;
                            const time = isReal ? (ts ? new Date(ts).toLocaleTimeString() : '10.20.30') : (item.time || '10.20.30');
                            const sevRaw = (item.severity || '').toLowerCase();
                            const st = (item.status || '').toUpperCase();
                            const isCrit = sevRaw === 'critical' || sevRaw === 'high' || (!sevRaw && st === 'DOWN');
                            return (
                                <div key={i} style={{ display:'flex', alignItems:'center', justifyContent:'space-between', borderBottom: i<arr.length-1 ? '1px solid #f1f5f9' : 'none', padding:'10px 0' }}>
                                    <div>
                                        <div style={{ fontSize:10, fontWeight:700, color:'#1e293b' }}>{url}</div>
                                        <div style={{ fontSize:9, color:'#94a3b8', marginTop:2 }}>{time}</div>
                                    </div>
                                    <div style={{ display:'flex', alignItems:'center', gap:4, background: isCrit ? '#fee2e2' : '#fef3c7', color: isCrit ? '#dc2626' : '#d97706', padding:'3px 8px', borderRadius:6, fontSize:9, fontWeight:700 }}>
                                        <AlertTriangle size={10} /> {isCrit ? 'Critical' : 'Medium'}
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </div>
        </div>
    );
};

export default UserDashboard;
