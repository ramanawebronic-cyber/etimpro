"use client";

import { useEffect, useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import {
    User,
    Mail,
    LogOut,
    LayoutDashboard,
    Settings,
    Bell,
    ShieldCheck,
    Package,
    CreditCard,
    ChevronRight,
    ExternalLink,
    Key,
    FileText,
    Download,
    UserCircle,
    Clock,
    CheckCircle2,
    Calendar,
    ArrowUpRight
} from "lucide-react";
import { Button } from "@/components/ui/button";

interface UserData {
    email: string;
    nicename: string;
    displayName: string;
}

type TabType = "license" | "subscriptions" | "invoices" | "downloads" | "profile";

export default function MyAccountPage() {
    const [user, setUser] = useState<UserData | null>(null);
    const [loading, setLoading] = useState(true);
    const [activeTab, setActiveTab] = useState<TabType>("license");

    useEffect(() => {
        const token = localStorage.getItem("etim_token");
        if (!token) {
            window.location.href = "/login";
            return;
        }

        const userData = {
            email: localStorage.getItem("etim_user_email") || "",
            nicename: localStorage.getItem("etim_user_nicename") || "",
            displayName: localStorage.getItem("etim_user_display_name") || "",
        };

        setUser(userData);
        setLoading(false);
    }, []);

    const handleLogout = () => {
        localStorage.removeItem("etim_token");
        localStorage.removeItem("etim_user_email");
        localStorage.removeItem("etim_user_nicename");
        localStorage.removeItem("etim_user_display_name");
        window.location.href = "/login";
    };

    if (loading) {
        return (
            <div className="min-h-screen flex items-center justify-center bg-slate-50">
                <div className="flex flex-col items-center gap-4">
                    <div className="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin" />
                    <p className="text-slate-500 font-medium">Loading your profile...</p>
                </div>
            </div>
        );
    }

    const renderContent = () => {
        switch (activeTab) {
            case "license":
                return (
                    <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} className="space-y-6">
                        <h2 className="text-2xl font-bold text-slate-900 px-2">License Keys</h2>
                        <div className="grid gap-4">
                            <LicenseCard keyString="ETIM-PRO-3928-1122" domain="etim.test" status="active" />
                            <LicenseCard keyString="ETIM-DEV-4400-8811" domain="dev.etim.pro" status="active" />
                            <LicenseCard keyString="ETIM-OLD-1100-2200" domain="test.etim.local" status="expired" />
                        </div>
                    </motion.div>
                );
            case "subscriptions":
                return (
                    <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} className="space-y-6">
                        <h2 className="text-2xl font-bold text-slate-900 px-2">Your Membership</h2>
                        <div className="bg-white rounded-[2rem] border border-slate-200 p-8 shadow-sm">
                            <div className="flex flex-col md:flex-row items-center justify-between gap-6 pb-8 border-b border-slate-100">
                                <div>
                                    <span className="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black uppercase rounded-full tracking-widest">Enterprise Plan</span>
                                    <h3 className="text-4xl font-black text-slate-900 mt-2">Professional Pro</h3>
                                    <p className="text-slate-500 mt-1">Full access to ETIM 9.0 automated classification toolset.</p>
                                </div>
                                <div className="text-center md:text-right">
                                    <p className="text-4xl font-black text-blue-600">$49<span className="text-sm font-medium text-slate-400">/mo</span></p>
                                    <p className="text-xs text-slate-400 mt-1">Next bill: Oct 12, 2026</p>
                                </div>
                            </div>
                            <div className="grid sm:grid-cols-2 gap-4 mt-8">
                                <div className="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl">
                                    <div className="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm">
                                        <CheckCircle2 className="w-4 h-4 text-emerald-500" />
                                    </div>
                                    <span className="text-sm font-bold text-slate-700">Unlimited API Requests</span>
                                </div>
                                <div className="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl">
                                    <div className="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm">
                                        <CheckCircle2 className="w-4 h-4 text-emerald-500" />
                                    </div>
                                    <span className="text-sm font-bold text-slate-700">Priority Support Support</span>
                                </div>
                                <div className="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl">
                                    <div className="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm">
                                        <CheckCircle2 className="w-4 h-4 text-emerald-500" />
                                    </div>
                                    <span className="text-sm font-bold text-slate-700">Automated Data Sync</span>
                                </div>
                                <div className="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl">
                                    <div className="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm">
                                        <CheckCircle2 className="w-4 h-4 text-emerald-500" />
                                    </div>
                                    <span className="text-sm font-bold text-slate-700">Custom Taxonomy Maps</span>
                                </div>
                            </div>
                        </div>
                    </motion.div>
                );
            case "invoices":
                return (
                    <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} className="space-y-6">
                        <h2 className="text-2xl font-bold text-slate-900 px-2">Billing History</h2>
                        <div className="bg-white rounded-[2rem] border border-slate-200 overflow-hidden shadow-sm">
                            <div className="p-4 space-y-2">
                                <InvoiceItem id="EB-8273" date="Oct 12, 2026" amount="49.00" status="Paid" />
                                <InvoiceItem id="EB-8272" date="Sep 12, 2026" amount="49.00" status="Paid" />
                                <InvoiceItem id="EB-8271" date="Aug 12, 2026" amount="49.00" status="Paid" />
                                <InvoiceItem id="EB-8270" date="Jul 12, 2026" amount="49.00" status="Paid" />
                                <InvoiceItem id="EB-8269" date="Jun 12, 2026" amount="49.00" status="Paid" />
                            </div>
                        </div>
                    </motion.div>
                );
            case "downloads":
                return (
                    <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} className="space-y-6">
                        <h2 className="text-2xl font-bold text-slate-900 px-2">Available Downloads</h2>
                        <div className="grid sm:grid-cols-2 gap-4">
                            <DownloadCard title="ETIM Pro Core Source" version="2.4.0" size="1.2 MB" />
                            <DownloadCard title="Bulk Classification Tool" version="1.1.2" size="450 KB" />
                            <DownloadCard title="ETIM Taxonomy Dataset" version="v9.0 Stable" size="8.9 MB" />
                            <DownloadCard title="API Integration SDK" version="1.0.1" size="210 KB" />
                        </div>
                    </motion.div>
                );
            case "profile":
                return (
                    <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} className="space-y-6">
                        <h2 className="text-2xl font-bold text-slate-900 px-2">Your Profile Identity</h2>
                        <div className="bg-white rounded-[2.5rem] border border-slate-200 p-10 shadow-sm space-y-10">
                            <div className="flex flex-col md:flex-row items-center gap-8">
                                <div className="w-24 h-24 rounded-full bg-slate-100 border-4 border-white shadow-xl flex items-center justify-center text-slate-400 overflow-hidden">
                                    <User className="w-12 h-12" />
                                </div>
                                <div className="text-center md:text-left">
                                    <h3 className="text-2xl font-bold text-slate-900">{user?.displayName}</h3>
                                    <p className="text-slate-500 font-medium">Verified Enterprise Member</p>
                                </div>
                                <Button className="md:ml-auto bg-slate-900 text-white rounded-xl font-bold px-8 shadow-lg shadow-slate-900/10" asChild>
                                    <a href="http://etim.test/wp-admin/profile.php" target="_blank" rel="noopener noreferrer">Edit Wordpress Profile</a>
                                </Button>
                            </div>

                            <div className="grid md:grid-cols-2 gap-8 pt-4">
                                <div className="space-y-2">
                                    <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Primary Email Address</label>
                                    <div className="w-full p-5 bg-slate-50 border border-slate-100 rounded-2xl text-slate-800 font-bold overflow-hidden truncate">{user?.email}</div>
                                </div>
                                <div className="space-y-2">
                                    <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-1">Public Display Name</label>
                                    <div className="w-full p-5 bg-slate-50 border border-slate-100 rounded-2xl text-slate-800 font-bold">{user?.displayName}</div>
                                </div>
                            </div>
                        </div>
                    </motion.div>
                );
            default:
                return null;
        }
    };

    return (
        <div className="min-h-screen bg-slate-50/50 pb-20">
            {/* Header Profile Section */}
            <div className="bg-white border-b border-slate-200 pt-12 pb-24">
                <div className="container mx-auto max-w-6xl px-4">
                    <div className="flex flex-col md:flex-row items-center gap-8">
                        <motion.div
                            initial={{ opacity: 0, scale: 0.9 }}
                            animate={{ opacity: 1, scale: 1 }}
                            className="relative"
                        >
                            <div className="w-32 h-32 rounded-3xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white shadow-2xl shadow-blue-500/20 ring-4 ring-white">
                                <User className="w-16 h-16" />
                            </div>
                            <div className="absolute -bottom-2 -right-2 bg-emerald-500 border-4 border-white w-8 h-8 rounded-full" />
                        </motion.div>

                        <div className="text-center md:text-left flex-1">
                            <motion.div
                                initial={{ opacity: 0, y: 10 }}
                                animate={{ opacity: 1, y: 0 }}
                            >
                                <div className="flex flex-wrap items-center justify-center md:justify-start gap-3 mb-2">
                                    <h1 className="text-3xl font-bold text-slate-900">{user?.displayName}</h1>
                                    <span className="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full border border-blue-100 uppercase tracking-wider">
                                        Active Subscriber
                                    </span>
                                </div>
                                <div className="flex items-center justify-center md:justify-start gap-4 text-slate-500">
                                    <div className="flex items-center gap-1.5">
                                        <Mail className="w-4 h-4 text-slate-400" />
                                        <span className="text-sm">{user?.email}</span>
                                    </div>
                                    <div className="hidden sm:flex items-center gap-1.5 font-bold">
                                        <ShieldCheck className="w-4 h-4 text-emerald-500" />
                                        <span className="text-sm">99% Accuracy Rate</span>
                                    </div>
                                </div>
                            </motion.div>
                        </div>

                        <div className="flex gap-3">
                            <Button
                                variant="outline"
                                onClick={handleLogout}
                                className="rounded-xl bg-red-50 text-red-600 border border-red-100 hover:bg-red-600 hover:text-white font-bold transition-all"
                            >
                                Sign Out <LogOut className="ml-2 w-4 h-4" />
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            {/* Main Content Sections */}
            <div className="container mx-auto max-w-6xl px-4 -mt-12">
                <div className="grid lg:grid-cols-12 gap-8">

                    {/* Sidebar Navigation */}
                    <div className="lg:col-span-4 space-y-6">
                        <div className="bg-white rounded-3xl border border-slate-200 p-4 shadow-sm">
                            <nav className="space-y-1">
                                <NavItem icon={<Key className="w-5 h-5" />} label="License" active={activeTab === "license"} onClick={() => setActiveTab("license")} />
                                <NavItem icon={<Package className="w-5 h-5" />} label="Subscriptions" active={activeTab === "subscriptions"} onClick={() => setActiveTab("subscriptions")} />
                                <NavItem icon={<FileText className="w-5 h-5" />} label="Invoices" active={activeTab === "invoices"} onClick={() => setActiveTab("invoices")} />
                                <NavItem icon={<Download className="w-5 h-5" />} label="Downloads" active={activeTab === "downloads"} onClick={() => setActiveTab("downloads")} />
                                <NavItem icon={<UserCircle className="w-5 h-5" />} label="Profile" active={activeTab === "profile"} onClick={() => setActiveTab("profile")} />
                            </nav>
                        </div>

                        <div className="bg-[#0f172a] rounded-3xl p-8 text-white relative overflow-hidden group shadow-2xl shadow-slate-900/10">
                            <div className="absolute top-0 right-0 p-10 opacity-10 group-hover:scale-110 transition-transform">
                                <LayoutDashboard className="w-32 h-32 rotate-12" />
                            </div>
                            <h3 className="text-xl font-bold mb-2 relative z-10">Exclusive Access</h3>
                            <p className="text-slate-400 text-sm mb-6 relative z-10">Our classification experts are standing by to assist with your taxonomy file sync.</p>
                            <Button className="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl h-12 shadow-lg shadow-blue-600/20 active:scale-95 transition-all">
                                Connect with an expert
                            </Button>
                        </div>
                    </div>

                    {/* Main Content Area */}
                    <div className="lg:col-span-8">
                        <AnimatePresence mode="wait">
                            {renderContent()}
                        </AnimatePresence>
                    </div>
                </div>
            </div>
        </div>
    );
}

function NavItem({ icon, label, active = false, onClick }: { icon: React.ReactNode, label: string, active?: boolean, onClick?: () => void }) {
    return (
        <button
            onClick={onClick}
            className={`w-full flex items-center justify-between p-4 rounded-2xl transition-all group ${active ? "bg-blue-600 text-white shadow-xl shadow-blue-600/20 -translate-y-0.5" : "text-slate-600 hover:bg-slate-50"
                }`}>
            <div className="flex items-center gap-3">
                <span className={`${active ? "text-white" : "text-slate-400 group-hover:text-blue-600"} transition-colors`}>{icon}</span>
                <span className="font-bold text-sm tracking-tight">{label}</span>
            </div>
            <ChevronRight className={`w-4 h-4 transition-transform ${active ? "text-white/70" : "text-slate-300 group-hover:translate-x-1"}`} />
        </button>
    );
}

function LicenseCard({ keyString, domain, status }: { keyString: string, domain: string, status: 'active' | 'expired' }) {
    return (
        <div className={`p-6 bg-white border border-slate-100 rounded-3xl shadow-sm flex flex-col sm:flex-row items-center justify-between gap-6 hover:shadow-lg hover:border-blue-100 transition-all ${status === "expired" ? "opacity-60" : ""}`}>
            <div className="flex items-center gap-5">
                <div className={`w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg ${status === "active" ? "bg-blue-50 text-blue-600" : "bg-slate-100 text-slate-400"}`}>
                    <Key className="w-6 h-6" />
                </div>
                <div>
                    <div className="flex items-center gap-2 mb-1">
                        <span className={`text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded ${status === "active" ? "bg-emerald-100 text-emerald-800" : "bg-red-50 text-red-600"}`}>
                            {status}
                        </span>
                        <span className="text-xs font-bold text-slate-400">Enterprise Plus</span>
                    </div>
                    <code className="text-xl font-black text-slate-900 tracking-tight">{keyString}</code>
                    <p className="text-xs text-slate-400 mt-0.5">Primary Domain: <span className="text-blue-600 font-bold">{domain}</span></p>
                </div>
            </div>
            <Button variant="outline" className={`rounded-xl px-10 font-bold h-12 transition-all ${status === "active" ? "hover:bg-red-50 hover:text-red-500 hover:border-red-100" : "hover:bg-blue-50 hover:text-blue-600 hover:border-blue-100"}`}>
                {status === "active" ? "Deactivate" : "Renew Plan"}
            </Button>
        </div>
    );
}

function InvoiceItem({ id, date, amount, status }: { id: string, date: string, amount: string, status: string }) {
    return (
        <div className="flex items-center justify-between p-5 hover:bg-slate-50 transition-all rounded-2xl group">
            <div className="flex items-center gap-4">
                <div className="w-12 h-12 rounded-xl bg-white border border-slate-100 flex items-center justify-center text-slate-400 group-hover:text-blue-600 transition-colors shadow-sm">
                    <FileText className="w-5 h-5" />
                </div>
                <div>
                    <h4 className="font-bold text-slate-900">Subscription Invoice {id}</h4>
                    <p className="text-xs text-slate-400 font-medium">Auto-renewed on {date}</p>
                </div>
            </div>
            <div className="flex items-center gap-10">
                <div className="text-right">
                    <p className="text-lg font-black text-slate-900">${amount}</p>
                    <span className="text-[9px] font-black text-emerald-500 uppercase tracking-widest px-2 py-0.5 bg-emerald-50 rounded">{status}</span>
                </div>
                <button className="p-3 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                    <Download className="w-4 h-4" />
                </button>
            </div>
        </div>
    );
}

function DownloadCard({ title, version, size }: { title: string, version: string, size: string }) {
    return (
        <div className="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all group overflow-hidden relative">
            <Download className="absolute -bottom-4 -right-4 w-24 h-24 text-slate-50 group-hover:text-blue-50 transition-colors" />
            <div className="flex justify-between items-start mb-6">
                <div className="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-sm group-hover:bg-blue-600 group-hover:text-white transition-all">
                    <Download className="w-5 h-5" />
                </div>
                <div className="text-right">
                    <p className="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded inline-block">v{version}</p>
                    <p className="text-[10px] text-slate-400 font-black uppercase tracking-widest mt-1.5">{size}</p>
                </div>
            </div>
            <h4 className="text-xl font-bold text-slate-900 mb-6">{title}</h4>
            <Button className="w-full bg-slate-900 hover:bg-black text-white rounded-xl font-bold py-6 shadow-lg shadow-slate-900/10">
                Download zip
            </Button>
        </div>
    );
}
