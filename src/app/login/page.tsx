"use client";

import { useState } from "react";
import Link from "next/link";
import { motion, AnimatePresence } from "framer-motion";
import { Button } from "@/components/ui/button";
import {
    Mail,
    Lock,
    ArrowRight,
    Eye,
    EyeOff,
    CheckCircle2,
    Chrome,
    Github,
    AlertCircle
} from "lucide-react";

export default function LoginPage() {
    const [email, setEmail] = useState("");
    const [password, setPassword] = useState("");
    const [showPassword, setShowPassword] = useState(false);
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState("");
    const [rememberMe, setRememberMe] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsLoading(true);
        setError("");

        try {
            const res = await fetch("http://etim.test/index.php?rest_route=/etim/v1/login", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    username: email,
                    password: password,
                }),
            });

            const data = await res.json();

            if (res.ok && data.success) {
                // Store symbols safely
                localStorage.setItem("etim_token", data.token);
                localStorage.setItem("etim_user_email", data.user_email);
                localStorage.setItem("etim_user_nicename", data.user_nicename);
                localStorage.setItem("etim_user_display_name", data.user_display_name);

                // Redirect to my-account
                window.location.href = "/my-account";
            } else {
                setError(data.message || "Invalid username or password");
            }
        } catch (err) {
            setError("Connection failed. Did you add the code to functions.php?");
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className="min-h-[calc(100vh-64px)] flex items-center justify-center bg-slate-50/50 p-4 sm:p-6 lg:p-8">
            {/* Decorative background elements */}
            <div className="absolute inset-0 z-0 overflow-hidden pointer-events-none">
                <div className="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-blue-400/10 rounded-full blur-[120px]" />
                <div className="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-indigo-400/10 rounded-full blur-[120px]" />
            </div>

            <motion.div
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.5 }}
                className="w-full max-w-md relative z-10"
            >
                <div className="mb-6">
                    <Link
                        href="/"
                        className="inline-flex items-center text-sm font-medium text-slate-500 hover:text-blue-600 transition-colors group"
                    >
                        <ArrowRight className="h-4 w-4 mr-2 rotate-180 group-hover:-translate-x-1 transition-transform" />
                        Back to website
                    </Link>
                </div>

                <div className="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                    <div className="p-8 pb-4">
                        <div className="mb-8 text-center">
                            <h1 className="text-3xl font-bold text-slate-900 mb-2">Welcome Back</h1>
                            <p className="text-slate-500">Sign in to manage your ETIM classifications</p>
                        </div>

                        <form onSubmit={handleSubmit} className="space-y-5">
                            <AnimatePresence mode="wait">
                                {error && (
                                    <motion.div
                                        initial={{ opacity: 0, height: 0 }}
                                        animate={{ opacity: 1, height: "auto" }}
                                        exit={{ opacity: 0, height: 0 }}
                                        className="p-3 bg-red-50 border border-red-100 rounded-xl flex items-center gap-3 text-red-600 text-sm"
                                    >
                                        <AlertCircle className="h-4 w-4 shrink-0" />
                                        <span>{error}</span>
                                    </motion.div>
                                )}
                            </AnimatePresence>

                            <div className="space-y-1.5">
                                <label className="text-sm font-semibold text-slate-700 ml-1">Username or Email</label>
                                <div className="relative group">
                                    <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                        <Mail className="h-5 w-5" />
                                    </div>
                                    <input
                                        type="text"
                                        required
                                        value={email}
                                        onChange={(e) => setEmail(e.target.value)}
                                        placeholder="Username or your email"
                                        className="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-900"
                                    />
                                </div>
                            </div>

                            <div className="space-y-1.5">
                                <div className="flex justify-between items-center ml-1">
                                    <label className="text-sm font-semibold text-slate-700">Password</label>
                                    <Link href="/forgot-password" className="text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                                        Forgot Password?
                                    </Link>
                                </div>
                                <div className="relative group">
                                    <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-600 transition-colors">
                                        <Lock className="h-5 w-5" />
                                    </div>
                                    <input
                                        type={showPassword ? "text" : "password"}
                                        required
                                        value={password}
                                        onChange={(e) => setPassword(e.target.value)}
                                        placeholder="••••••••"
                                        className="w-full pl-11 pr-11 py-3 bg-slate-50 border border-slate-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-slate-900"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => setShowPassword(!showPassword)}
                                        className="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                                    >
                                        {showPassword ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
                                    </button>
                                </div>
                            </div>

                            <div className="flex items-center gap-2 ml-1">
                                <button
                                    type="button"
                                    onClick={() => setRememberMe(!rememberMe)}
                                    className={`h-5 w-5 rounded-md border flex items-center justify-center transition-all ${rememberMe ? "bg-blue-600 border-blue-600" : "bg-white border-slate-300"
                                        }`}
                                >
                                    {rememberMe && <CheckCircle2 className="h-3.5 w-3.5 text-white" />}
                                </button>
                                <span className="text-sm text-slate-600 cursor-pointer select-none" onClick={() => setRememberMe(!rememberMe)}>
                                    Remember me for 30 days
                                </span>
                            </div>

                            <Button
                                type="submit"
                                disabled={isLoading}
                                className="w-full py-6 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-lg shadow-lg shadow-blue-600/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2"
                            >
                                {isLoading ? (
                                    <motion.div
                                        animate={{ rotate: 360 }}
                                        transition={{ repeat: Infinity, duration: 1, ease: "linear" }}
                                    >
                                        <ArrowRight className="h-6 w-6" />
                                    </motion.div>
                                ) : (
                                    <>
                                        Sign In
                                        <ArrowRight className="h-5 w-5" />
                                    </>
                                )}
                            </Button>
                        </form>

                        <div className="my-8 relative">
                            <div className="absolute inset-0 flex items-center">
                                <div className="w-full border-t border-slate-100"></div>
                            </div>
                            <div className="relative flex justify-center text-xs uppercase">
                                <span className="bg-white px-4 text-slate-400 font-medium">Or continue with</span>
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <button className="flex items-center justify-center gap-2 py-3 px-4 rounded-2xl border border-slate-200 hover:bg-slate-50 transition-colors group">
                                <Chrome className="h-5 w-5 text-slate-600 group-hover:text-red-500 transition-colors" />
                                <span className="text-sm font-semibold text-slate-700">Google</span>
                            </button>
                            <button className="flex items-center justify-center gap-2 py-3 px-4 rounded-2xl border border-slate-200 hover:bg-slate-50 transition-colors group">
                                <Github className="h-5 w-5 text-slate-600 group-hover:text-black transition-colors" />
                                <span className="text-sm font-semibold text-slate-700">GitHub</span>
                            </button>
                        </div>
                    </div>

                    <div className="p-8 bg-slate-50 border-t border-slate-100 text-center">
                        <p className="text-sm text-slate-600">
                            Don&apos;t have an account?{" "}
                            <Link href="/pricing" className="text-blue-600 font-bold hover:underline">
                                View Plans
                            </Link>
                        </p>
                    </div>
                </div>

                {/* Footer text */}
                <div className="mt-8 text-center text-slate-400 text-xs">
                    <p>© {new Date().getFullYear()} ETIM Pro. All rights reserved.</p>
                    <div className="mt-2 flex justify-center gap-4">
                        <Link href="/terms" className="hover:text-slate-600 underline underline-offset-4">Terms</Link>
                        <Link href="/privacy" className="hover:text-slate-600 underline underline-offset-4">Privacy</Link>
                    </div>
                </div>
            </motion.div>
        </div>
    );
}
