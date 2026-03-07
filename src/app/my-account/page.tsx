"use client";

import { useEffect, useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import {
    User,
    Mail,
    LogOut,
    LayoutDashboard,
    ShieldCheck,
    Package,
    ChevronRight,
    Key,
    FileText,
    Download,
    UserCircle,
    CheckCircle2,
    RefreshCw,
    Wallet,
    Clock,
    Check,
    Copy,
    X,
    Eye,
    EyeOff
} from "lucide-react";
import { Button } from "@/components/ui/button";

interface UserData {
    email: string;
    nicename: string;
    displayName: string;
    licenseKey: string;
    licenseStatus: string;
    planName: string;
    expireDate: string;
    plnAmount: string;
    balance: string;
}

type TabType = "license" | "subscriptions" | "invoices" | "downloads" | "profile";

const capitalize = (s: string) => {
    if (!s) return "";
    const p = s.toLowerCase();
    if (p.includes("distributor")) return "Distributor Plan";
    if (p.includes("manufactur")) return "Manufacturer Plan";
    if (p.includes("agency")) return "Agency Plan";
    return s.charAt(0).toUpperCase() + s.slice(1);
};

const getPrice = (plan: string | undefined) => {
    const p = (plan || "").toLowerCase();
    if (p.includes("manufactur")) return "29.00";
    if (p.includes("distributor")) return "79.00";
    if (p.includes("agency") || p.includes("enterprise")) return "199.00";
    return "0.00";
};

export default function MyAccountPage() {
    const [user, setUser] = useState<UserData | null>(null);
    const [loading, setLoading] = useState(true);
    const [activeTab, setActiveTab] = useState<TabType>("license");
    const [refreshing, setRefreshing] = useState(false);
    const [showKey, setShowKey] = useState(false);
    const [showReceiptModal, setShowReceiptModal] = useState(false);
    const [showDetailsModal, setShowDetailsModal] = useState(false);

    const loadUserData = () => {
        const userData: UserData = {
            email: localStorage.getItem("etim_user_email") || "",
            nicename: localStorage.getItem("etim_user_nicename") || "",
            displayName: localStorage.getItem("etim_user_display_name") || "",
            licenseKey: localStorage.getItem("etim_license_key") || "XXXX-XXXX-XXXX-XXXX",
            licenseStatus: localStorage.getItem("etim_license_status") || "unknown",
            planName: localStorage.getItem("etim_plan_name") || "No Active Plan",
            expireDate: localStorage.getItem("etim_expire_date") || "Never",
            plnAmount: localStorage.getItem("etim_pln_amount") || "0.00",
            balance: localStorage.getItem("etim_balance") || "0.00",
        };
        setUser(userData);
    };

    useEffect(() => {
        const token = localStorage.getItem("etim_token");
        if (!token) {
            window.location.href = "/login";
            return;
        }
        loadUserData();
        setLoading(false);
    }, []);

    const refreshData = async () => {
        if (!user?.email) return;
        setRefreshing(true);
        try {
            // Using the same URL logic as login
            const WORDPRESS_URL = "http://etim.test";
            const res = await fetch(`${WORDPRESS_URL}/index.php?rest_route=/etim/v1/license&email=${user.email}`);
            const result = await res.json();

            if (result.success && result.data) {
                const d = result.data;
                localStorage.setItem("etim_license_key", d.license_key || "");
                localStorage.setItem("etim_license_status", d.license_status || "");
                localStorage.setItem("etim_plan_name", d.plan_name || "");
                localStorage.setItem("etim_expire_date", d.expire_date || "");
                localStorage.setItem("etim_pln_amount", d.pln_amount || "0.00");
                localStorage.setItem("etim_balance", d.balance || "0.00");
                loadUserData();
            }
        } catch (err) {
            console.error("Failed to refresh data", err);
        } finally {
            setRefreshing(false);
        }
    };

    const handleLogout = () => {
        localStorage.clear();
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
        const planPrice = getPrice(user?.planName);

        switch (activeTab) {
            case "license":
                return (
                    <motion.div initial={{ opacity: 0, scale: 0.98 }} animate={{ opacity: 1, scale: 1 }} className="space-y-6">
                        <h2 className="text-3xl font-black text-slate-900 mb-8 pl-1">License</h2>

                        {/* Masked Key Box */}
                        <div className="bg-white p-5 rounded-3xl border border-blue-100 shadow-sm flex items-center justify-between group hover:border-blue-400 transition-all">
                            <code className="text-blue-600 font-black tracking-[0.3em] overflow-hidden truncate max-w-[80%]">
                                {showKey ? user?.licenseKey : `****************${user?.licenseKey.slice(-4) || "XXXX"}`}
                            </code>
                            <button
                                onClick={() => setShowKey(!showKey)}
                                className="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 shadow-sm hover:bg-blue-600 hover:text-white transition-all"
                            >
                                {showKey ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                            </button>
                        </div>

                        {/* Plan Details Card */}
                        <div className="bg-white p-10 rounded-[2.5rem] border-2 border-emerald-500 shadow-xl relative overflow-hidden group">
                            <div className="flex flex-col md:flex-row justify-between items-start gap-8">
                                <div className="space-y-6 flex-1">
                                    <div className="flex flex-wrap items-center gap-4">
                                        <div className="flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-600 rounded-full border border-emerald-100 shadow-sm">
                                            <div className="w-5 h-5 rounded-full bg-emerald-500 flex items-center justify-center">
                                                <Check className="w-3 h-3 text-white" />
                                            </div>
                                            <span className="text-[10px] font-black uppercase tracking-widest">Status: {user?.licenseStatus}</span>
                                        </div>
                                        <span className="text-xs font-bold text-slate-400">{capitalize(user?.planName || "")} (Annual)</span>
                                    </div>

                                    <div className="space-y-2">
                                        <div className="w-16 h-16 rounded-xl bg-blue-50 flex items-center justify-center mb-6">
                                            <Package className="w-10 h-10 text-blue-600" />
                                        </div>
                                        <h3 className="text-2xl font-black text-slate-900">{capitalize(user?.planName || "")}</h3>
                                        <div className="flex items-baseline gap-1">
                                            <span className="text-4xl font-black text-blue-600">${planPrice}</span>
                                            <span className="text-sm font-bold text-slate-400">per year (Annual)</span>
                                        </div>
                                    </div>
                                </div>

                                <div className="space-y-6 flex-1 w-full md:w-auto md:text-right">
                                    <div className="md:text-right mb-8">
                                        <p className="text-xs font-bold text-slate-400 mb-1">Trial Plan / Live Plan Valid Until</p>
                                        <p className="text-lg font-black text-emerald-600">{user?.expireDate}</p>
                                    </div>
                                    <div className="space-y-4 md:flex md:flex-col md:items-end">
                                        <FeatureItem text="Unlimited Classifications" />
                                        <FeatureItem text="Unlimited Domain Sync" />
                                        <FeatureItem text="Priority Live Support" />
                                    </div>
                                </div>
                            </div>
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
                                    <span className={`px-3 py-1 text-[10px] font-black uppercase rounded-full tracking-widest ${user?.licenseStatus === 'active' ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600'}`}>
                                        {user?.licenseStatus || 'No Plan'}
                                    </span>
                                    <h3 className="text-4xl font-black text-slate-900 mt-2">{capitalize(user?.planName || "")}</h3>
                                    <p className="text-slate-500 mt-1">Classification limits and API access for your WooCommerce store.</p>
                                </div>
                                <div className="text-center md:text-right">
                                    <p className="text-4xl font-black text-blue-600">${planPrice}<span className="text-sm font-medium text-slate-400">/total</span></p>
                                    <p className="text-xs text-slate-400 mt-1">Expires: {user?.expireDate || 'N/A'}</p>
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
                        <h2 className="text-3xl font-black text-slate-900 px-2 pb-4">Invoices</h2>
                        <div className="bg-white rounded-[2rem] border border-slate-100 overflow-hidden shadow-sm">
                            <div className="grid grid-cols-4 p-6 border-b border-slate-50 bg-slate-50/50 text-[10px] font-black uppercase tracking-widest text-slate-400">
                                <div>ID</div>
                                <div>Date</div>
                                <div>Amount</div>
                                <div className="text-center">Actions</div>
                            </div>
                            <div className="divide-y divide-slate-50">
                                <InvoiceItem
                                    id="#523"
                                    date="February 27, 2026"
                                    amount={planPrice}
                                    onShowReceipt={() => setShowReceiptModal(true)}
                                    onShowDetails={() => setShowDetailsModal(true)}
                                />
                            </div>
                        </div>
                    </motion.div>
                );
            case "downloads":
                return (
                    <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} className="space-y-6">
                        <h2 className="text-3xl font-black text-slate-900 px-2">Downloads</h2>
                        <div className="bg-white p-12 rounded-[3rem] border-2 border-slate-100 shadow-xl overflow-hidden relative group">
                            <Download className="absolute -bottom-10 -right-10 w-64 h-64 text-slate-50 group-hover:text-blue-50 transition-all opacity-50" />
                            <div className="relative z-10">
                                <div className="w-20 h-20 rounded-3xl bg-blue-600 flex items-center justify-center text-white shadow-2xl shadow-blue-600/20 mb-10">
                                    <Package className="w-10 h-10" />
                                </div>
                                <h3 className="text-3xl font-black text-slate-900 mb-4">ETIM Pro Enterprise Kit</h3>
                                <p className="text-slate-500 font-bold mb-10 max-w-md">Download the full suite including core source code, bulk classification tools, and the latest taxonomy datasets. All in one single package.</p>

                                <div className="grid sm:grid-cols-3 gap-6 mb-12">
                                    <div className="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                        <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Version</p>
                                        <p className="font-bold text-slate-900">v2.4.0 Final</p>
                                    </div>
                                    <div className="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                        <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">File Size</p>
                                        <p className="font-bold text-slate-900">12.5 MB</p>
                                    </div>
                                    <div className="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                        <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Format</p>
                                        <p className="font-bold text-slate-900">ZIP / PDF</p>
                                    </div>
                                </div>

                                <Button className="w-full bg-slate-900 hover:bg-black text-white rounded-2xl font-black py-8 shadow-2xl shadow-slate-900/20 text-lg transition-all active:scale-[0.98]">
                                    Download Full Bundle (.zip)
                                </Button>
                            </div>
                        </div>
                    </motion.div>
                );
            case "profile":
                return (
                    <motion.div
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        className="space-y-8"
                    >
                        <section>
                            <h2 className="text-3xl font-black text-slate-900 mb-8">Account Identity</h2>
                            <div className="bg-white p-10 rounded-[2.5rem] border border-slate-100 shadow-sm">
                                <div className="flex flex-col md:flex-row items-center gap-10 mb-10">
                                    <div className="w-24 h-24 rounded-full bg-blue-600 flex items-center justify-center text-white text-3xl font-black shadow-lg shadow-blue-600/20">
                                        {user?.displayName.charAt(0)}
                                    </div>
                                    <div className="text-center md:text-left">
                                        <h3 className="text-2xl font-black text-slate-900 mb-2">{user?.displayName}</h3>
                                        <p className="text-slate-500 font-bold">Member ID: #449210</p>
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-8 border-t border-slate-50 pt-10 mb-10">
                                    <ProfileItem label="Username" value={user?.nicename} />
                                    <ProfileItem label="Email Address" value={user?.email} />
                                    <ProfileItem label="Account Status" value="Verified Provider" />
                                    <ProfileItem label="Region" value="Sweden" />
                                </div>

                                <Button className="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-black py-8 shadow-xl shadow-blue-600/20 text-lg group">
                                    Manage WP Profile
                                    <ChevronRight className="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" />
                                </Button>
                            </div>
                        </section>
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
                                        {capitalize(user?.planName || "")}
                                    </span>
                                </div>
                                <div className="flex flex-wrap items-center justify-center md:justify-start gap-4 text-slate-500">
                                    <div className="flex items-center gap-1.5">
                                        <Mail className="w-4 h-4 text-slate-400" />
                                        <span className="text-sm">{user?.email}</span>
                                    </div>
                                    <div className="flex items-center gap-1.5 font-bold">
                                        <ShieldCheck className="w-4 h-4 text-emerald-500" />
                                        <span className="text-sm">Status: {user?.licenseStatus}</span>
                                    </div>
                                    <div className="hidden sm:flex items-center gap-1.5 font-bold text-blue-500">
                                        <Clock className="w-4 h-4" />
                                        <span className="text-sm">Expires: {user?.expireDate}</span>
                                    </div>
                                </div>
                            </motion.div>
                        </div>

                        <div className="flex gap-3">
                            <Button
                                variant="outline"
                                onClick={refreshData}
                                disabled={refreshing}
                                className="rounded-xl bg-blue-50 text-blue-600 border border-blue-100 hover:bg-blue-600 hover:text-white font-bold transition-all"
                            >
                                {refreshing ? "Refreshing..." : "Refresh Status"}
                                <RefreshCw className={`ml-2 w-4 h-4 ${refreshing ? "animate-spin" : ""}`} />
                            </Button>
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
                            <div key={activeTab}>
                                {renderContent()}
                            </div>
                        </AnimatePresence>
                    </div>
                </div>
            </div>

            {/* Modals */}
            <LicenseDetailsModal isOpen={showDetailsModal} onClose={() => setShowDetailsModal(false)} user={user} />
            <ReceiptModal isOpen={showReceiptModal} onClose={() => setShowReceiptModal(false)} user={user} />
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



function InvoiceItem({ id, date, amount, onShowReceipt, onShowDetails }: { id: string, date: string, amount: string, onShowReceipt: () => void, onShowDetails: () => void }) {
    return (
        <div className="grid grid-cols-4 items-center p-6 hover:bg-slate-50 transition-all group">
            <div className="font-bold text-slate-900">{id}</div>
            <div className="text-sm font-bold text-slate-500">{date}</div>
            <div className="font-black text-slate-900">${amount}</div>
            <div className="flex items-center justify-center gap-3">
                <button
                    onClick={onShowReceipt}
                    className="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 hover:bg-blue-600 hover:text-white transition-all shadow-sm"
                >
                    <FileText className="w-4 h-4" />
                </button>
                <button
                    onClick={onShowDetails}
                    className="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm"
                >
                    <Key className="w-4 h-4" />
                </button>
            </div>
        </div>
    );
}



function ProfileItem({ label, value }: { label: string, value: string | undefined }) {
    return (
        <div className="space-y-2">
            <span className="text-[10px] font-black text-slate-400 uppercase tracking-widest block pl-1">{label}</span>
            <div className="p-4 bg-slate-50 border border-slate-100 rounded-2xl text-slate-900 font-bold overflow-hidden truncate">
                {value || "Not specified"}
            </div>
        </div>
    );
}

function FeatureItem({ text }: { text: string }) {
    return (
        <div className="flex items-center gap-3">
            <div className="w-5 h-5 rounded-full bg-blue-100 flex items-center justify-center">
                <CheckCircle2 className="w-3 h-3 text-blue-600" />
            </div>
            <span className="text-sm font-bold text-slate-700">{text}</span>
        </div>
    );
}

function LicenseDetailsModal({ isOpen, onClose, user }: { isOpen: boolean, onClose: () => void, user: UserData | null }) {
    if (!isOpen) return null;
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <motion.div initial={{ opacity: 0, scale: 0.9 }} animate={{ opacity: 1, scale: 1 }} className="bg-white rounded-[2.5rem] w-full max-w-xl overflow-hidden shadow-2xl">
                <div className="bg-blue-600 p-8 text-white flex justify-between items-center">
                    <div className="flex items-center gap-4">
                        <div className="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center backdrop-blur-md">
                            <Key className="w-6 h-6" />
                        </div>
                        <div>
                            <h3 className="text-xl font-black">License Key Details</h3>
                            <p className="text-blue-100 text-xs font-bold">Your purchase information</p>
                        </div>
                    </div>
                    <button onClick={onClose} className="p-2 hover:bg-white/10 rounded-xl transition-colors"><X className="w-6 h-6" /></button>
                </div>
                <div className="p-10 space-y-8">
                    <div className="grid grid-cols-2 gap-8">
                        <div>
                            <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Account Email</p>
                            <p className="font-bold text-slate-900 break-all">{user?.email}</p>
                        </div>
                        <div>
                            <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Invoice ID</p>
                            <p className="font-bold text-slate-900">#523</p>
                        </div>
                        <div>
                            <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Purchase Date</p>
                            <p className="font-bold text-slate-900">February 27, 2026</p>
                        </div>
                        <div>
                            <p className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Amount Paid</p>
                            <p className="font-bold text-slate-900">${getPrice(user?.planName)}</p>
                        </div>
                    </div>

                    <div className="bg-slate-50 p-6 rounded-3xl border border-slate-100">
                        <div className="flex justify-between items-center mb-4">
                            <p className="text-lg font-black text-slate-900">Your License Key</p>
                            <span className="bg-emerald-500 text-white text-[10px] font-black px-3 py-1 rounded-full uppercase">Important</span>
                        </div>
                        <div className="flex gap-4">
                            <div className="flex-1 bg-white p-4 rounded-2xl border border-slate-200 font-mono font-bold text-slate-900 text-sm overflow-hidden truncate">
                                {user?.licenseKey}
                            </div>
                            <button className="p-4 bg-white border border-slate-200 rounded-2xl hover:bg-slate-900 hover:text-white transition-all shadow-sm">
                                <Copy className="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    <div className="bg-blue-50 p-6 rounded-3xl border border-blue-100">
                        <p className="font-black text-blue-900 text-sm mb-4">How to use this license key:</p>
                        <ul className="space-y-3 text-sm font-bold text-blue-700">
                            {[
                                "Go to WordPress admin → ETIM PRO → Settings → License",
                                "Paste this key in the license field",
                                "Click \"Activate License\" button",
                                "Enjoy all Standard / Pro features immediately"
                            ].map((step, i) => (
                                <li key={i} className="flex gap-3">
                                    <span className="w-1.5 h-1.5 rounded-full bg-blue-400 mt-2 shrink-0" />
                                    {step}
                                </li>
                            ))}
                        </ul>
                    </div>

                    <Button onClick={onClose} className="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-black py-8 shadow-xl shadow-blue-600/20 text-lg">
                        Done
                    </Button>
                </div>
            </motion.div>
        </div>
    );
}

function ReceiptModal({ isOpen, onClose, user }: { isOpen: boolean, onClose: () => void, user: UserData | null }) {
    if (!isOpen) return null;
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
            <motion.div initial={{ opacity: 0, scale: 0.9 }} animate={{ opacity: 1, scale: 1 }} className="bg-white rounded-[2.5rem] w-full max-w-md overflow-hidden shadow-2xl relative">
                <button onClick={onClose} className="absolute top-4 right-4 p-2 hover:bg-slate-100 rounded-xl text-slate-400 transition-colors z-10"><X className="w-5 h-5" /></button>
                <div className="p-8">
                    <div className="flex flex-col items-center text-center mb-6">
                        <div className="w-16 h-16 rounded-full bg-emerald-500 flex items-center justify-center text-white mb-4 shadow-xl shadow-emerald-500/20">
                            <Check className="w-8 h-8" strokeWidth={3} />
                        </div>
                        <h3 className="text-2xl font-black text-slate-900">Thank You!</h3>
                        <p className="text-slate-500 font-bold text-sm mt-1">Your payment has been successfully processed</p>
                    </div>

                    <div className="flex justify-between items-start mb-6 gap-6 border-b border-slate-50 pb-6">
                        <div>
                            <h4 className="text-blue-600 font-black text-xs uppercase tracking-wider">Thingsatweb Sweden AB</h4>
                            <p className="text-slate-400 text-[9px] font-bold leading-relaxed mt-0.5">Sockerbruksgatan 7, 53140 Lidköping<br />support-360@thingsatweb.com</p>
                        </div>
                        <div className="text-right">
                            <h4 className="font-black text-slate-900 text-sm">ETIM <span className="text-blue-600">PRO</span></h4>
                        </div>
                    </div>

                    <div className="flex justify-between items-center py-3 border-b border-slate-50 mb-6">
                        <p className="font-black text-emerald-500 tracking-tighter text-sm">ORD-2026-0523</p>
                        <p className="font-bold text-slate-400 text-xs">Feb 27, 2026</p>
                    </div>

                    <div className="space-y-4 mb-8">
                        <div className="flex justify-between items-center bg-slate-50/50 p-3 rounded-xl border border-dotted border-slate-200">
                            <p className="text-slate-400 font-bold uppercase tracking-widest text-[9px]">Name :</p>
                            <p className="font-black text-slate-900 text-xs uppercase tracking-tight">{user?.displayName}</p>
                        </div>
                        <div className="flex justify-between items-center bg-slate-50/50 p-3 rounded-xl border border-dotted border-slate-200">
                            <p className="text-slate-400 font-bold uppercase tracking-widest text-[9px]">Email :</p>
                            <p className="font-black text-slate-900 text-xs tracking-tight">{user?.email}</p>
                        </div>
                    </div>

                    <div className="bg-blue-600 rounded-3xl p-8 text-white shadow-xl shadow-blue-600/20">
                        <div className="flex justify-between items-start mb-6">
                            <div>
                                <p className="text-lg font-black">Subscription</p>
                                <p className="text-blue-200 text-[10px] font-bold uppercase tracking-widest mt-0.5">Yearly</p>
                            </div>
                            <div className="text-right">
                                <p className="text-lg font-black">{capitalize(user?.planName || "")}</p>
                                <p className="text-blue-200 text-[10px] font-bold uppercase tracking-widest mt-0.5">Yearly</p>
                            </div>
                        </div>
                        <div className="space-y-3 border-t border-white/10 pt-6">
                            <div className="flex justify-between opacity-80 text-sm font-bold">
                                <span>Price</span>
                                <span>${getPrice(user?.planName)}</span>
                            </div>
                            <div className="flex justify-between opacity-80 text-sm font-bold">
                                <span>GST (0%)</span>
                                <span>$0.00</span>
                            </div>
                            <div className="flex justify-between text-xl font-black pt-2">
                                <span>Total</span>
                                <span>${getPrice(user?.planName)}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div className="p-8 pt-0">
                    <div className="bg-emerald-50 p-2.5 rounded-xl text-center mb-6">
                        <p className="text-[8px] font-black text-emerald-700 uppercase tracking-[0.2em]">Your payment has been securely processed via Razorpay over<br />SSL-encrypted connection.</p>
                    </div>
                    <div className="grid grid-cols-2 gap-3">
                        <Button className="bg-slate-900 hover:bg-black text-white rounded-xl font-black py-5 text-xs shadow-lg shadow-slate-200">
                            Download PDF
                        </Button>
                        <Button onClick={onClose} className="bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black py-5 text-xs shadow-lg shadow-blue-200">
                            Close
                        </Button>
                    </div>
                </div>
            </motion.div>
        </div>
    );
}
