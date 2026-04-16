import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useNavigate, useLocation } from 'react-router-dom';
import { User, Lock, AlertCircle, Loader2, Share2, FileText, Link as LinkIcon, BarChart3, Globe } from 'lucide-react';

const Login = () => {
    console.log("Login page is rendering");
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [error, setError] = useState('');
    const [loading, setLoading] = useState(false);
    const navigate = useNavigate();
    const location = useLocation();

    // Check if user is already logged in, redirect to dashboard if so
    useEffect(() => {
        const token = localStorage.getItem('token');
        if (token) {
            navigate('/', { replace: true });
        }
    }, [navigate]);

    const handleLogin = async (e) => {
        e.preventDefault();
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
        <div 
        className="min-h-screen w-full relative overflow-hidden flex flex-col font-sans bg-cover bg-center bg-no-repeat"
        style={{ backgroundImage: "url('/images/bg-login.svg')" }}
        >
            


            {/* Top Navigation / Header */}
            <header className="relative z-10 flex justify-between items-start p-8 md:p-12">
                <div className="text-white">
                    <h1 className="text-xl md:text-2xl font-bold leading-tight tracking-tight">
                        Dashboard<br />
                        <span className="font-light opacity-90">Monitoring</span> API
                    </h1>
                </div>
                <div className="flex items-center gap-3 bg-white/10 backdrop-blur-md px-4 py-2 rounded-2xl border border-white/20">
                    <img 
                        src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/ad/Coat_of_arms_of_East_Java.svg/1200px-Coat_of_arms_of_East_Java.svg.png" 
                        alt="Logo Jatim" 
                        className="w-8 h-8 object-contain"
                    />
                    <div className="text-white text-[10px] md:text-xs font-bold leading-tight">
                        Dinas Komunikasi<br />
                        dan Informatika<br />
                        Provinsi Jawa Timur
                    </div>
                </div>
            </header>

            {/* Main Content: Login Card */}
            <main className="flex-1 relative z-10 flex items-center justify-center p-6">
                <div className="flex items-center justify-center gap-20 max-w-6xl w-full">
                    
                    {/* Floating Icons Decoration (Left side of card) */}
                    <div className="hidden lg:block relative w-64 h-64 pointer-events-none">
                        <div className="relative w-full h-full">
                            {/* Orbit lines */}
                            <div className="absolute inset-0 border border-white/20 rounded-full scale-[1.2]"></div>
                            <div className="absolute inset-0 border border-white/10 rounded-full scale-[1.5]"></div>
                            
                            {/* Icons on orbit */}
                            <div className="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white/20 backdrop-blur-lg p-2.5 rounded-lg border border-white/30 shadow-xl">
                                <Globe className="w-5 h-5 text-white" />
                            </div>
                            <div className="absolute top-1/4 left-0 -translate-x-1/2 bg-white/20 backdrop-blur-lg p-2.5 rounded-lg border border-white/30 shadow-xl">
                                <FileText className="w-5 h-5 text-white" />
                            </div>
                            <div className="absolute bottom-1/4 left-0 -translate-x-1/2 bg-white/20 backdrop-blur-lg p-2.5 rounded-lg border border-white/30 shadow-xl">
                                <LinkIcon className="w-5 h-5 text-white" />
                            </div>
                            <div className="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2 bg-white/20 backdrop-blur-lg p-2.5 rounded-lg border border-white/30 shadow-xl">
                                <BarChart3 className="w-5 h-5 text-white" />
                            </div>

                            {/* Center Logo Circle */}
                            <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-24 h-24 bg-white rounded-full flex items-center justify-center shadow-2xl shadow-blue-900/50 z-20">
                                <Share2 className="w-12 h-12 text-[#0047FF] transform rotate-90" />
                            </div>
                        </div>
                    </div>

                    {/* Glassmorphism Card */}
                    <div className="w-full max-w-md bg-white/10 backdrop-blur-xl border border-white/20 rounded-[40px] p-10 md:p-12 shadow-2xl shadow-black/20">
                        <h2 className="text-4xl font-black text-white mb-10 text-center tracking-tight">Log In</h2>
                        
                        <form onSubmit={handleLogin} className="space-y-6">
                            {error && (
                                <div className="bg-red-500/20 backdrop-blur-md border border-red-500/30 text-red-100 p-4 rounded-2xl flex items-center gap-3 text-sm font-medium animate-shake">
                                    <AlertCircle className="w-5 h-5 flex-shrink-0" />
                                    {error}
                                </div>
                            )}

                            <div className="space-y-4">
                                <div className="relative group">
                                    <div className="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white/20 flex items-center justify-center transition-all group-focus-within:bg-white/40">
                                        <User className="w-4 h-4 text-white" />
                                    </div>
                                    <input
                                        type="email"
                                        required
                                        className="w-full bg-white/10 border border-white/20 rounded-full py-3.5 pl-14 pr-6 text-white placeholder-white/50 focus:ring-2 focus:ring-white/50 outline-none transition-all font-medium text-sm"
                                        placeholder="Username"
                                        value={email}
                                        onChange={(e) => setEmail(e.target.value)}
                                    />
                                </div>

                                <div className="relative group">
                                    <div className="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white/20 flex items-center justify-center transition-all group-focus-within:bg-white/40">
                                        <Lock className="w-4 h-4 text-white" />
                                    </div>
                                    <input
                                        type="password"
                                        required
                                        className="w-full bg-white/10 border border-white/20 rounded-full py-3.5 pl-14 pr-6 text-white placeholder-white/50 focus:ring-2 focus:ring-white/50 outline-none transition-all font-medium text-sm"
                                        placeholder="Password"
                                        value={password}
                                        onChange={(e) => setPassword(e.target.value)}
                                    />
                                </div>
                            </div>

                            <button
                                type="submit"
                                disabled={loading}
                                className="w-full py-4 bg-[#0029FF] hover:bg-[#001AFF] text-white rounded-full font-black text-base shadow-xl shadow-blue-900/40 transition-all active:scale-[0.98] disabled:bg-white/20 disabled:text-white/50 disabled:shadow-none flex justify-center items-center"
                            >
                                {loading ? (
                                    <Loader2 className="w-6 h-6 animate-spin" />
                                ) : (
                                    'Log In'
                                )}
                            </button>
                        </form>
                    </div>
                </div>
            </main>

            {/* Footer */}
            <footer className="relative z-10 p-8 flex flex-col items-center gap-2">
                <div className="flex items-center gap-3">
                    {/* Bottom Logo Group */}
                    <div className="flex items-center gap-2">
                        {/* Eye logo style for Kominfo Jatim */}
                        <div className="w-8 h-8 relative flex items-center justify-center">
                            <div className="absolute inset-0 bg-white rounded-full"></div>
                            <div className="w-6 h-4 bg-[#0047FF] rounded-full relative overflow-hidden flex items-center justify-center">
                                <div className="w-2.5 h-2.5 bg-white rounded-full"></div>
                            </div>
                        </div>
                        <div className="text-white text-[8px] font-black leading-[1.1]">
                            KOMINFO<br />JATIM
                        </div>
                    </div>
                    
                    <div className="h-6 w-px bg-white/20 mx-1"></div>

                    {/* Asentinel Text Logo */}
                    <div className="flex items-center gap-1.5">
                        <Share2 className="w-5 h-5 text-white" />
                        <span className="text-white text-xl font-black tracking-tight italic">sentinel</span>
                        <span className="text-white/60 text-[10px] mt-1 ml-1 font-medium">by Pancasolve</span>
                    </div>
                </div>
                
                <p className="text-white/60 text-[10px] font-medium tracking-wide">
                    ©Pancasolve 2026. All Rights Reserved.
                </p>
            </footer>
        </div>
    );
};

export default Login;
