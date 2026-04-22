import { useState, useEffect } from "react";
import axios from 'axios';
import { useNavigate, useLocation } from 'react-router-dom';
import { AlertCircle, Loader2 } from 'lucide-react';

// ── SVG Logos ──────────────────────────────────────────────────────────────────

const KominfoLogo = () => (
  <img 
    src="/images/logo-kominfo.svg" 
    alt="Logo Kominfo" 
    style={{ width: '68px', height: '68px', objectFit: 'contain' }} 
  />
);

const SentinelLogo = () => (
  <svg width="32" height="32" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="20" cy="10" r="5" fill="white"/>
    <circle cx="8" cy="30" r="5" fill="white"/>
    <circle cx="32" cy="30" r="5" fill="white"/>
    <line x1="20" y1="14" x2="8" y2="26" stroke="white" strokeWidth="2.5" strokeLinecap="round"/>
    <line x1="20" y1="14" x2="32" y2="26" stroke="white" strokeWidth="2.5" strokeLinecap="round"/>
    <line x1="8" y1="30" x2="32" y2="30" stroke="white" strokeWidth="2.5" strokeLinecap="round"/>
  </svg>
);

const ApiNodeIcon = () => (
  <svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="26" cy="14" r="7" fill="#1a56db"/>
    <circle cx="12" cy="38" r="7" fill="#1a56db"/>
    <circle cx="40" cy="38" r="7" fill="#1a56db"/>
    <line x1="26" y1="20" x2="12" y2="32" stroke="#1a56db" strokeWidth="3" strokeLinecap="round"/>
    <line x1="26" y1="20" x2="40" y2="32" stroke="#1a56db" strokeWidth="3" strokeLinecap="round"/>
    <line x1="12" y1="38" x2="40" y2="38" stroke="#1a56db" strokeWidth="3" strokeLinecap="round"/>
    <circle cx="26" cy="14" r="4" fill="white"/>
    <circle cx="12" cy="38" r="4" fill="white"/>
    <circle cx="40" cy="38" r="4" fill="white"/>
  </svg>
);

// ── Floating orbit icons ────────────────────────────────────────────────────────
const GlobeIcon = () => (
  <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
    <circle cx="12" cy="12" r="10" stroke="white" strokeWidth="1.8"/>
    <ellipse cx="12" cy="12" rx="4" ry="10" stroke="white" strokeWidth="1.8"/>
    <line x1="2" y1="12" x2="22" y2="12" stroke="white" strokeWidth="1.8"/>
    <line x1="4" y1="7" x2="20" y2="7" stroke="white" strokeWidth="1.5"/>
    <line x1="4" y1="17" x2="20" y2="17" stroke="white" strokeWidth="1.5"/>
  </svg>
);

const FolderIcon = () => (
  <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
    <path d="M2 6C2 5 3 4 4 4H10L12 6H20C21 6 22 7 22 8V18C22 19 21 20 20 20H4C3 20 2 19 2 18V6Z" stroke="white" strokeWidth="1.8" fill="white" fillOpacity="0.25"/>
  </svg>
);

const LinkIcon = () => (
  <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" stroke="white" strokeWidth="1.8" strokeLinecap="round"/>
    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" stroke="white" strokeWidth="1.8" strokeLinecap="round"/>
  </svg>
);

const ChartIcon = () => (
  <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
    <rect x="3" y="3" width="18" height="18" rx="2" stroke="white" strokeWidth="1.8"/>
    <polyline points="7,16 10,11 13,14 17,8" stroke="white" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round"/>
    <polyline points="15,8 17,8 17,10" stroke="white" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round"/>
  </svg>
);

// ── Main Component ──────────────────────────────────────────────────────────────

export default function Login() {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const [mounted, setMounted] = useState(false);
  const [splashDone, setSplashDone] = useState(false);
  const [showSplash, setShowSplash] = useState(true);

  const navigate = useNavigate();
  const location = useLocation();

  useEffect(() => {
    // Check if user is already logged in
    const token = localStorage.getItem('token');
    if (token) {
      navigate('/', { replace: true });
    }

    // Phase 1: splash visible + fade-in
    const t1 = setTimeout(() => setMounted(true), 50);
    // Phase 2: splash fades out
    const t2 = setTimeout(() => setSplashDone(true), 1800);
    // Phase 3: remove splash from DOM
    const t3 = setTimeout(() => setShowSplash(false), 2400);
    return () => { clearTimeout(t1); clearTimeout(t2); clearTimeout(t3); };
  }, [navigate]);

  const handleSubmit = async (e) => {
    e?.preventDefault();
    setError('');
    setLoading(true);

    try {
      const response = await axios.post('/api/admin/login', { email, password });
      
      if (response.data.success) {
        localStorage.setItem('token', response.data.token);
        localStorage.setItem('user', JSON.stringify(response.data.user));
        axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
        
        // Redirect to the page they were trying to access, or dashboard
        const from = location.state?.from?.pathname || "/";
        navigate(from, { replace: true });
      }
    } catch (err) {
      setError(err.response?.data?.message || 'The provided credentials are incorrect.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <style>{`
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap');

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
          font-family: 'Nunito', sans-serif;
          background: transparent;
          min-height: 100vh;
          overflow: hidden;
        }

        /* ── Splash ── */
        .splash {
          position: fixed; inset: 0; z-index: 999;
          display: flex; align-items: center; justify-content: center;
          background: #1338be;
          transition: opacity 0.6s ease, visibility 0.6s ease;
        }
        .splash.fade-out { opacity: 0; visibility: hidden; }
        .splash-inner {
          display: flex; align-items: center; gap: 14px;
          opacity: 0; transform: scale(0.85);
          transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .splash-inner.show { opacity: 1; transform: scale(1); }

        /* ── Background ── */
        .bg {
          position: fixed; inset: 0;
          background-image: url('/images/bg-login.svg');
          background-size: cover;
          background-position: center;
          background-repeat: no-repeat;
          overflow: hidden;
          z-index: -1;
        }
        .bg-blob {
          position: absolute; border-radius: 50%;
          filter: blur(80px); /* Tambahin blur biar nyatu sama SVG */
          z-index: 0;
        }
        .blob1 {
          width: 55vw; height: 55vw; top: -18%; left: -8%;
          background: radial-gradient(circle at 40% 40%, #1a56db88, #1338be00 70%);
        }
        .blob2 {
          width: 45vw; height: 45vw; top: -10%; right: -5%;
          background: radial-gradient(circle at 60% 30%, #00aaff33, #1338be00 70%);
        }
        .blob3 {
          width: 40vw; height: 40vw; bottom: -15%; right: -5%;
          background: radial-gradient(circle at 60% 70%, #0ea5e955, #1338be00 70%);
        }
        /* Curved wave shapes matching reference */
        .wave {
          position: absolute;
          border-radius: 50%;
          border: 1.5px solid rgba(255,255,255,0.08);
        }
        .wave1 { width: 70vw; height: 70vw; top: -25%; left: -15%; }
        .wave2 { width: 55vw; height: 55vw; top: -20%; left: -10%; }

        /* ── Page wrapper ── */
        .page {
          position: relative; z-index: 1;
          min-height: 100vh;
          display: flex; flex-direction: column;
          opacity: 0; transform: translateY(16px);
          transition: opacity 0.7s ease 2.1s, transform 0.7s ease 2.1s;
        }
        .page.visible { opacity: 1; transform: translateY(0); }

        /* ── Top bar ── */
        .topbar {
          display: flex; justify-content: space-between; align-items: flex-start;
          padding: 24px 32px 0;
        }
        .title-block { color: white; }
        .title-block .subtitle {
          font-size: 13px; font-weight: 600; letter-spacing: 0.02em;
          opacity: 0.9; line-height: 1.4;
        }
        .title-block .main-title {
          font-size: 15px; font-weight: 800; letter-spacing: 0.04em;
          line-height: 1.3;
        }
        .title-block .api-badge {
          color: #60c3ff; font-weight: 900;
        }

        .kominfo-badge {
          display: flex; align-items: center; gap: 10px;
          background: rgba(255,255,255,0.12);
          border: 1px solid rgba(255,255,255,0.22);
          border-radius: 12px; padding: 8px 14px;
          backdrop-filter: blur(10px);
        }
        .kominfo-badge .badge-text {
          color: white; font-size: 11px; font-weight: 700;
          line-height: 1.35; text-align: left;
        }

        /* ── Center area ── */
        .center {
          flex: 1; display: flex; align-items: center; justify-content: center;
          padding: 20px;
          position: relative;
        }

        /* ── Orbit ring ── */
        .orbit-wrap {
          position: absolute;
          left: calc(50% - 380px);
          top: 50%; transform: translateY(-50%);
          width: 320px; height: 320px;
        }
        .orbit-ring {
          position: absolute; inset: 0;
          border-radius: 50%;
          border: 1.5px solid rgba(255,255,255,0.18);
          animation: spin 20s linear infinite;
        }
        .orbit-ring-inner {
          position: absolute;
          inset: 40px;
          border-radius: 50%;
          border: 1.5px dashed rgba(255,255,255,0.12);
          animation: spin 15s linear infinite reverse;
        }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        .orbit-center {
          position: absolute;
          top: 50%; left: 50%;
          transform: translate(-50%, -50%);
          width: 90px; height: 90px;
          background: white;
          border-radius: 50%;
          display: flex; align-items: center; justify-content: center;
          box-shadow: 0 8px 32px rgba(0,0,0,0.25);
        }

        .orbit-icon {
          position: absolute;
          width: 42px; height: 42px;
          background: rgba(255,255,255,0.15);
          border: 1.5px solid rgba(255,255,255,0.3);
          border-radius: 50%;
          display: flex; align-items: center; justify-content: center;
          backdrop-filter: blur(8px);
        }
        /* Position icons on the ring - top, left, right, bottom */
        .icon-top    { top: -21px; left: 50%; transform: translateX(-50%); }
        .icon-left   { left: -21px; top: 50%; transform: translateY(-50%); }
        .icon-right  { right: -21px; top: 50%; transform: translateY(-50%); }
        .icon-bottom { bottom: -21px; left: 50%; transform: translateX(-50%); }

        /* Lines connecting center to icons */
        .orbit-line {
          position: absolute; top: 50%; left: 50%;
          width: 1.5px; background: rgba(255,255,255,0.25);
          transform-origin: top center;
        }
        .line-top    { height: 115px; transform: rotate(0deg) translateX(-50%) translateY(-100%); }
        .line-left   { height: 115px; transform: rotate(-90deg) translateX(-50%) translateY(-100%); }
        .line-right  { height: 115px; transform: rotate(90deg) translateX(-50%) translateY(-100%); }
        .line-bottom { height: 115px; transform: rotate(180deg) translateX(-50%) translateY(-100%); }

        /* ── Login card ── */
        .card {
          width: 400px;
          background: rgba(30, 70, 200, 0.45);
          border: 1px solid rgba(255,255,255,0.18);
          border-radius: 24px;
          padding: 48px 40px 44px;
          backdrop-filter: blur(24px);
          box-shadow: 0 20px 60px rgba(0,0,0,0.35), 0 0 0 1px rgba(255,255,255,0.08) inset;
          position: relative;
          /* partial overlap with orbit circle */
          margin-left: -48px;
        }

        .card h1 {
          text-align: center; color: white;
          font-size: 28px; font-weight: 800;
          margin-bottom: 32px; letter-spacing: 0.01em;
        }

        .input-wrap {
          position: relative; margin-bottom: 14px;
        }
        .input-icon {
          position: absolute; left: 16px; top: 50%; transform: translateY(-50%);
          opacity: 0.7; pointer-events: none;
          display: flex; align-items: center;
        }
        .login-input {
          width: 100%; padding: 14px 16px 14px 48px;
          background: rgba(255,255,255,0.12);
          border: 1px solid rgba(255,255,255,0.22);
          border-radius: 50px;
          color: white; font-size: 14px; font-weight: 600;
          font-family: 'Nunito', sans-serif;
          outline: none;
          transition: border-color 0.2s, background 0.2s;
        }
        .login-input::placeholder { color: rgba(255,255,255,0.55); font-weight: 600; }
        .login-input:focus {
          border-color: rgba(255,255,255,0.55);
          background: rgba(255,255,255,0.18);
        }

        .btn-login {
          width: 100%; padding: 15px;
          margin-top: 10px;
          background: #0d2b9e;
          border: none; border-radius: 50px;
          color: white; font-size: 15px; font-weight: 700;
          font-family: 'Nunito', sans-serif;
          cursor: pointer;
          letter-spacing: 0.03em;
          transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
          box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .btn-login:hover {
          background: #0a2080;
          transform: translateY(-1px);
          box-shadow: 0 8px 28px rgba(0,0,0,0.4);
        }
        .btn-login:active { transform: translateY(0); }

        /* ── Footer ── */
        .footer {
          display: flex; flex-direction: column; align-items: center; gap: 6px;
          padding: 18px 20px 24px;
          color: white;
        }
        .footer-brand {
          display: flex; align-items: center; gap: 10px;
        }
        .footer-sentinel {
          display: flex; align-items: center; gap: 8px;
        }
        .sentinel-name {
          font-size: 26px; font-weight: 900; color: white; letter-spacing: -0.02em;
        }
        .by-text {
          font-size: 11px; font-weight: 700; color: rgba(255,255,255,0.7);
          align-self: flex-end; margin-bottom: 4px;
        }
        .footer-copy {
          font-size: 12px; font-weight: 500; color: rgba(255,255,255,0.65);
          letter-spacing: 0.01em;
        }
        .footer-divider {
          width: 2px; height: 36px;
          background: rgba(255,255,255,0.3);
          margin: 0 6px;
        }
        .kominfo-foot {
          display: flex; align-items: center; gap: 6px;
        }
        .kominfo-foot-text {
          font-size: 11px; font-weight: 800; color: white;
          text-align: center; line-height: 1.3;
        }
      `}</style>

      {/* Splash screen */}
      {showSplash && (
        <div className={`splash${splashDone ? " fade-out" : ""}`}>
          <div className={`splash-inner${mounted ? " show" : ""}`}>
            <SentinelLogo />
            <span style={{
              fontSize: 36, fontWeight: 900, color: "white",
              fontFamily: "'Nunito', sans-serif", letterSpacing: "-0.02em"
            }}>sentinel</span>
          </div>
        </div>
      )}

      {/* Background */}
      <div className="bg">
        <div className="bg-blob blob1" />
        <div className="bg-blob blob2" />
        <div className="bg-blob blob3" />
        <div className="wave wave1" />
        <div className="wave wave2" />
      </div>

      {/* Main page */}
      <div className={`page${!showSplash ? " visible" : ""}`}>
        {/* Top bar */}
        <div className="topbar">
          <div className="title-block">
            <div className="subtitle">Dashboard</div>
            <div className="subtitle">Monitoring <span className="api-badge">API</span></div>
          </div>
          <div className="kominfo-badge">
            <KominfoLogo />
            <div className="badge-text">
              Dinas Komunikasi<br/>
              dan Informatika<br/>
              Jawa Timur
            </div>
          </div>
        </div>

        {/* Center: orbit + card */}
        <div className="center">
          {/* Orbit ring */}
          <div className="orbit-wrap">
            <div className="orbit-ring">
              <div className="orbit-line line-top" />
              <div className="orbit-line line-left" />
              <div className="orbit-line line-right" />
              <div className="orbit-line line-bottom" />

              <div className="orbit-icon icon-top"><GlobeIcon /></div>
              <div className="orbit-icon icon-left"><FolderIcon /></div>
              <div className="orbit-icon icon-right"><LinkIcon /></div>
              <div className="orbit-icon icon-bottom"><ChartIcon /></div>
            </div>
            <div className="orbit-ring-inner" />
            <div className="orbit-center">
              <ApiNodeIcon />
            </div>
          </div>

          {/* Login card */}
          <form className="card" onSubmit={handleSubmit}>
            <h1>Log In</h1>

            {error && (
              <div style={{
                background: 'rgba(239, 68, 68, 0.2)',
                backdropFilter: 'blur(12px)',
                border: '1px solid rgba(239, 68, 68, 0.3)',
                color: '#fee2e2',
                padding: '12px 16px',
                borderRadius: '16px',
                display: 'flex',
                alignItems: 'center',
                gap: '12px',
                fontSize: '13px',
                fontWeight: '600',
                marginBottom: '20px'
              }}>
                <AlertCircle size={18} style={{ flexShrink: 0 }} />
                {error}
              </div>
            )}

            {/* Username */}
            <div className="input-wrap">
              <span className="input-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                  <circle cx="12" cy="8" r="4" stroke="white" strokeWidth="2"/>
                  <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" stroke="white" strokeWidth="2" strokeLinecap="round"/>
                </svg>
              </span>
              <input
                className="login-input"
                type="text"
                placeholder="Username"
                value={email}
                onChange={e => setEmail(e.target.value)}
                autoComplete="username"
                required
              />
            </div>

            {/* Password */}
            <div className="input-wrap">
              <span className="input-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                  <rect x="5" y="11" width="14" height="10" rx="2" stroke="white" strokeWidth="2"/>
                  <path d="M8 11V7a4 4 0 1 1 8 0v4" stroke="white" strokeWidth="2" strokeLinecap="round"/>
                  <circle cx="12" cy="16" r="1.5" fill="white"/>
                </svg>
              </span>
              <input
                className="login-input"
                type="password"
                placeholder="Password"
                value={password}
                onChange={e => setPassword(e.target.value)}
                autoComplete="current-password"
                required
              />
            </div>

            <button 
              type="submit" 
              className="btn-login" 
              disabled={loading}
              style={{ display: 'flex', alignItems: 'center', justifyContent: 'center' }}
            >
              {loading ? <Loader2 size={20} className="animate-spin" /> : 'Log In'}
            </button>
          </form>
        </div>

        {/* Footer */}
        <div className="footer">
          <div className="footer-brand">
            {/* Kominfo foot logo */}
            <div className="kominfo-foot">
            <img 
              src="/images/logo-kominfo.svg" 
               alt="Logo Kominfo" 
              style={{ width: '68px', height: '68px', objectFit: 'contain' }} 
            />
              <div className="kominfo-foot-text">KOMINFO<br/>JATIM</div>
            </div>

            <div className="footer-divider" />

            {/* Sentinel */}
            <div className="footer-sentinel">
              <SentinelLogo />
              <span className="sentinel-name">sentinel</span>
              <span className="by-text">by PancaSolva</span>
            </div>
          </div>
          <div className="footer-copy">©Pancasolve 2026. All Rights Reserved.</div>
        </div>
      </div>
    </>
  );
}
