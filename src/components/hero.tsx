import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { ArrowRight, CheckCircle2, Sparkles, Zap, BarChart3 } from "lucide-react";
import Link from "next/link";

export function HeroSection() {
    return (
        <section className="relative overflow-hidden w-full pt-24 md:pt-32 lg:pt-40 pb-16 md:pb-24">
            {/* Animated grid background */}
            <div className="absolute inset-0 bg-[linear-gradient(to_right,#3b82f608_1px,transparent_1px),linear-gradient(to_bottom,#3b82f608_1px,transparent_1px)] bg-[size:24px_24px] [mask-image:radial-gradient(ellipse_80%_60%_at_50%_0%,#000_70%,transparent_100%)]"></div>
            {/* Blue gradient glow */}
            <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-blue-500/10 blur-[120px] rounded-full pointer-events-none"></div>
            {/* Secondary glow */}
            <div className="absolute top-40 right-0 w-[400px] h-[300px] bg-indigo-400/8 blur-[100px] rounded-full pointer-events-none"></div>
            <div className="absolute top-60 left-0 w-[300px] h-[200px] bg-cyan-400/8 blur-[80px] rounded-full pointer-events-none"></div>

            <div className="container mx-auto relative z-10 px-4 md:px-6">
                <div className="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                    {/* Left Content */}
                    <div className="flex flex-col items-center lg:items-start text-center lg:text-left">
                        <Badge variant="outline" className="mb-6 rounded-full px-4 py-1.5 bg-blue-50 border-blue-200 text-blue-700">
                            <span className="flex h-2 w-2 rounded-full bg-blue-500 mr-2 animate-pulse"></span>
                            ETIM Pro for WooCommerce is now live
                        </Badge>

                        <h1 className="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight mb-6 text-slate-900 leading-[1.1]">
                            Master Product Classification with{" "}
                            <span className="bg-gradient-to-r from-blue-600 via-blue-500 to-indigo-500 text-transparent bg-clip-text">ETIM Pro</span>
                        </h1>

                        <p className="text-lg md:text-xl text-slate-500 mb-8 max-w-[540px] leading-relaxed">
                            The ultimate WooCommerce plugin for assigning ETIM groups, classes, and features. Import CSV/XML data, generate features dynamically, and support 17 languages seamlessly.
                        </p>

                        <div className="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                            <Button size="lg" asChild className="h-14 px-8 text-base shadow-lg shadow-blue-500/20 hover:shadow-blue-500/30 transition-all rounded-full bg-blue-600 hover:bg-blue-700 text-white">
                                <Link href="/pricing">
                                    Get Started
                                    <ArrowRight className="ml-2 h-5 w-5" />
                                </Link>
                            </Button>
                            <Button size="lg" variant="outline" asChild className="h-14 px-8 text-base bg-white border-slate-200 text-slate-700 hover:bg-slate-50 rounded-full">
                                <Link href="/docs">
                                    Read Documentation
                                </Link>
                            </Button>
                        </div>

                        <div className="mt-10 flex flex-wrap items-center justify-center lg:justify-start gap-6 text-sm text-slate-500">
                            <div className="flex items-center gap-2"><CheckCircle2 className="h-4 w-4 text-blue-500" /> Multi-language Support</div>
                            <div className="flex items-center gap-2"><CheckCircle2 className="h-4 w-4 text-blue-500" /> Bulk CSV/XML Import</div>
                            <div className="flex items-center gap-2"><CheckCircle2 className="h-4 w-4 text-blue-500" /> Dynamic Features</div>
                        </div>
                    </div>

                    {/* Right - Hero Visual / Product Preview */}
                    <div className="relative">
                        {/* Floating decorative elements */}
                        <div className="absolute -top-4 -right-4 w-20 h-20 bg-blue-100 rounded-2xl rotate-12 opacity-60 animate-pulse"></div>
                        <div className="absolute -bottom-6 -left-6 w-16 h-16 bg-indigo-100 rounded-full opacity-60 animate-pulse" style={{ animationDelay: '1s' }}></div>

                        {/* Main card */}
                        <div className="relative bg-white rounded-2xl border border-slate-200 shadow-2xl shadow-blue-500/10 overflow-hidden">
                            {/* Header */}
                            <div className="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                                <div className="flex items-center gap-3">
                                    <div className="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                                        <Sparkles className="h-4 w-4 text-white" />
                                    </div>
                                    <div>
                                        <div className="text-white font-semibold text-sm">ETIM Pro Dashboard</div>
                                        <div className="text-blue-200 text-xs">Real-time Classification Manager</div>
                                    </div>
                                </div>
                            </div>

                            {/* Stats row */}
                            <div className="grid grid-cols-3 divide-x divide-slate-100 border-b border-slate-100">
                                <div className="p-4 text-center">
                                    <div className="text-2xl font-bold text-slate-900">14.3K</div>
                                    <div className="text-xs text-slate-400 mt-1">Products</div>
                                </div>
                                <div className="p-4 text-center bg-blue-50/50">
                                    <div className="text-2xl font-bold text-blue-600">8,204</div>
                                    <div className="text-xs text-blue-500 mt-1">ETIM Assigned</div>
                                </div>
                                <div className="p-4 text-center">
                                    <div className="text-2xl font-bold text-slate-900">142</div>
                                    <div className="text-xs text-slate-400 mt-1">Classes</div>
                                </div>
                            </div>

                            {/* Mini table */}
                            <div className="p-4 space-y-3">
                                {[
                                    { name: "LED Lamps", code: "EC001959", count: "1,240", pct: 85 },
                                    { name: "Power Cables", code: "EC000057", count: "830", pct: 72 },
                                    { name: "Installation Switches", code: "EC001590", count: "450", pct: 55 },
                                ].map((item, i) => (
                                    <div key={i} className="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-blue-50/50 transition-colors group">
                                        <div className="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center shrink-0 group-hover:bg-blue-200 transition-colors">
                                            <BarChart3 className="h-5 w-5 text-blue-600" />
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-center justify-between">
                                                <span className="text-sm font-medium text-slate-800 truncate">{item.name}</span>
                                                <span className="text-xs text-slate-400 ml-2">{item.code}</span>
                                            </div>
                                            <div className="mt-1.5 flex items-center gap-2">
                                                <div className="flex-1 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                                    <div
                                                        className="h-full bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full"
                                                        style={{ width: `${item.pct}%` }}
                                                    ></div>
                                                </div>
                                                <span className="text-xs text-slate-500 w-12 text-right">{item.count}</span>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            {/* Footer action */}
                            <div className="px-4 pb-4">
                                <div className="flex items-center justify-between px-4 py-3 bg-gradient-to-r from-emerald-50 to-green-50 rounded-xl border border-emerald-200/50">
                                    <div className="flex items-center gap-2">
                                        <Zap className="h-4 w-4 text-emerald-600" />
                                        <span className="text-sm font-medium text-emerald-700">Auto-sync active</span>
                                    </div>
                                    <span className="text-xs text-emerald-500">Updated 2m ago</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
