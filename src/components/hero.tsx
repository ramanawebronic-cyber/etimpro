import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { ArrowRight, CheckCircle2 } from "lucide-react";
import Link from "next/link";

export function HeroSection() {
    return (
        <section className="relative overflow-hidden w-full pt-24 md:pt-32 lg:pt-40 pb-16 md:pb-24 flex items-center justify-center">
            {/* Subtle grid background */}
            <div className="absolute inset-0 bg-[linear-gradient(to_right,#3b82f608_1px,transparent_1px),linear-gradient(to_bottom,#3b82f608_1px,transparent_1px)] bg-[size:24px_24px] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>
            {/* Blue gradient glow */}
            <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[300px] bg-blue-500/10 blur-[120px] rounded-full pointer-events-none"></div>

            <div className="container mx-auto relative z-10 flex flex-col items-center text-center max-w-[800px]">
                <Badge variant="outline" className="mb-6 rounded-full px-4 py-1.5 bg-blue-50 border-blue-200 text-blue-700">
                    <span className="flex h-2 w-2 rounded-full bg-blue-500 mr-2 animate-pulse"></span>
                    ETIM Pro for WooCommerce is now live
                </Badge>

                <h1 className="text-4xl md:text-6xl lg:text-7xl font-extrabold tracking-tight mb-8 text-slate-900">
                    Master Product Classification with <br className="hidden md:block" />
                    <span className="bg-gradient-to-r from-blue-600 via-blue-500 to-indigo-500 text-transparent bg-clip-text">ETIM Pro</span>
                </h1>

                <p className="text-lg md:text-xl text-slate-500 mb-10 max-w-[600px] mx-auto leading-relaxed">
                    The ultimate WooCommerce plugin for assigning ETIM groups, classes, and features. Import CSV/XML data, generate features dynamically, and support 17 languages seamlessly.
                </p>

                <div className="flex flex-col sm:flex-row gap-4 w-full sm:w-auto mt-4 px-4 sm:px-0">
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

                <div className="mt-12 flex flex-wrap items-center justify-center gap-6 text-sm text-slate-500">
                    <div className="flex items-center gap-2"><CheckCircle2 className="h-4 w-4 text-blue-500" /> Multi-language Support</div>
                    <div className="flex items-center gap-2"><CheckCircle2 className="h-4 w-4 text-blue-500" /> Bulk CSV/XML Import</div>
                    <div className="flex items-center gap-2"><CheckCircle2 className="h-4 w-4 text-blue-500" /> Dynamic Features</div>
                </div>
            </div>
        </section>
    );
}
